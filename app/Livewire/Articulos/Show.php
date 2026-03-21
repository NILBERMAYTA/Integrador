<?php

namespace App\Livewire\Articulos;

use App\Models\Articulo;
use App\Models\ArticuloSerie;
use App\Models\InventarioUnidadArticulo;
use App\Models\OperacionDetalle;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Show extends Component
{
    use WithPagination;

    public Articulo $articulo;
    public int $perPage = 10;
    public string $searchSerie = '';
    public string $estadoFiltro = '';
    public string $condicionFiltro = '';
    public array $condicionesActuales = [];

    public function mount(Articulo $articulo): void
    {
        $this->articulo = $articulo;
    }

    public function updatingSearchSerie(): void
    {
        $this->resetPage();
    }

    public function updatingEstadoFiltro(): void
    {
        $this->resetPage();
    }

    public function updatingCondicionFiltro(): void
    {
        $this->resetPage();
    }

    public function guardarCondicion(int $serieId): void
    {
        abort_unless(auth()->user()?->can('articulos.manage'), 403);

        $condicion = $this->condicionesActuales[$serieId] ?? null;
        if (! in_array($condicion, $this->condicionesDisponibles(), true)) {
            $this->addError("condicionesActuales.$serieId", 'Condicion no valida.');
            return;
        }

        $serie = ArticuloSerie::query()
            ->where('articulo_id', $this->articulo->id)
            ->whereKey($serieId)
            ->firstOrFail();

        $serie->update([
            'condicion_actual' => $condicion,
        ]);

        session()->flash('success', 'Condicion fisica actualizada.');
    }

    public function render()
    {
        if ($this->articulo->isSerializado()) {
            $baseSeries = ArticuloSerie::query()
                ->with([
                    'unidad',
                    'operacionDetalleActual.operacion.usuarioDestino',
                ])
                ->where('articulo_id', $this->articulo->id)
                ->whereNull('deleted_at');

            $seriesResumen = (clone $baseSeries)->get();

            $series = (clone $baseSeries)
                ->when($this->searchSerie !== '', fn ($q) => $q->where('codigo_serie', 'ILIKE', '%'.$this->searchSerie.'%'))
                ->when($this->estadoFiltro !== '', fn ($q) => $q->where('estado', $this->estadoFiltro))
                ->when($this->condicionFiltro !== '', fn ($q) => $q->where('condicion_actual', $this->condicionFiltro))
                ->orderBy('codigo_serie')
                ->paginate($this->perPage);

            foreach ($series as $serie) {
                $this->condicionesActuales[$serie->id] = $this->condicionesActuales[$serie->id] ?? $serie->condicion_actual;
            }

            $resumen = [
                'total' => $seriesResumen->count(),
                'disponibles' => $seriesResumen->where('estado', 'disponible')->count(),
                'asignados' => $seriesResumen->where('estado', 'asignado')->count(),
                'mantenimiento' => $seriesResumen->where('estado', 'en_mantenimiento')->count(),
                'inoperativos' => $seriesResumen->whereIn('estado', ['inoperativo', 'dado_de_baja'])->count(),
                'condicion_predominante' => $seriesResumen
                    ->groupBy(fn ($serie) => $serie->condicion_actual ?: 'bueno')
                    ->sortByDesc(fn ($group) => $group->count())
                    ->keys()
                    ->first() ?? 'bueno',
                'unidades' => $seriesResumen->pluck('unidad.sigla')->filter()->unique()->values(),
            ];

            $movimientosRecientes = OperacionDetalle::query()
                ->with(['operacion', 'series.serie'])
                ->where('articulo_id', $this->articulo->id)
                ->latest('created_at')
                ->limit(8)
                ->get();

            $estadosDisponibles = [
                'disponible',
                'asignado',
                'en_mantenimiento',
                'observado',
                'inoperativo',
                'dado_de_baja',
            ];

            $condicionesDisponibles = $this->condicionesDisponibles();

            return view('livewire.articulos.show', compact(
                'series',
                'resumen',
                'estadosDisponibles',
                'condicionesDisponibles',
                'movimientosRecientes'
            ));
        }

        $inventarios = InventarioUnidadArticulo::query()
            ->with('unidad:id,nombre,sigla')
            ->where('articulo_id', $this->articulo->id)
            ->orderBy('unidad_id')
            ->get();

        $entrada = (float) DB::table('operacion_detalles as od')
            ->join('operaciones as o', 'o.id', '=', 'od.operacion_id')
            ->whereNull('od.deleted_at')
            ->where('od.articulo_id', $this->articulo->id)
            ->whereIn('o.tipo', ['ajuste', 'devolucion', 'mantenimiento_retorno'])
            ->sum('od.cantidad');

        $salida = (float) DB::table('operacion_detalles as od')
            ->join('operaciones as o', 'o.id', '=', 'od.operacion_id')
            ->whereNull('od.deleted_at')
            ->where('od.articulo_id', $this->articulo->id)
            ->whereIn('o.tipo', ['asignacion', 'consumo', 'mantenimiento_salida'])
            ->sum('od.cantidad');

        $totalDisponible = (float) $inventarios->sum('cantidad_disponible');

        $resumen = [
            'entrada' => $entrada,
            'salida' => $salida,
            'total' => $totalDisponible,
            'estado' => $totalDisponible <= 0 ? 'agotado' : ($totalDisponible <= 5 ? 'bajo_stock' : 'disponible'),
            'unidades' => $inventarios->pluck('unidad.sigla')->filter()->unique()->values(),
        ];

        $detalles = OperacionDetalle::query()
            ->with('operacion')
            ->where('articulo_id', $this->articulo->id)
            ->latest('created_at')
            ->paginate($this->perPage);

        return view('livewire.articulos.show', compact('detalles', 'inventarios', 'resumen'));
    }

    public function exportPdf()
    {
        $articulo = $this->articulo->load('categoria');

        if ($articulo->isSerializado()) {
            $series = ArticuloSerie::query()
                ->where('articulo_id', $articulo->id)
                ->whereNull('deleted_at')
                ->orderBy('created_at', 'desc')
                ->get();

            $resumen = [
                'total' => $series->count(),
                'disponibles' => $series->where('estado', 'disponible')->count(),
                'asignados' => $series->where('estado', 'asignado')->count(),
                'mantenimiento' => $series->where('estado', 'en_mantenimiento')->count(),
                'observados' => $series->where('estado', 'observado')->count(),
                'inoperativos' => $series->where('estado', 'inoperativo')->count(),
                'baja' => $series->where('estado', 'dado_de_baja')->count(),
                'cond_bueno' => $series->where('condicion_actual', 'bueno')->count(),
                'cond_defectos' => $series->where('condicion_actual', 'con_defectos')->count(),
                'cond_malo' => $series->where('condicion_actual', 'malo')->count(),
                'cond_inoperativo' => $series->where('condicion_actual', 'inoperativo')->count(),
            ];

            $pdf = PDF::loadView('reports.articulo', [
                'articulo' => $articulo,
                'series' => $series,
                'detalles' => collect(),
                'resumen' => $resumen,
            ])->setPaper('a4', 'portrait');
        } else {
            $detalles = OperacionDetalle::query()
                ->with('operacion')
                ->where('articulo_id', $articulo->id)
                ->orderBy('created_at', 'desc')
                ->get();

            $entrada = DB::table('operacion_detalles as od')
                ->join('operaciones as o', 'o.id', '=', 'od.operacion_id')
                ->whereNull('od.deleted_at')
                ->where('od.articulo_id', $articulo->id)
                ->whereIn('o.tipo', ['ajuste', 'devolucion', 'mantenimiento_retorno'])
                ->sum('od.cantidad') ?? 0;

            $salida = DB::table('operacion_detalles as od')
                ->join('operaciones as o', 'o.id', '=', 'od.operacion_id')
                ->whereNull('od.deleted_at')
                ->where('od.articulo_id', $articulo->id)
                ->whereIn('o.tipo', ['asignacion', 'consumo', 'mantenimiento_salida'])
                ->sum('od.cantidad') ?? 0;

            $resumen = [
                'entrada' => $entrada,
                'salida' => $salida,
                'total' => $entrada - $salida,
            ];

            $pdf = PDF::loadView('reports.articulo', [
                'articulo' => $articulo,
                'series' => collect(),
                'detalles' => $detalles,
                'resumen' => $resumen,
            ])->setPaper('a4', 'portrait');
        }

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'articulo_'.$articulo->id.'_'.now()->format('Ymd_His').'.pdf'
        );
    }

    private function condicionesDisponibles(): array
    {
        return [
            'bueno',
            'con_defectos',
            'malo',
            'inoperativo',
        ];
    }
}
