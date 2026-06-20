<?php

namespace App\Livewire\Prestamos;

use App\Models\ArticuloSerie;
use App\Models\Operacion;
use App\Models\OperacionDetalle;
use App\Models\OperacionDetalleSerie;
use App\Services\InventarioUnidadService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Devolucion extends Component
{
    public Operacion $operacion;

    public $items = [];

    public $devueltosCantidad = [];

    public string $qrMensaje = '';

    public string $qrError = '';

    public function mount(Operacion $operacion)
    {
        abort_unless(auth()->user()?->isAdmin() || auth()->user()?->isFurriel(), 403);
        abort_unless($operacion->tipo === 'asignacion', 404);
        abort_unless(auth()->user()?->isAdministradorGeneral() || auth()->user()?->unidad_id === $operacion->unidad_id, 403);

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
                'seguimiento' => $detalle->articulo?->isSerializado() ? 'serie' : 'cantidad',
                'cantidad_prestada' => $detalle->cantidad,
                'cantidad_pendiente' => $pendienteCantidad,
                'cantidad_devolver' => 0,
                'series_pendientes' => $seriesAsignadas->map(fn ($s) => [
                    'id' => $s->serie->id,
                    'codigo' => $s->serie->codigo_serie,
                ])->values()->all(),
                'series_devolver' => [],
            ];
        }
    }

    public function procesarQr(string $contenido): void
    {
        $this->qrMensaje = '';
        $this->qrError = '';

        $payload = json_decode(trim($contenido), true);
        if (! is_array($payload) || ($payload['type'] ?? null) !== 'serie') {
            $this->qrError = 'Escanea el QR individual de una serie.';

            return;
        }

        $serieId = (int) ($payload['id'] ?? 0);
        $serie = ArticuloSerie::find($serieId);

        if (! $serie) {
            $this->qrError = 'La serie escaneada no existe.';

            return;
        }

        foreach ($this->items as $index => $item) {
            $pendientes = collect($item['series_pendientes'] ?? []);
            if (! $pendientes->contains('id', $serieId)) {
                continue;
            }

            if (in_array($serieId, $this->items[$index]['series_devolver'], true)) {
                $this->qrError = "La serie {$serie->codigo_serie} ya fue marcada para devolucion.";

                return;
            }

            $this->items[$index]['series_devolver'][] = $serieId;
            $this->items[$index]['series_devolver'] = array_values(array_unique($this->items[$index]['series_devolver']));
            $this->qrMensaje = "Serie {$serie->codigo_serie} marcada para devolucion.";

            return;
        }

        $this->qrError = 'La serie no esta pendiente en este prestamo.';
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

    public function save(InventarioUnidadService $inventario)
    {
        $this->validate();

        $hayDevolucion = false;

        foreach ($this->items as $index => $item) {
            if ($item['seguimiento'] === 'serie') {
                $pendientes = collect($item['series_pendientes'])->pluck('id')->map(fn ($id) => (int) $id);
                $seleccionadas = collect($item['series_devolver'])->map(fn ($id) => (int) $id)->unique();

                if ($seleccionadas->diff($pendientes)->isNotEmpty()) {
                    $this->addError("items.$index.series_devolver", 'Hay una serie que no pertenece a este prestamo.');

                    return;
                }

                if ($seleccionadas->isNotEmpty()) {
                    $seriesValidas = ArticuloSerie::query()
                        ->whereIn('id', $seleccionadas)
                        ->where('unidad_id', $this->operacion->unidad_id)
                        ->where('operacion_detalle_id_actual', $item['detalle_id'])
                        ->count();

                    if ($seriesValidas !== $seleccionadas->count()) {
                        $this->addError("items.$index.series_devolver", 'Alguna serie ya no esta asignada a este prestamo.');

                        return;
                    }
                }

                $hayDevolucion = $hayDevolucion || $seleccionadas->isNotEmpty();
            } else {
                if ($item['cantidad_devolver'] < 0 || $item['cantidad_devolver'] > $item['cantidad_pendiente']) {
                    $this->addError("items.$index.cantidad_devolver", 'Cantidad a devolver fuera de rango.');

                    return;
                }

                $hayDevolucion = $hayDevolucion || (int) $item['cantidad_devolver'] > 0;
            }
        }

        if (! $hayDevolucion) {
            $this->addError('items', 'Escanea o selecciona al menos una serie o cantidad para devolver.');

            return;
        }

        $operacionDevolucion = DB::transaction(function () use ($inventario) {
            $op = Operacion::create([
                'tipo' => 'devolucion',
                'evento_id' => $this->operacion->evento_id,
                'usuario_destino_id' => $this->operacion->usuario_destino_id,
                'actor_id' => Auth::id(),
                'unidad_id' => $this->operacion->unidad_id,
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

                    $inventario->returnAssigned(
                        $this->operacion->unidad_id,
                        \App\Models\Articulo::findOrFail($item['articulo_id']),
                        (float) $item['cantidad_devolver']
                    );
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
