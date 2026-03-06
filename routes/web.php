<?php

use App\Livewire\Articulos\Create as ArticulosCreate;
use App\Livewire\Articulos\Delete as ArticulosDelete;
use App\Livewire\Articulos\Index as ArticulosIndex;
use App\Livewire\Articulos\Update as ArticulosUpdate;
use App\Livewire\Articulos\Inventario as ArticulosInventario;
use App\Livewire\Articulos\Show as ArticulosShow;
use App\Livewire\ActivityLogs\Index as ActivityLogsIndex;
use App\Livewire\Categorias\Create as CategoriasCreate;
use App\Livewire\Categorias\Delete as CategoriasDelete;
use App\Livewire\Categorias\Index as CategoriasIndex;
use App\Livewire\Categorias\Update as CategoriasUpdate;
use App\Livewire\Eventos\Create as EventosCreate;
use App\Livewire\Eventos\Delete as EventosDelete;
use App\Livewire\Eventos\Index as EventosIndex;
use App\Livewire\Eventos\Update as EventosUpdate;
use App\Livewire\Mantenimientos\Create as MantenimientosCreate;
use App\Livewire\Mantenimientos\Delete as MantenimientosDelete;
use App\Livewire\Mantenimientos\Index as MantenimientosIndex;
use App\Livewire\Mantenimientos\Update as MantenimientosUpdate;
use App\Livewire\Prestamos\Index as PrestamosIndex;
use App\Livewire\Prestamos\Create as PrestamosCreate;
use App\Livewire\Prestamos\Devolucion as PrestamosDevolucion;
use App\Livewire\Prestamos\RegisterSeries as PrestamosRegisterSeries;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use App\Livewire\Users\Create;
use App\Livewire\Users\Delete;
use App\Livewire\Users\Index;
use App\Livewire\Users\Update;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('dashboard', function () {
    $prestamos = \App\Models\Operacion::with([
        'policia',
        'detalles.articulo',
        'detalles.series.serie',
        'devoluciones.detalles.articulo',
    ])->where('tipo', 'asignacion')->latest()->get();

    $estadoPrestamo = function ($op) {
        $devueltosCantidad = [];
        foreach ($op->devoluciones as $dev) {
            foreach ($dev->detalles as $detDev) {
                if (optional($detDev->articulo)->seguimiento === 'cantidad') {
                    $devueltosCantidad[$detDev->articulo_id] = ($devueltosCantidad[$detDev->articulo_id] ?? 0) + (int) $detDev->cantidad;
                }
            }
        }
        foreach ($op->detalles as $detOp) {
            if (optional($detOp->articulo)->seguimiento === 'serie') {
                $asignadas = $detOp->series->filter(fn($s) => optional($s->serie)->operacion_detalle_id_actual === $detOp->id);
                if ($asignadas->count() > 0) {
                    return 'pendiente';
                }
            } else {
                $dev = $devueltosCantidad[$detOp->articulo_id] ?? 0;
                if ($detOp->cantidad > $dev) {
                    return 'pendiente';
                }
            }
        }
        return 'concluido';
    };

    $prestamosActivos = $prestamos->filter(fn($op) => $estadoPrestamo($op) === 'pendiente')->count();
    $prestamosConcluidos = $prestamos->count() - $prestamosActivos;
    $devolucionesPendientes = $prestamosActivos;
    $personalActivo = \App\Models\User::where('role', 'policia')->count();

    $prestamosRecientes = $prestamos->take(6)->map(function ($op) use ($estadoPrestamo) {
        return [
            'id' => $op->id,
            'policia' => $op->policia?->name ?? 'Sin asignar',
            'badge' => $op->policia?->numero_escalafon ?? '',
            'articulo' => $op->detalles->first()?->articulo?->nombre ?? 'Articulo',
            'serie' => $op->detalles->first()?->series->first()?->serie?->codigo_serie ?? null,
            'fecha' => optional($op->fecha)->format('d M Y'),
            'estado' => $estadoPrestamo($op),
        ];
    });

    $categorias = \App\Models\Articulo::select('categoria_id', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
        ->with('categoria')
        ->groupBy('categoria_id')
        ->orderByDesc('total')
        ->get();
    $totalInventario = $categorias->sum('total');

    return view('dashboard', [
        'prestamosActivos' => $prestamosActivos,
        'prestamosConcluidos' => $prestamosConcluidos,
        'devolucionesPendientes' => $devolucionesPendientes,
        'personalActivo' => $personalActivo,
        'prestamosRecientes' => $prestamosRecientes,
        'categorias' => $categorias,
        'totalInventario' => $totalInventario,
    ]);
})
    ->middleware(['auth', 'verified', 'role:admin|furriel', 'permission:dashboard.view'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');

    Route::get('prestamos', PrestamosIndex::class)
        ->middleware(['permission:prestamos.view'])
        ->name('prestamos.index');

    Route::get('settings/two-factor', TwoFactor::class)
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});

Route::middleware(['auth', 'role:admin|furriel'])->group(function () {
    Route::get('users', Index::class)->middleware(['permission:users.manage'])->name('users.index');
    Route::get('users/create', Create::class)->middleware(['permission:users.manage'])->name('users.create');
    Route::get('users/{user}/update', Update::class)->middleware(['permission:users.manage'])->name('users.update');
    Route::get('users/deleted', Delete::class)->middleware(['permission:users.manage'])->name('users.delete.index');

    Route::get('categorias', CategoriasIndex::class)->middleware(['permission:categorias.manage'])->name('categorias.index');
    Route::get('categorias/create', CategoriasCreate::class)->middleware(['permission:categorias.manage'])->name('categorias.create');
    Route::get('categorias/{categoria}/update', CategoriasUpdate::class)->middleware(['permission:categorias.manage'])->name('categorias.update');
    Route::get('categorias/deleted', CategoriasDelete::class)->middleware(['permission:categorias.manage'])->name('categorias.delete.index');

    Route::get('articulos', ArticulosIndex::class)->middleware(['permission:articulos.manage'])->name('articulos.index');
    Route::get('articulos/create', ArticulosCreate::class)->middleware(['permission:articulos.manage'])->name('articulos.create');
    Route::get('articulos/{articulo}/update', ArticulosUpdate::class)->middleware(['permission:articulos.manage'])->name('articulos.update');
    Route::get('articulos/deleted', ArticulosDelete::class)->middleware(['permission:articulos.manage'])->name('articulos.delete.index');
    Route::get('aticulos/invetario', ArticulosInventario::class)->middleware(['permission:articulos.manage'])->name('articulos.inventario');
    Route::get('articulos/{articulo}/show', ArticulosShow::class)->middleware(['permission:articulos.manage'])->name('articulos.show');

    Route::get('eventos', EventosIndex::class)->middleware(['permission:eventos.manage'])->name('eventos.index');
    Route::get('eventos/create', EventosCreate::class)->middleware(['permission:eventos.manage'])->name('eventos.create');
    Route::get('eventos/{evento}/update', EventosUpdate::class)->middleware(['permission:eventos.manage'])->name('eventos.update');
    Route::get('eventos/deleted', EventosDelete::class)->middleware(['permission:eventos.manage'])->name('eventos.delete.index');
    
    Route::get('mantenimientos', MantenimientosIndex::class)->middleware(['permission:mantenimientos.manage'])->name('mantenimientos.index');
    Route::get('mantenimientos/create', MantenimientosCreate::class)->middleware(['permission:mantenimientos.manage'])->name('mantenimientos.create');
    Route::get('mantenimientos/{mantenimiento}/update', MantenimientosUpdate::class)->middleware(['permission:mantenimientos.manage'])->name('mantenimientos.update');
    Route::get('mantenimientos/deleted', MantenimientosDelete::class)->middleware(['permission:mantenimientos.manage'])->name('mantenimientos.delete.index');
    
    Route::get('prestamos/create', PrestamosCreate::class)->middleware(['permission:prestamos.manage'])->name('prestamos.create');
    Route::get('prestamos/{operacion}/devolucion', PrestamosDevolucion::class)->middleware(['permission:prestamos.manage'])->name('prestamos.devolucion');
    
    Route::get('activity-logs', ActivityLogsIndex::class)->middleware(['permission:activity_logs.view'])->name('activity-logs.index');
});

require __DIR__.'/auth.php';
