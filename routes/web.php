<?php

use App\Livewire\Articulos\Create as ArticulosCreate;
use App\Livewire\Articulos\Delete as ArticulosDelete;
use App\Livewire\Articulos\Index as ArticulosIndex;
use App\Livewire\Articulos\Update as ArticulosUpdate;
use App\Livewire\Articulos\Inventario as ArticulosInventario;
use App\Livewire\Articulos\Show as ArticulosShow;
use App\Livewire\Categorias\Create as CategoriasCreate;
use App\Livewire\Categorias\Delete as CategoriasDelete;
use App\Livewire\Categorias\Index as CategoriasIndex;
use App\Livewire\Categorias\Update as CategoriasUpdate;
use App\Livewire\Eventos\Create as EventosCreate;
use App\Livewire\Eventos\Delete as EventosDelete;
use App\Livewire\Eventos\Index as EventosIndex;
use App\Livewire\Eventos\Update as EventosUpdate;
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
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');

    Route::get('users', Index::class)->name('users.index');
    Route::get('users/create', Create::class)->name('users.create');
    Route::get('users/{user}/update', Update::class)->name('users.update');
    Route::get('users/deleted', Delete::class)->name('users.delete.index');

    Route::get('categorias', CategoriasIndex::class)->name('categorias.index');
    Route::get('categorias/create', CategoriasCreate::class)->name('categorias.create');
    Route::get('categorias/{categoria}/update', CategoriasUpdate::class)->name('categorias.update');
    Route::get('categorias/deleted', CategoriasDelete::class)->name('categorias.delete.index');

    Route::get('articulos', ArticulosIndex::class)->name('articulos.index');
    Route::get('articulos/create', ArticulosCreate::class)->name('articulos.create');
    Route::get('articulos/{articulo}/update', ArticulosUpdate::class)->name('articulos.update');
    Route::get('articulos/deleted', ArticulosDelete::class)->name('articulos.delete.index');
    Route::get('aticulos/invetario',ArticulosInventario::class)->name('articulos.inventario');
    Route::get('articulos/{articulo}/show',ArticulosShow::class)->name('articulos.show');

    Route::get('eventos', EventosIndex::class)->name('eventos.index');
    Route::get('eventos/create', EventosCreate::class)->name('eventos.create');
    Route::get('eventos/{evento}/update', EventosUpdate::class)->name('eventos.update');
    Route::get('eventos/deleted', EventosDelete::class)->name('eventos.delete.index');
    
    Route::get('prestamos', PrestamosIndex::class)->name('prestamos.index');
    Route::get('prestamos/create', PrestamosCreate::class)->name('prestamos.create');
    Route::get('prestamos/{operacion}/devolucion', PrestamosDevolucion::class)->name('prestamos.devolucion');
    //Route::get('prestamos/{operacion}/series', PrestamosRegisterSeries::class)->name('prestamos.series');
    
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

require __DIR__.'/auth.php';
