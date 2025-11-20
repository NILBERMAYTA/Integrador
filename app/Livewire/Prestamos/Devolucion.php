<?php

namespace App\Livewire\Prestamos;

use App\Models\ArticuloSerie;
use App\Models\Operacion;
use App\Models\OperacionDetalle;
use App\Models\OperacionDetalleSerie;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Devolucion extends Component
{
    public Operacion $operacion;
    public $items = [];
    public $devueltosCantidad = [];

    public function mount(Operacion $operacion)
    {
        abort_unless($operacion->tipo === 'asignacion', 404);

        $this->operacion = $operacion->load(['detalles.articulo', 'detalles.series.serie']);
        $this->calcularDevueltosCantidad();
        $this->prepararItems();
    }

    protected function calcularDevueltosCantidad(): void
    {
        $sumas = OperacionDetalle::query()
            ->whereHas('operacion', fn ($q) => $q->where('operacion_padre_id', $this->operacion->id)->where('tipo', 'devolucion'))
            ->selectRaw('articulo_id, COALESCE(SUM(cantidad),0) as total')
            ->groupBy('articulo_id')
            ->pluck('total', 'articulo_id')
            ->toArray();

        $this->devueltosCantidad = $sumas;
    }

    protected function prepararItems(): void
    {
        $this->items = [];

        foreach ($this->operacion->detalles as $detalle) {
            $pendienteCantidad = max(0, ($detalle->cantidad ?? 0) - ($this->devueltosCantidad[$detalle->articulo_id] ?? 0));

            $seriesAsignadas = $detalle->series
                ->filter(fn ($s) => optional($s->serie)->operacion_detalle_id_actual == $detalle->id)
                ->values();

            $this->items[] = [
                'detalle_id' => $detalle->id,
                'articulo_id' => $detalle->articulo_id,
                'articulo' => $detalle->articulo?->nombre ?? 'Articulo',
                'seguimiento' => $detalle->articulo?->seguimiento ?? 'cantidad',
                'cantidad_prestada' => $detalle->cantidad,
                'cantidad_pendiente' => $pendienteCantidad,
                'cantidad_devolver' => $pendienteCantidad,
                'series_pendientes' => $seriesAsignadas->map(fn ($s) => [
                    'id' => $s->serie->id,
                    'codigo' => $s->serie->codigo_serie,
                ])->values()->all(),
                'series_devolver' => $seriesAsignadas->map(fn ($s) => $s->serie->id)->values()->all(),
            ];
        }
    }

    protected function rules()
    {
        return [
            'items' => 'array',
            'items.*.cantidad_devolver' => 'nullable|integer|min:0',
            'items.*.series_devolver' => 'array',
            'items.*.series_devolver.*' => 'integer|exists:articulo_series,id',
        ];
    }

    public function save()
    {
        $this->validate();

        foreach ($this->items as $item) {
            if ($item['seguimiento'] === 'serie') {
                $pendientesCount = count($item['series_pendientes']);
                if (count($item['series_devolver']) !== $pendientesCount) {
                    $this->addError('items.series_devolver', 'Debes devolver todas las series pendientes.');
                    return;
                }
            } else {
                if ($item['cantidad_devolver'] < 0 || $item['cantidad_devolver'] > $item['cantidad_pendiente']) {
                    $this->addError('items.cantidad_devolver', 'Cantidad a devolver fuera de rango.');
                    return;
                }
            }
        }

        $operacionDevolucion = DB::transaction(function () {
            $op = Operacion::create([
                'tipo' => 'devolucion',
                'evento_id' => $this->operacion->evento_id,
                'policia_id' => $this->operacion->policia_id,
                'actor_id' => Auth::id(),
                'fecha' => now(),
                'observaciones' => 'Devolucion de operacion '.$this->operacion->id,
                'operacion_padre_id' => $this->operacion->id,
            ]);

            foreach ($this->items as $item) {
                if ($item['seguimiento'] === 'serie') {
                    if (empty($item['series_devolver'])) {
                        continue;
                    }

                    $det = OperacionDetalle::create([
                        'operacion_id' => $op->id,
                        'articulo_id' => $item['articulo_id'],
                        'cantidad' => count($item['series_devolver']),
                        'condicion' => 'bueno',
                    ]);

                    foreach ($item['series_devolver'] as $serieId) {
                        OperacionDetalleSerie::create([
                            'operacion_detalle_id' => $det->id,
                            'serie_id' => $serieId,
                        ]);

                        ArticuloSerie::where('id', $serieId)->update([
                            'estado' => 'disponible',
                            'operacion_detalle_id_actual' => null,
                        ]);
                    }
                } else {
                    if (($item['cantidad_devolver'] ?? 0) <= 0) {
                        continue;
                    }

                    OperacionDetalle::create([
                        'operacion_id' => $op->id,
                        'articulo_id' => $item['articulo_id'],
                        'cantidad' => $item['cantidad_devolver'],
                        'condicion' => 'bueno',
                    ]);
                }
            }

            return $op;
        });

        session()->flash('success', 'Devolucion registrada.');
        return redirect()->route('prestamos.index');
    }

    public function render()
    {
        return view('livewire.prestamos.devolucion');
    }
}
