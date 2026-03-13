<?php

namespace App\Livewire\Articulos;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Articulo;
use App\Models\ArticuloSerie;
use App\Models\OperacionDetalle;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Support\Facades\DB;

class Show extends Component
{
    use WithPagination;

    public Articulo $articulo;

    // Opcional: número de items por página para series
    public int $perPage = 15;

    public function mount(Articulo $articulo)
    {
        $this->articulo = $articulo;
    }

    public function render()
    {
        // Si el artículo es por series, devolvemos la paginación de series.
        if ($this->articulo->seguimiento === 'serie') {
            $series = ArticuloSerie::query()
                ->where('articulo_id', $this->articulo->id)
                ->whereNull('deleted_at')
                ->orderBy('created_at', 'desc')
                ->paginate($this->perPage);

            return view('livewire.articulos.show', compact('series'));
        }

        // Para artículos por cantidad, mostramos los movimientos (operacion_detalles)
        $detalles = OperacionDetalle::query()
            ->with('operacion')
            ->where('articulo_id', $this->articulo->id)
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.articulos.show', compact('detalles'));
    }

    /**
     * Exportar PDF del artÃ­culo actual
     */
    public function exportPdf()
    {
        $articulo = $this->articulo->load('categoria');

        if ($articulo->seguimiento === 'serie') {
            $series = ArticuloSerie::query()
                ->where('articulo_id', $articulo->id)
                ->whereNull('deleted_at')
                ->orderBy('created_at', 'desc')
                ->get();

            $resumen = [
                'total' => $series->count(),
                'disponibles' => $series->where('estado', 'disponible')->count(),
                'asignados' => $series->where('estado', 'asignado')->count(),
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
            fn() => print($pdf->output()),
            'articulo_'.$articulo->id.'_'.now()->format('Ymd_His').'.pdf'
        );
    }
}
