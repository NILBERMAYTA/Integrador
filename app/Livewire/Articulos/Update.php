<?php

namespace App\Livewire\Articulos;

use App\Models\Articulo;
use App\Models\ArticuloSerie;
use App\Models\Categoria;
use App\Models\InventarioUnidadArticulo;
use App\Models\OperacionDetalle;
use App\Models\Unidad;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

class Update extends Component
{
    use WithFileUploads;

    public Articulo $articulo;

    public $categoria_id;
    public $nombre;
    public $descripcion;
    public $tipo;
    public $foto;
    public $foto_actual = null;
    #[Url(except: 'datos')]
    public string $tab = 'datos';
    public ?int $nueva_serie_unidad_id = null;
    public string $nueva_serie_codigo = '';
    public string $nueva_serie_estado = 'disponible';
    public string $nueva_serie_condicion = 'bueno';
    public ?string $nueva_serie_observaciones = null;
    public array $seriesForm = [];
    public array $stockMinimos = [];

    public function mount(Articulo $articulo)
    {
        $this->articulo = $articulo;
        $this->categoria_id = $articulo->categoria_id;
        $this->nombre = $articulo->nombre;
        $this->descripcion = $articulo->descripcion;
        $this->tipo = $articulo->tipo;
        $this->foto_actual = $articulo->foto_path;
    }

    protected function rules()
    {
        return [
            'categoria_id' => ['required', 'integer', 'exists:categorias,id'],
            'nombre' => [
                'required',
                'string',
                'max:100',
                Rule::unique('articulos', 'nombre')
                    ->ignore($this->articulo->id)
                    ->where(fn ($q) => $q->where('categoria_id', $this->categoria_id ?: 0)),
            ],
            'descripcion' => ['nullable', 'string'],
            'tipo' => ['required', Rule::in(['reutilizable', 'consumible'])],
            'foto' => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function actualizararticulo()
    {
        $data = $this->validate();

        $payload = [
            'categoria_id' => $data['categoria_id'],
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?: null,
            'tipo' => $data['tipo'],
            'seguimiento' => $data['tipo'] === 'reutilizable' ? 'serie' : 'cantidad',
        ];

        if (! empty($data['foto'])) {
            if (! empty($this->articulo->foto_path)) {
                Storage::disk('public')->delete($this->articulo->foto_path);
            }

            $payload['foto_path'] = $data['foto']->store('articulos', 'public');
            $this->foto_actual = $payload['foto_path'];
        }

        $this->articulo->update($payload);

        session()->flash('success', 'Articulo actualizado correctamente.');
    }

    public function guardarSerie(): void
    {
        abort_unless(auth()->user()?->can('articulos.manage'), 403);
        abort_unless($this->articulo->isSerializado(), 404);

        $data = $this->validate([
            'nueva_serie_unidad_id' => ['required', 'integer', 'exists:unidades,id'],
            'nueva_serie_codigo' => ['required', 'string', 'max:100', Rule::unique('articulo_series', 'codigo_serie')],
            'nueva_serie_estado' => ['required', Rule::in($this->estadosSerie())],
            'nueva_serie_condicion' => ['required', Rule::in($this->condicionesSerie())],
            'nueva_serie_observaciones' => ['nullable', 'string', 'max:500'],
        ]);

        ArticuloSerie::create([
            'articulo_id' => $this->articulo->id,
            'unidad_id' => $data['nueva_serie_unidad_id'],
            'codigo_serie' => trim($data['nueva_serie_codigo']),
            'estado' => $data['nueva_serie_estado'],
            'condicion_actual' => $data['nueva_serie_condicion'],
            'observaciones' => $data['nueva_serie_observaciones'] ?: null,
        ]);

        $this->reset(['nueva_serie_codigo', 'nueva_serie_observaciones']);
        $this->nueva_serie_estado = 'disponible';
        $this->nueva_serie_condicion = 'bueno';
        session()->flash('success', 'Serie agregada correctamente.');
    }

    public function actualizarSerie(int $serieId): void
    {
        abort_unless(auth()->user()?->can('articulos.manage'), 403);
        abort_unless($this->articulo->isSerializado(), 404);

        $data = $this->validate([
            "seriesForm.$serieId.codigo_serie" => [
                'required',
                'string',
                'max:100',
                Rule::unique('articulo_series', 'codigo_serie')->ignore($serieId),
            ],
            "seriesForm.$serieId.unidad_id" => ['required', 'integer', 'exists:unidades,id'],
            "seriesForm.$serieId.estado" => ['required', Rule::in($this->estadosSerie())],
            "seriesForm.$serieId.condicion_actual" => ['required', Rule::in($this->condicionesSerie())],
            "seriesForm.$serieId.observaciones" => ['nullable', 'string', 'max:500'],
        ]);

        $payload = $data['seriesForm'][$serieId];

        ArticuloSerie::query()
            ->where('articulo_id', $this->articulo->id)
            ->whereKey($serieId)
            ->update([
                'codigo_serie' => trim($payload['codigo_serie']),
                'unidad_id' => $payload['unidad_id'],
                'estado' => $payload['estado'],
                'condicion_actual' => $payload['condicion_actual'],
                'observaciones' => $payload['observaciones'] ?: null,
            ]);

        session()->flash('success', 'Serie actualizada correctamente.');
    }

    public function actualizarStockMinimo(int $inventarioId): void
    {
        abort_unless(auth()->user()?->can('articulos.manage'), 403);
        abort_unless($this->articulo->isCantidad(), 404);

        $data = $this->validate([
            "stockMinimos.$inventarioId" => ['required', 'numeric', 'min:0'],
        ]);

        InventarioUnidadArticulo::query()
            ->where('articulo_id', $this->articulo->id)
            ->whereKey($inventarioId)
            ->update([
                'stock_minimo' => (float) $data['stockMinimos'][$inventarioId],
            ]);

        session()->flash('success', 'Stock minimo actualizado correctamente.');
    }

    public function render()
    {
        if ($this->articulo->isSerializado() && $this->tab === 'stock') {
            $this->tab = 'series';
        }

        if ($this->articulo->isCantidad() && $this->tab === 'series') {
            $this->tab = 'stock';
        }

        $categorias = Categoria::orderBy('nombre')->get(['id', 'nombre']);
        $unidades = Unidad::orderBy('nombre')->get(['id', 'nombre', 'sigla']);
        $series = collect();
        $inventarios = collect();
        $movimientos = collect();

        if ($this->articulo->isSerializado()) {
            $series = ArticuloSerie::query()
                ->with('unidad:id,nombre,sigla')
                ->where('articulo_id', $this->articulo->id)
                ->orderBy('codigo_serie')
                ->get();

            foreach ($series as $serie) {
                $this->seriesForm[$serie->id] = $this->seriesForm[$serie->id] ?? [
                    'codigo_serie' => $serie->codigo_serie,
                    'unidad_id' => $serie->unidad_id,
                    'estado' => $serie->estado,
                    'condicion_actual' => $serie->condicion_actual,
                    'observaciones' => $serie->observaciones,
                ];
            }
        } else {
            $inventarios = InventarioUnidadArticulo::query()
                ->with('unidad:id,nombre,sigla')
                ->where('articulo_id', $this->articulo->id)
                ->orderBy('unidad_id')
                ->get();

            foreach ($inventarios as $inventario) {
                $this->stockMinimos[$inventario->id] = $this->stockMinimos[$inventario->id] ?? (string) $inventario->stock_minimo;
            }
        }

        $movimientos = OperacionDetalle::query()
            ->with('operacion')
            ->where('articulo_id', $this->articulo->id)
            ->latest('created_at')
            ->limit(12)
            ->get();

        return view('livewire.articulos.update', compact('categorias', 'unidades', 'series', 'inventarios', 'movimientos'))
            ->with(['articulo' => $this->articulo]);
    }

    public function estadosSerie(): array
    {
        return ['disponible', 'asignado', 'en_mantenimiento', 'observado', 'inoperativo', 'dado_de_baja', 'perdido', 'robado'];
    }

    public function condicionesSerie(): array
    {
        return ['bueno', 'con_defectos', 'malo', 'inoperativo'];
    }
}
