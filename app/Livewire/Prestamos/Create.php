<?php

namespace App\Livewire\Prestamos;

use App\Models\Articulo;
use App\Models\ArticuloSerie;
use App\Models\Evento;
use App\Models\Operacion;
use App\Models\OperacionDetalle;
use App\Models\OperacionDetalleSerie;
use App\Models\User;
use App\Services\InventarioUnidadService;
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

    public string $qrMensaje = '';

    public string $qrError = '';

    protected $listeners = [
        'yolo-detecciones' => 'agregarDesdeYolo',
    ];

    private function unidadActualId(): ?int
    {
        return auth()->user()?->unidad_id;
    }

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
        abort_unless(auth()->user()?->isAdmin() || auth()->user()?->isFurriel(), 403);

        $unidadId = $this->unidadActualId();
        abort_if(! $unidadId, 403, 'El usuario actual no tiene una unidad asignada.');

        $this->eventos = Evento::orderBy('id', 'desc')->get();
        $this->policias = User::query()
            ->where('role', 'policia')
            ->where('unidad_id', $unidadId)
            ->orderBy('name')
            ->get();
        $this->articulos = Articulo::orderBy('nombre')->get();
        $this->seriesDisponibles = ArticuloSerie::query()
            ->select('id', 'articulo_id', 'codigo_serie')
            ->where('unidad_id', $unidadId)
            ->where('estado', 'disponible')
            ->orderBy('codigo_serie')
            ->get()
            ->groupBy('articulo_id')
            ->map(fn ($group) => $group->map(fn ($serie) => [
                'id' => $serie->id,
                'codigo_serie' => $serie->codigo_serie,
            ])->values()->all())
            ->toArray();

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
            $articulo = $value ? collect($this->articulos)->firstWhere('id', (int) $value) : null;

            $this->items[$index]['nombre'] = $articulo->nombre ?? '';
            $this->items[$index]['seguimiento'] = $articulo?->isSerializado() ? 'serie' : 'cantidad';
            $this->items[$index]['series'] = [];
        }
    }

    public function procesarQr(string $contenido): void
    {
        $this->qrMensaje = '';
        $this->qrError = '';

        $payload = $this->decodificarQr($contenido);
        if (! $payload) {
            $this->qrError = 'El codigo QR no tiene un formato reconocido.';

            return;
        }

        $unidadId = $this->unidadActualId();

        if (($payload['type'] ?? null) === 'user') {
            $policia = User::query()
                ->whereKey((int) ($payload['id'] ?? 0))
                ->where('role', 'policia')
                ->where('unidad_id', $unidadId)
                ->first();

            if (! $policia) {
                $this->qrError = 'El usuario del QR no es un policia habilitado de esta unidad.';

                return;
            }

            $this->policia_id = $policia->id;
            $this->qrMensaje = 'Policia seleccionado: '.$policia->name;

            return;
        }

        if (($payload['type'] ?? null) === 'serie') {
            $serie = ArticuloSerie::query()
                ->with('articulo')
                ->whereKey((int) ($payload['id'] ?? 0))
                ->where('unidad_id', $unidadId)
                ->where('estado', 'disponible')
                ->first();

            if (! $serie || ! $serie->articulo?->isSerializado()) {
                $this->qrError = 'La serie no pertenece a esta unidad o no esta disponible.';

                return;
            }

            foreach ($this->items as $item) {
                if (in_array($serie->id, $item['series'] ?? [], true)) {
                    $this->qrError = 'La serie ya fue agregada al prestamo.';

                    return;
                }
            }

            $index = collect($this->items)->search(
                fn ($item) => (int) ($item['articulo_id'] ?? 0) === $serie->articulo_id
                    && ($item['seguimiento'] ?? '') === 'serie'
            );

            if ($index === false) {
                $index = $this->indiceFilaVacia();
                $row = [
                    'articulo_id' => $serie->articulo_id,
                    'nombre' => $serie->articulo->nombre,
                    'cantidad' => 1,
                    'seguimiento' => 'serie',
                    'series' => [$serie->id],
                ];

                if ($index === null) {
                    $this->items[] = $row;
                } else {
                    $this->items[$index] = $row;
                }
            } else {
                $this->items[$index]['series'][] = $serie->id;
                $this->items[$index]['series'] = array_values(array_unique($this->items[$index]['series']));
                $this->items[$index]['cantidad'] = count($this->items[$index]['series']);
            }

            $this->qrMensaje = "Serie {$serie->codigo_serie} agregada.";

            return;
        }

        if (($payload['type'] ?? null) === 'articulo') {
            $articulo = Articulo::find((int) ($payload['id'] ?? 0));
            if (! $articulo || $articulo->isSerializado()) {
                $this->qrError = 'Para articulos reutilizables debes escanear el QR de la serie.';

                return;
            }

            $index = collect($this->items)->search(
                fn ($item) => (int) ($item['articulo_id'] ?? 0) === $articulo->id
                    && ($item['seguimiento'] ?? '') === 'cantidad'
            );

            if ($index === false) {
                $index = $this->indiceFilaVacia();
                $row = [
                    'articulo_id' => $articulo->id,
                    'nombre' => $articulo->nombre,
                    'cantidad' => 1,
                    'seguimiento' => 'cantidad',
                    'series' => [],
                ];

                if ($index === null) {
                    $this->items[] = $row;
                } else {
                    $this->items[$index] = $row;
                }
            } else {
                $this->items[$index]['cantidad'] = (int) $this->items[$index]['cantidad'] + 1;
            }

            $this->qrMensaje = "Articulo {$articulo->nombre} agregado.";

            return;
        }

        $this->qrError = 'Este QR no corresponde a un usuario, articulo o serie.';
    }

    private function decodificarQr(string $contenido): ?array
    {
        $payload = json_decode(trim($contenido), true);

        return is_array($payload) ? $payload : null;
    }

    private function indiceFilaVacia(): ?int
    {
        $index = collect($this->items)->search(fn ($item) => empty($item['articulo_id']));

        return $index === false ? null : (int) $index;
    }

    public function agregarDesdeYolo($payload = [])
    {
        $raw = $payload['detecciones'] ?? [];
        $this->yoloErrores = [];
        $insertados = 0;
        $detecciones = [];

        if (is_array($raw) && isset($raw['summary']) && is_array($raw['summary'])) {
            foreach ($raw['summary'] as $label => $count) {
                $detecciones[] = ['label' => $label, 'count' => (int) $count];
            }
        }

        if (empty($detecciones) && is_array($raw) && isset($raw[0]['label'])) {
            foreach ($raw as $det) {
                $detecciones[] = [
                    'label' => $det['label'] ?? null,
                    'count' => (int) ($det['count'] ?? 1),
                ];
            }
        }

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
            if (! $label || $count <= 0) {
                continue;
            }

            $labelNorm = Str::lower($label);
            $articulo = Articulo::whereRaw('LOWER(nombre) = ?', [$labelNorm])->first();

            if (! $articulo) {
                $this->yoloErrores[] = "Sin coincidencia para '{$label}'";

                continue;
            }

            $index = collect($this->items)->search(fn ($item) => (int) ($item['articulo_id'] ?? 0) === (int) $articulo->id);

            if ($index !== false) {
                $this->items[$index]['cantidad'] += $count;
            } else {
                $this->items[] = [
                    'articulo_id' => $articulo->id,
                    'nombre' => $articulo->nombre,
                    'cantidad' => $count,
                    'seguimiento' => $articulo->isSerializado() ? 'serie' : 'cantidad',
                    'series' => [],
                ];
                $index = count($this->items) - 1;
            }

            if ($articulo->isSerializado()) {
                $this->asignarSeries($index);
            }

            $insertados++;
        }

        if ($insertados === 0 && empty($this->items)) {
            $this->addRow();
        }

        if ($insertados > 0 && count($this->items) > 1 && empty($this->items[0]['articulo_id'])) {
            $this->items = array_values(array_filter($this->items, fn ($item) => ! empty($item['articulo_id'])));
        }
    }

    protected function asignarSeries(int $index): void
    {
        $articuloId = $this->items[$index]['articulo_id'] ?? null;
        if (! $articuloId) {
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
            ->reject(fn ($s) => $yaSeleccionadas->contains($s['id']))
            ->pluck('id')
            ->take($faltantes);

        $this->items[$index]['series'] = $yaSeleccionadas->merge($nuevas)->take($cantidad)->unique()->values()->all();
    }

    public function save(InventarioUnidadService $inventario)
    {
        $this->validate();

        $unidadId = $this->unidadActualId();
        if (! $unidadId) {
            $this->addError('unidad', 'El usuario actual no tiene una unidad asignada.');

            return;
        }

        $destino = User::query()->whereKey($this->policia_id)->where('unidad_id', $unidadId)->first();
        if (! $destino) {
            $this->addError('policia_id', 'El usuario destino no pertenece a la misma unidad.');

            return;
        }

        foreach ($this->items as $idx => $item) {
            $articulo = Articulo::find($item['articulo_id']);
            if (! $articulo) {
                continue;
            }

            if (($item['seguimiento'] ?? '') === 'serie') {
                $cantidad = (int) ($item['cantidad'] ?? 0);
                $seleccionadas = is_array($item['series']) ? count($item['series']) : 0;

                if ($seleccionadas !== $cantidad) {
                    $this->addError("items.$idx.series", "Debes seleccionar exactamente {$cantidad} series.");

                    return;
                }

                $seriesValidas = ArticuloSerie::query()
                    ->whereIn('id', $item['series'])
                    ->where('articulo_id', $articulo->id)
                    ->where('unidad_id', $unidadId)
                    ->where('estado', 'disponible')
                    ->count();

                if ($seriesValidas !== $cantidad) {
                    $this->addError("items.$idx.series", 'Alguna serie no pertenece a la unidad o no esta disponible.');

                    return;
                }
            } else {
                $stock = $inventario->ensure($unidadId, $articulo->id);
                if ((float) $stock->cantidad_disponible < (float) $item['cantidad']) {
                    $this->addError("items.$idx.cantidad", 'Stock insuficiente en la unidad para el articulo seleccionado.');

                    return;
                }
            }
        }

        DB::transaction(function () use ($unidadId, $inventario) {
            $operacion = Operacion::create([
                'tipo' => 'asignacion',
                'evento_id' => $this->evento_id,
                'usuario_destino_id' => $this->policia_id,
                'actor_id' => Auth::id(),
                'unidad_id' => $unidadId,
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

                if (($item['seguimiento'] ?? '') === 'serie' && ! empty($item['series'])) {
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
                } else {
                    $inventario->assign(
                        $unidadId,
                        Articulo::findOrFail($item['articulo_id']),
                        (float) $item['cantidad']
                    );
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
