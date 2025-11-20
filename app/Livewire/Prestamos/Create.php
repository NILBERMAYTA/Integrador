<?php

namespace App\Livewire\Prestamos;

use App\Models\Articulo;
use App\Models\ArticuloSerie;
use App\Models\Evento;
use App\Models\Operacion;
use App\Models\OperacionDetalle;
use App\Models\OperacionDetalleSerie;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;

class Create extends Component
{
    public $evento_id;
    public $policia_id;
    public $observaciones;
    public $items = [];
    public $eventos = [];
    public $policias = [];
    public $articulos = [];
    public $seriesDisponibles = [];
    public $yoloErrores = [];

    protected $listeners = [
        'yolo-detecciones' => 'agregarDesdeYolo',
    ];

    protected $rules = [
        'evento_id' => 'required|exists:eventos,id',
        'policia_id' => 'required|exists:users,id',
        'items' => 'required|array|min:1',
        'items.*.articulo_id' => 'required|exists:articulos,id',
        'items.*.cantidad' => 'required|integer|min:1',
        'items.*.seguimiento' => 'nullable|in:serie,cantidad',
        'items.*.series' => 'array',
        'items.*.series.*' => 'integer|exists:articulo_series,id',
    ];

    public function mount()
    {
        $this->eventos = Evento::orderBy('id', 'desc')->get();
        $this->policias = User::where('role', 'policia')->get();
        $this->articulos = Articulo::orderBy('nombre')->get();
        $this->seriesDisponibles = ArticuloSerie::select('id', 'articulo_id', 'codigo_serie')
            ->where('estado', 'disponible')
            ->orderBy('codigo_serie')
            ->get()
            ->groupBy('articulo_id')
            ->map(function ($group) {
                return $group->map(fn($serie) => [
                    'id' => $serie->id,
                    'codigo_serie' => $serie->codigo_serie,
                ])->values()->all();
            })
            ->toArray(); // Livewire no deshidrata colecciones Eloquent

        $this->addRow();
        $this->yoloErrores = [];
    }

    public function addRow()
    {
        $this->items[] = [
            'articulo_id' => '',
            'nombre' => '',
            'cantidad' => 1,
            'seguimiento' => '',
            'series' => [],
        ];
    }

    public function removeRow($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function updatedItems($value, $key)
    {
        $parts = explode('.', $key);
        if (count($parts) === 2 && $parts[1] === 'articulo_id') {
            $index = (int) $parts[0];
            // Livewire rehidrata como array; usar collect para buscar el articulo seleccionado
            $articulo = $value ? collect($this->articulos)->firstWhere('id', (int) $value) : null;

            $this->items[$index]['nombre'] = $articulo->nombre ?? '';
            $this->items[$index]['seguimiento'] = $articulo->seguimiento ?? '';
            $this->items[$index]['series'] = [];
        }
    }

    public function agregarDesdeYolo($payload = [])
    {
        $raw = $payload['detecciones'] ?? [];
        $this->yoloErrores = [];
        $insertados = 0;

        $detecciones = [];

        // Caso 1: estructura {summary: {...}, detections: [...]}
        if (is_array($raw) && isset($raw['summary']) && is_array($raw['summary'])) {
            foreach ($raw['summary'] as $label => $count) {
                $detecciones[] = ['label' => $label, 'count' => (int) $count];
            }
        }

        // Caso 2: arreglo de {label, count} directamente
        if (empty($detecciones) && is_array($raw) && isset($raw[0]['label'])) {
            foreach ($raw as $det) {
                $detecciones[] = [
                    'label' => $det['label'] ?? null,
                    'count' => (int) ($det['count'] ?? 1),
                ];
            }
        }

        // Caso 3: detections detalladas sin summary; contar labels
        if (empty($detecciones) && is_array($raw) && isset($raw['detections']) && is_array($raw['detections'])) {
            $contados = [];
            foreach ($raw['detections'] as $det) {
                $label = $det['label'] ?? null;
                if ($label) {
                    $contados[$label] = ($contados[$label] ?? 0) + 1;
                }
            }
            foreach ($contados as $label => $count) {
                $detecciones[] = ['label' => $label, 'count' => $count];
            }
        }

        if (empty($detecciones)) {
            $this->yoloErrores[] = 'No se pudo interpretar detecciones del modelo.';
        }

        foreach ($detecciones as $det) {
            $label = $det['label'] ?? null;
            $count = (int) ($det['count'] ?? 0);
            if (!$label || $count <= 0) {
                continue;
            }

            $labelNorm = Str::lower($label);
            // Evitar columna inexistente etiqueta_ia; usamos coincidencia por nombre
            $articulo = Articulo::whereRaw('LOWER(nombre) = ?', [$labelNorm])->first();

            if (!$articulo) {
                $this->yoloErrores[] = "Sin coincidencia para '{$label}'";
                continue;
            }

            $index = collect($this->items)->search(fn($item) => (int) ($item['articulo_id'] ?? 0) === (int) $articulo->id);

            if ($index !== false) {
                $this->items[$index]['cantidad'] += $count;
            } else {
                $this->items[] = [
                    'articulo_id' => $articulo->id,
                    'nombre' => $articulo->nombre,
                    'cantidad' => $count,
                    'seguimiento' => $articulo->seguimiento,
                    'series' => [],
                ];
                $index = count($this->items) - 1;
            }

            if ($articulo->seguimiento === 'serie') {
                $this->asignarSeries($index);
            }

            $insertados++;
        }

        // si no hubo coincidencias, no elimines la fila vacia
        if ($insertados === 0 && empty($this->items)) {
            $this->addRow();
        }

        if ($insertados > 0 && count($this->items) > 1 && empty($this->items[0]['articulo_id'])) {
            $this->items = array_values(array_filter($this->items, fn($item) => !empty($item['articulo_id'])));
        }
    }

    protected function asignarSeries(int $index): void
    {
        $articuloId = $this->items[$index]['articulo_id'] ?? null;
        if (!$articuloId) {
            return;
        }

        $disponibles = collect($this->seriesDisponibles[$articuloId] ?? []);
        if ($disponibles->isEmpty()) {
            return;
        }

        $cantidad = (int) ($this->items[$index]['cantidad'] ?? 0);
        $yaSeleccionadas = collect($this->items[$index]['series'] ?? []);
        $faltantes = max(0, $cantidad - $yaSeleccionadas->count());

        $nuevas = $disponibles
            ->reject(fn($s) => $yaSeleccionadas->contains($s['id']))
            ->pluck('id')
            ->take($faltantes);

        $series = $yaSeleccionadas->merge($nuevas)->take($cantidad)->unique()->values()->all();
        $this->items[$index]['series'] = $series;

        if ($cantidad === 0 && count($series) > 0) {
            $this->items[$index]['cantidad'] = count($series);
        }
    }

    public function save()
    {
        $this->validate();

        foreach ($this->items as $idx => $item) {
            if (($item['seguimiento'] ?? '') === 'serie') {
                $cantidad = (int) ($item['cantidad'] ?? 0);
                $seleccionadas = is_array($item['series']) ? count($item['series']) : 0;

                if ($seleccionadas !== $cantidad) {
                    $this->addError("items.$idx.series", "Debes seleccionar exactamente {$cantidad} series.");
                    return;
                }
            }
        }

        DB::transaction(function () {
            $operacion = Operacion::create([
                'tipo' => 'asignacion',
                'evento_id' => $this->evento_id,
                'policia_id' => $this->policia_id,
                'actor_id' => Auth::id(),
                'fecha' => now(),
                'observaciones' => $this->observaciones,
            ]);

            foreach ($this->items as $item) {
                $detalle = OperacionDetalle::create([
                    'operacion_id' => $operacion->id,
                    'articulo_id' => $item['articulo_id'],
                    'cantidad' => $item['cantidad'],
                    'condicion' => 'bueno',
                ]);

                if (($item['seguimiento'] ?? '') === 'serie' && !empty($item['series'])) {
                    foreach ($item['series'] as $serieId) {
                        OperacionDetalleSerie::create([
                            'operacion_detalle_id' => $detalle->id,
                            'serie_id' => $serieId,
                        ]);

                        ArticuloSerie::where('id', $serieId)->update([
                            'estado' => 'asignado',
                            'operacion_detalle_id_actual' => $detalle->id,
                        ]);
                    }
                }
            }

            session()->flash('success', 'Prestamo registrado correctamente.');
        });

        return redirect()->route('prestamos.index');
    }

    public function render()
    {
        return view('livewire.prestamos.create');
    }
}
