<?php

use App\Http\Controllers\Settings\ToggleAppearanceController;
use App\Livewire\ActivityLogs\Index as ActivityLogsIndex;
use App\Livewire\Articulos\Create as ArticulosCreate;
use App\Livewire\Articulos\Delete as ArticulosDelete;
use App\Livewire\Articulos\Index as ArticulosIndex;
use App\Livewire\Articulos\Inventario as ArticulosInventario;
use App\Livewire\Articulos\Show as ArticulosShow;
use App\Livewire\Articulos\Update as ArticulosUpdate;
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
use App\Livewire\Predicciones\Index as PrediccionesIndex;
use App\Livewire\Prestamos\Create as PrestamosCreate;
use App\Livewire\Prestamos\Devolucion as PrestamosDevolucion;
use App\Livewire\Prestamos\Index as PrestamosIndex;
use App\Livewire\Reposicion\Index as ReposicionIndex;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use App\Livewire\Unidades\Create as UnidadesCreate;
use App\Livewire\Unidades\Index as UnidadesIndex;
use App\Livewire\Unidades\Update as UnidadesUpdate;
use App\Livewire\Users\Create;
use App\Livewire\Users\Delete;
use App\Livewire\Users\Index;
use App\Livewire\Users\Transfer;
use App\Livewire\Users\Update;
use App\Models\ArticuloSerie;
use App\Models\InventarioUnidadArticulo;
use App\Models\Operacion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('dashboard', function () {
    $user = auth()->user()?->loadMissing('unidad');
    $unidadId = $user?->unidad_id;

    $prestamosBase = Operacion::query()
        ->where('tipo', 'asignacion')
        ->when(! $user?->isAdministradorGeneral(), fn ($query) => $query->where('unidad_id', $unidadId));

    $prestamoPendienteSubquery = function ($subquery): void {
        $subquery
            ->selectRaw('1')
            ->from('operacion_detalles as od')
            ->join('articulos as a', 'a.id', '=', 'od.articulo_id')
            ->whereColumn('od.operacion_id', 'operaciones.id')
            ->whereNull('od.deleted_at')
            ->where(function ($pending) {
                $pending
                    ->where(function ($serializados) {
                        $serializados
                            ->whereRaw("a.tipo::text = 'reutilizable'")
                            ->whereExists(function ($series) {
                                $series
                                    ->selectRaw('1')
                                    ->from('operacion_detalle_series as ods')
                                    ->join('articulo_series as s', 's.id', '=', 'ods.serie_id')
                                    ->whereColumn('ods.operacion_detalle_id', 'od.id')
                                    ->whereColumn('s.operacion_detalle_id_actual', 'od.id')
                                    ->whereNull('ods.deleted_at')
                                    ->whereNull('s.deleted_at');
                            });
                    })
                    ->orWhere(function ($cantidad) {
                        $cantidad
                            ->whereRaw("a.tipo::text = 'consumible'")
                            ->whereRaw('od.cantidad > COALESCE((
                                SELECT SUM(odd.cantidad)
                                FROM operaciones dev
                                JOIN operacion_detalles odd ON odd.operacion_id = dev.id
                                WHERE dev.operacion_padre_id = operaciones.id
                                    AND dev.deleted_at IS NULL
                                    AND odd.deleted_at IS NULL
                                    AND odd.articulo_id = od.articulo_id
                            ), 0)');
                    });
            });
    };

    $wherePrestamoPendiente = fn ($query) => $query->whereExists($prestamoPendienteSubquery);

    $estadoPrestamo = function ($op) {
        $devueltosCantidad = [];
        foreach ($op->devoluciones as $dev) {
            foreach ($dev->detalles as $detDev) {
                if (optional($detDev->articulo)?->isCantidad()) {
                    $devueltosCantidad[$detDev->articulo_id] = ($devueltosCantidad[$detDev->articulo_id] ?? 0) + (int) $detDev->cantidad;
                }
            }
        }
        foreach ($op->detalles as $detOp) {
            if (optional($detOp->articulo)?->isSerializado()) {
                $asignadas = $detOp->series->filter(fn ($s) => optional($s->serie)->operacion_detalle_id_actual === $detOp->id);
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

    $totalPrestamos = (clone $prestamosBase)->count();
    $prestamosActivos = $wherePrestamoPendiente(clone $prestamosBase)->count();
    $prestamosConcluidos = max(0, $totalPrestamos - $prestamosActivos);
    $devolucionesPendientes = $prestamosActivos;
    $personalActivo = User::query()
        ->where('role', 'policia')
        ->when(! $user?->isAdministradorGeneral(), fn ($query) => $query->where('unidad_id', $unidadId))
        ->count();

    $prestamosRecientes = (clone $prestamosBase)
        ->with([
            'usuarioDestino',
            'detalles.articulo',
            'detalles.series.serie',
            'devoluciones.detalles.articulo',
        ])
        ->latest()
        ->limit(6)
        ->get()
        ->map(function ($op) use ($estadoPrestamo) {
            return [
                'id' => $op->id,
                'policia' => $op->usuarioDestino?->name ?? 'Sin asignar',
                'badge' => $op->usuarioDestino?->numero_escalafon ?? '',
                'articulo' => $op->detalles->first()?->articulo?->nombre ?? 'Articulo',
                'serie' => $op->detalles->first()?->series->first()?->serie?->codigo_serie ?? null,
                'fecha' => optional($op->fecha)->format('d M Y'),
                'estado' => $estadoPrestamo($op),
            ];
        });

    $prestamosPorMes = (clone $prestamosBase)
        ->where('fecha', '>=', now()->startOfMonth()->subMonths(5))
        ->selectRaw("TO_CHAR(fecha, 'YYYY-MM') AS periodo, COUNT(*) AS total")
        ->groupByRaw("TO_CHAR(fecha, 'YYYY-MM')")
        ->pluck('total', 'periodo');

    $prestamosTendencia = collect(range(5, 0))
        ->map(function (int $offset) use ($prestamosPorMes) {
            $month = now()->startOfMonth()->subMonths($offset);

            return [
                'label' => $month->locale('es')->translatedFormat('M'),
                'full_label' => $month->translatedFormat('F Y'),
                'total' => (int) ($prestamosPorMes[$month->format('Y-m')] ?? 0),
            ];
        })
        ->values();

    $inicioSemana = now()->startOfWeek();
    $finSemana = $inicioSemana->copy()->endOfWeek();
    $prestamosPorDia = (clone $prestamosBase)
        ->whereBetween('fecha', [$inicioSemana, $finSemana])
        ->selectRaw('DATE(fecha) AS dia, COUNT(*) AS total')
        ->groupByRaw('DATE(fecha)')
        ->pluck('total', 'dia');

    $prestamosSemana = collect(range(0, 6))
        ->map(function (int $offset) use ($prestamosPorDia, $inicioSemana) {
            $day = $inicioSemana->copy()->addDays($offset);

            return [
                'label' => mb_strtoupper($day->locale('es')->translatedFormat('D')),
                'full_label' => $day->locale('es')->translatedFormat('l d \d\e F'),
                'total' => (int) ($prestamosPorDia[$day->format('Y-m-d')] ?? 0),
            ];
        })
        ->values();

    $categorias = \App\Models\Articulo::select('categoria_id', DB::raw('count(*) as total'))
        ->with('categoria')
        ->groupBy('categoria_id')
        ->orderByDesc('total')
        ->get();
    $totalInventario = $categorias->sum('total');

    $seriesBase = ArticuloSerie::query()
        ->when(! $user?->isAdministradorGeneral(), fn ($query) => $query->where('unidad_id', $unidadId));

    $condicionArmamento = collect([
        ['key' => 'bueno', 'label' => 'Bueno', 'color' => 'bg-emerald-500', 'text' => 'text-emerald-600'],
        ['key' => 'con_defectos', 'label' => 'Con defectos', 'color' => 'bg-amber-500', 'text' => 'text-amber-600'],
        ['key' => 'malo', 'label' => 'Malo', 'color' => 'bg-orange-500', 'text' => 'text-orange-600'],
        ['key' => 'inoperativo', 'label' => 'Inoperativo', 'color' => 'bg-rose-500', 'text' => 'text-rose-600'],
    ])->map(function (array $item) use ($seriesBase) {
        $item['total'] = (clone $seriesBase)
            ->where('condicion_actual', $item['key'])
            ->count();

        return $item;
    });

    $totalArmamento = $condicionArmamento->sum('total');
    $condicionMax = max(1, $condicionArmamento->max('total') ?? 1);

    $seriesDisponibles = (clone $seriesBase)->where('estado', 'disponible')->count();
    $seriesAsignadas = (clone $seriesBase)->where('estado', 'asignado')->count();
    $seriesMantenimiento = (clone $seriesBase)->where('estado', 'en_mantenimiento')->count();
    $seriesInoperativas = (clone $seriesBase)->where('estado', 'inoperativo')->count();

    $consumiblesBase = InventarioUnidadArticulo::query()
        ->whereHas('articulo', fn ($query) => $query->where('tipo', 'consumible'))
        ->when(! $user?->isAdministradorGeneral(), fn ($query) => $query->where('unidad_id', $unidadId));

    $consumiblesBajoStock = (clone $consumiblesBase)
        ->where('cantidad_disponible', '>', 0)
        ->where('cantidad_disponible', '<=', 10)
        ->count();

    $consumiblesAgotados = (clone $consumiblesBase)
        ->where('cantidad_disponible', '<=', 0)
        ->count();

    return view('dashboard', [
        'prestamosActivos' => $prestamosActivos,
        'prestamosConcluidos' => $prestamosConcluidos,
        'devolucionesPendientes' => $devolucionesPendientes,
        'personalActivo' => $personalActivo,
        'prestamosRecientes' => $prestamosRecientes,
        'prestamosTendencia' => $prestamosTendencia,
        'prestamosSemana' => $prestamosSemana,
        'categorias' => $categorias,
        'totalInventario' => $totalInventario,
        'condicionArmamento' => $condicionArmamento,
        'totalArmamento' => $totalArmamento,
        'condicionMax' => $condicionMax,
        'seriesDisponibles' => $seriesDisponibles,
        'seriesAsignadas' => $seriesAsignadas,
        'seriesMantenimiento' => $seriesMantenimiento,
        'seriesInoperativas' => $seriesInoperativas,
        'consumiblesBajoStock' => $consumiblesBajoStock,
        'consumiblesAgotados' => $consumiblesAgotados,
    ]);
})
    ->middleware(['auth', 'verified', 'role:administrador_general|administrador_unidad|furriel', 'permission:dashboard.view'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
    Route::post('settings/appearance/toggle', ToggleAppearanceController::class)
        ->name('settings.appearance.toggle');

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

Route::middleware(['auth', 'role:administrador_general|administrador_unidad|furriel'])->group(function () {
    Route::get('users', Index::class)->middleware(['permission:users.manage'])->name('users.index');
    Route::get('users/create', Create::class)->middleware(['permission:users.manage'])->name('users.create');
    Route::get('users/{user}/update', Update::class)->middleware(['permission:users.manage'])->name('users.update');
    Route::get('users/{user}/transfer', Transfer::class)->middleware(['permission:users.transfer'])->name('users.transfer');
    Route::get('users/deleted', Delete::class)->middleware(['permission:users.manage'])->name('users.delete.index');

    Route::get('unidades', UnidadesIndex::class)->middleware(['permission:units.manage'])->name('unidades.index');
    Route::get('unidades/create', UnidadesCreate::class)->middleware(['permission:units.manage'])->name('unidades.create');
    Route::get('unidades/{unidad}/update', UnidadesUpdate::class)->middleware(['permission:units.manage'])->name('unidades.update');

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

    Route::get('predicciones', PrediccionesIndex::class)->middleware(['permission:predicciones.view'])->name('predicciones.index');
    Route::get('reposicion', ReposicionIndex::class)->middleware(['permission:reposicion.view'])->name('reposicion.index');

    Route::get('prestamos/create', PrestamosCreate::class)->middleware(['permission:prestamos.manage'])->name('prestamos.create');
    Route::get('prestamos/{operacion}/devolucion', PrestamosDevolucion::class)->middleware(['permission:prestamos.manage'])->name('prestamos.devolucion');

    Route::get('activity-logs', ActivityLogsIndex::class)->middleware(['permission:activity_logs.view'])->name('activity-logs.index');
});

require __DIR__.'/auth.php';
