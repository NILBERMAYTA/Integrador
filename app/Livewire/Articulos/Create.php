<?php

namespace App\Livewire\Articulos;

use Livewire\Component;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Articulo;
use App\Models\Categoria;
use App\Models\ArticuloSerie;
use App\Services\InventarioUnidadService;
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;

    // Modal de selección
    public bool $showModal = true;
    public ?string $mode = null; // 'cantidad' | 'serie' | null

    public int $step = 1;

    // Paso 1 – definición (ambos modos)
    public ?int $categoria_id = null;
    public string $nombre = '';
    public ?string $descripcion = null;
    public $foto;

    // Paso 2 – modo "cantidad"
    public ?float $cantidad_inicial = null;
    public ?float $stock_minimo = null;

    // Paso 2 – modo "serie"
    public string $codigo_serie = '';

    // Campos comunes Paso 2
    public ?string $fecha_ingreso = null;
    public ?string $obs_ingreso = null;

    public ?Articulo $articulo = null;

    private function unidadActualId(): ?int
    {
        return Auth::user()?->unidad_id;
    }

    // --- Seleccionar modo ---
    public function selectMode(string $mode)
    {
        $this->mode = $mode;
        $this->showModal = false;
        $this->step = 1;
        $this->reset(['nombre', 'categoria_id', 'descripcion', 'foto', 'cantidad_inicial', 'stock_minimo', 'codigo_serie', 'fecha_ingreso', 'obs_ingreso', 'articulo']);
    }

    public function closeModal()
    {
        $this->showModal = false;
        return redirect()->route('articulos.index');
    }

    // --- Validaciones dinámicas por modo ---
    protected function rulesStep1(): array
    {
        return [
            'categoria_id'  => ['required', 'integer', 'exists:categorias,id'],
            'nombre'        => [
                'required', 'string', 'max:100',
                Rule::unique('articulos', 'nombre')
                    ->where(fn($q) => $q->where('categoria_id', $this->categoria_id ?? 0)),
            ],
            'descripcion'   => ['nullable', 'string', 'max:500'],
            'foto'          => ['nullable', 'image', 'max:2048'],
        ];
    }

    protected function rulesStep2Cantidad(): array
    {
        return [
            'cantidad_inicial' => ['required', 'numeric', 'gt:0'],
            'stock_minimo'      => ['nullable', 'numeric', 'min:0'],
            'fecha_ingreso'    => ['nullable', 'date'],
            'obs_ingreso'      => ['nullable', 'string', 'max:500'],
        ];
    }

    protected function rulesStep2Serie(): array
    {
        return [
            'codigo_serie' => ['required', 'string', 'max:100', Rule::unique('articulo_series', 'codigo_serie')],
            'fecha_ingreso' => ['nullable', 'date'],
            'obs_ingreso'   => ['nullable', 'string', 'max:500'],
        ];
    }

    // --- Paso 1: crear artículo ---
    public function saveStep1()
    {
        $this->validate($this->rulesStep1());

        // Ajustar valores según modo
        $tipo = $this->mode === 'cantidad' ? 'consumible' : 'reutilizable';
        $seguimiento = $this->mode === 'cantidad' ? 'cantidad' : 'serie';

        $this->articulo = Articulo::create([
            'categoria_id'  => $this->categoria_id,
            'nombre'        => $this->nombre,
            'unidad_medida' => null,
            'descripcion'   => $this->descripcion,
            'foto_path'     => $this->foto ? $this->foto->store('articulos', 'public') : null,
            'tipo'          => $tipo,
            'seguimiento'   => $seguimiento,
        ]);

        $this->step = 2;
        $msg = $this->mode === 'cantidad'
            ? 'Artículo creado. Ahora registra el stock inicial.'
            : 'Artículo creado. Ahora registra la primera unidad.';
        session()->flash('success', $msg);
    }

    // --- Paso 2: registrar stock inicial (cantidad) ---
    public function saveStep2Cantidad(InventarioUnidadService $inventario)
    {
        $this->validate($this->rulesStep2Cantidad());

        if (!$this->articulo || !$this->articulo->id) {
            $this->addError('articulo', 'Debes completar el Paso 1 antes de registrar el stock inicial.');
            return;
        }

        if (!Auth::check()) {
            $this->addError('auth', 'Debes iniciar sesión para registrar movimientos.');
            return;
        }

        $unidadId = $this->unidadActualId();
        if (!$unidadId) {
            $this->addError('unidad', 'El usuario actual no tiene una unidad asignada.');
            return;
        }

        DB::transaction(function () use ($unidadId, $inventario) {
            $now = now();
            $fecha = $this->fecha_ingreso
                ? \Carbon\Carbon::parse($this->fecha_ingreso)
                : $now;

            // Operación principal (ajuste inicial)
            $opId = DB::table('operaciones')->insertGetId([
                'tipo' => 'ajuste',
                'evento_id' => null,
                'usuario_destino_id' => null,
                'actor_id' => Auth::id(),
                'unidad_id' => $unidadId,
                'fecha' => $fecha,
                'observaciones' => $this->obs_ingreso ?: 'Ingreso inicial',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Detalle principal
            DB::table('operacion_detalles')->insertGetId([
                'operacion_id' => $opId,
                'articulo_id'  => $this->articulo->id,
                'cantidad'     => $this->cantidad_inicial,
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);

            $inventario->addInitialStock($unidadId, $this->articulo->id, (float) $this->cantidad_inicial);
            $inventario->setMinimumStock($unidadId, $this->articulo->id, (float) ($this->stock_minimo ?? 0));
        });

    session()->flash('success', "Stock inicial creado: {$this->cantidad_inicial} unidades.");
    // Después de crear por cantidad volvemos al listado por defecto
    return redirect()->route('articulos.index');
    }

    // --- Paso 2: registrar serie (una unidad) ---
    public function saveStep2Serie()
    {
        $this->validate($this->rulesStep2Serie());

        if (!$this->articulo || !$this->articulo->id) {
            $this->addError('articulo', 'Debes completar el Paso 1 antes de registrar la serie.');
            return;
        }

        if (!Auth::check()) {
            $this->addError('auth', 'Debes iniciar sesión para registrar movimientos.');
            return;
        }

        $unidadId = $this->unidadActualId();
        if (!$unidadId) {
            $this->addError('unidad', 'El usuario actual no tiene una unidad asignada.');
            return;
        }

        DB::transaction(function () use ($unidadId) {
            $now = now();
            $fecha = $this->fecha_ingreso
                ? \Carbon\Carbon::parse($this->fecha_ingreso)
                : $now;

            // Operación principal (ajuste inicial)
            $opId = DB::table('operaciones')->insertGetId([
                'tipo' => 'ajuste',
                'evento_id' => null,
                'usuario_destino_id' => null,
                'actor_id' => Auth::id(),
                'unidad_id' => $unidadId,
                'fecha' => $fecha,
                'observaciones' => $this->obs_ingreso ?: 'Ingreso inicial',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Detalle con cantidad = 1
            $detId = DB::table('operacion_detalles')->insertGetId([
                'operacion_id' => $opId,
                'articulo_id'  => $this->articulo->id,
                'cantidad'     => 1,
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);

            // Crear serie
            $serieId = DB::table('articulo_series')->insertGetId([
                'articulo_id'   => $this->articulo->id,
                'unidad_id'     => $unidadId,
                'codigo_serie'  => trim($this->codigo_serie),
                'estado'        => 'disponible',
                'observaciones' => null,
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);

            // Vincular serie al detalle
            DB::table('operacion_detalle_series')->insert([
                'operacion_detalle_id' => $detId,
                'serie_id'             => $serieId,
                'created_at'           => $now,
                'updated_at'           => $now,
            ]);
        });

        session()->flash('success', "Unidad registrada con serie: {$this->codigo_serie}.");
        return redirect()->route('articulos.index', $this->articulo);
    }

    public function render()
    {
        $categorias = Categoria::orderBy('nombre')->get(['id', 'nombre']);
        return view('livewire.articulos.create', compact('categorias'));
    }
}
