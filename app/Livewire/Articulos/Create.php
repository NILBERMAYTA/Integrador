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

    // Condiciones físicas válidas para una serie (condicion_fisica_serie_enum)
    public const CONDICIONES = ['bueno', 'con_defectos', 'malo', 'inoperativo'];

    // Modal de selección
    public bool $showModal = true;
    public ?string $mode = null; // 'cantidad' | 'serie' | null

    public int $step = 1;

    // Paso 1 – definición (ambos modos)
    public ?int $categoria_id = null;
    public string $nombre = '';
    public ?string $unidad_medida = null;
    public ?string $descripcion = null;
    public $foto;

    // Paso 2 – modo "cantidad"
    public ?float $cantidad_inicial = null;
    public ?float $stock_minimo = null;

    // Paso 2 – modo "serie" (registro por lote)
    public string $series_input = '';          // un código por línea
    public string $condicion_inicial = 'bueno';

    // Generador opcional de series (prefijo + rango numérico)
    public ?string $serie_prefijo = null;
    public ?int $serie_inicio = 1;
    public ?int $serie_cantidad = null;
    public int $serie_relleno = 4;

    // Campos comunes Paso 2
    public ?string $fecha_ingreso = null;
    public ?string $obs_ingreso = null;

    public ?Articulo $articulo = null;

    private function unidadActualId(): ?int
    {
        return Auth::user()?->unidad_id;
    }

    public function mount()
    {
        // La seleccion del tipo de registro se hace en el listado (con la lista
        // de fondo). Si se entra a /articulos/create sin un modo valido, se
        // redirige al listado en lugar de mostrar un modal sobre una pagina vacia.
        $mode = request()->query('mode');

        if (! in_array($mode, ['cantidad', 'serie'], true)) {
            return redirect()->route('articulos.index');
        }

        $this->mode = $mode;
        $this->showModal = false;
        $this->step = 1;
    }

    // --- Seleccionar modo ---
    public function selectMode(string $mode)
    {
        $this->mode = $mode;
        $this->showModal = false;
        $this->step = 1;
        $this->reset([
            'nombre', 'categoria_id', 'descripcion', 'foto', 'unidad_medida',
            'cantidad_inicial', 'stock_minimo',
            'series_input', 'condicion_inicial',
            'serie_prefijo', 'serie_inicio', 'serie_cantidad', 'serie_relleno',
            'fecha_ingreso', 'obs_ingreso', 'articulo',
        ]);
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
            'unidad_medida' => ['nullable', 'string', 'max:20'],
            'foto'          => ['nullable', 'image', 'max:2048'],
        ];
    }

    protected function rulesStep2Cantidad(): array
    {
        return [
            'cantidad_inicial' => ['required', 'numeric', 'gt:0'],
            'stock_minimo'     => ['nullable', 'numeric', 'min:0'],
            'fecha_ingreso'    => ['nullable', 'date'],
            'obs_ingreso'      => ['nullable', 'string', 'max:500'],
        ];
    }

    protected function rulesStep2Serie(): array
    {
        return [
            'condicion_inicial' => ['required', Rule::in(self::CONDICIONES)],
            'fecha_ingreso'     => ['nullable', 'date'],
            'obs_ingreso'       => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Convierte el textarea en una lista limpia de códigos (sin vacíos).
     * Acepta saltos de línea, comas o punto y coma como separadores.
     */
    private function parsedSeries(): array
    {
        $raw = preg_split('/[\r\n,;]+/', (string) $this->series_input) ?: [];

        return collect($raw)
            ->map(fn ($code) => trim($code))
            ->filter(fn ($code) => $code !== '')
            ->values()
            ->all();
    }

    /** Códigos duplicados dentro del propio lote. */
    private function duplicatesInBatch(array $codes): array
    {
        return collect($codes)
            ->countBy()
            ->filter(fn ($count) => $count > 1)
            ->keys()
            ->all();
    }

    // --- Paso 1: crear artículo ---
    public function saveStep1()
    {
        $this->validate($this->rulesStep1());

        $tipo = $this->mode === 'cantidad' ? 'consumible' : 'reutilizable';
        $seguimiento = $this->mode === 'cantidad' ? 'cantidad' : 'serie';

        $this->articulo = Articulo::create([
            'categoria_id'  => $this->categoria_id,
            'nombre'        => $this->nombre,
            'unidad_medida' => $this->unidad_medida ?: null,
            'stock_minimo'  => $this->mode === 'cantidad' ? (float) ($this->stock_minimo ?? 0) : 0,
            'descripcion'   => $this->descripcion,
            'foto_path'     => $this->foto ? $this->foto->store('articulos', 'public') : null,
            'tipo'          => $tipo,
            'seguimiento'   => $seguimiento,
        ]);

        $this->step = 2;
        $msg = $this->mode === 'cantidad'
            ? 'Artículo creado. Ahora registra el stock inicial.'
            : 'Artículo creado. Ahora registra las unidades por serie.';
        session()->flash('success', $msg);
    }

    // --- Generador opcional de códigos de serie ---
    public function generarSeries()
    {
        $cantidad = (int) ($this->serie_cantidad ?? 0);

        if ($cantidad < 1) {
            $this->addError('serie_cantidad', 'Indica cuántas series deseas generar.');
            return;
        }

        if ($cantidad > 500) {
            $this->addError('serie_cantidad', 'Puedes generar como máximo 500 series por lote.');
            return;
        }

        $inicio = (int) ($this->serie_inicio ?? 1);
        $relleno = max(0, min(12, (int) $this->serie_relleno));
        $prefijo = trim((string) $this->serie_prefijo);

        $generadas = [];
        for ($i = 0; $i < $cantidad; $i++) {
            $numero = $inicio + $i;
            $generadas[] = $prefijo . str_pad((string) $numero, $relleno, '0', STR_PAD_LEFT);
        }

        // Se fusiona con lo que ya haya en el textarea, sin duplicar.
        $merged = array_values(array_unique(array_merge($this->parsedSeries(), $generadas)));
        $this->series_input = implode("\n", $merged);
    }

    public function limpiarSeries()
    {
        $this->series_input = '';
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
            $fecha = $this->fecha_ingreso ? \Carbon\Carbon::parse($this->fecha_ingreso) : $now;

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

            DB::table('operacion_detalles')->insertGetId([
                'operacion_id' => $opId,
                'articulo_id'  => $this->articulo->id,
                'cantidad'     => $this->cantidad_inicial,
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);

            $inventario->addInitialStock($unidadId, $this->articulo->id, (float) $this->cantidad_inicial);
            $inventario->setMinimumStock($unidadId, $this->articulo->id, (float) ($this->stock_minimo ?? 0));

            // Mantener tambien el minimo de referencia a nivel de articulo.
            $this->articulo->forceFill(['stock_minimo' => (float) ($this->stock_minimo ?? 0)])->save();
        });

        session()->flash('success', "Stock inicial creado: {$this->cantidad_inicial} unidades.");
        return redirect()->route('articulos.index');
    }

    // --- Paso 2: registrar series (lote) ---
    public function saveStep2Serie()
    {
        $this->validate($this->rulesStep2Serie());

        if (!$this->articulo || !$this->articulo->id) {
            $this->addError('articulo', 'Debes completar el Paso 1 antes de registrar las series.');
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

        $codes = $this->parsedSeries();

        if (empty($codes)) {
            $this->addError('series_input', 'Ingresa al menos un código de serie.');
            return;
        }

        // Longitud máxima por código (columna codigo_serie VARCHAR(100)).
        $largos = collect($codes)->filter(fn ($c) => mb_strlen($c) > 100)->all();
        if (!empty($largos)) {
            $this->addError('series_input', 'Algunos códigos superan los 100 caracteres permitidos.');
            return;
        }

        // Duplicados dentro del mismo lote.
        $dupLote = $this->duplicatesInBatch($codes);
        if (!empty($dupLote)) {
            $this->addError('series_input', 'Hay códigos repetidos en la lista: ' . implode(', ', array_slice($dupLote, 0, 10)) . (count($dupLote) > 10 ? '…' : ''));
            return;
        }

        // Duplicados contra la base de datos (incluye soft-deleted: el índice único los conserva).
        $existentes = ArticuloSerie::withTrashed()
            ->whereIn('codigo_serie', $codes)
            ->pluck('codigo_serie')
            ->all();
        if (!empty($existentes)) {
            $this->addError('series_input', 'Estos códigos ya están registrados: ' . implode(', ', array_slice($existentes, 0, 10)) . (count($existentes) > 10 ? '…' : ''));
            return;
        }

        // Una condición "inoperativo" deja la serie fuera de disponibilidad.
        $estado = $this->condicion_inicial === 'inoperativo' ? 'inoperativo' : 'disponible';

        DB::transaction(function () use ($unidadId, $codes, $estado) {
            $now = now();
            $fecha = $this->fecha_ingreso ? \Carbon\Carbon::parse($this->fecha_ingreso) : $now;

            // 1) Operación de ajuste (ingreso inicial).
            $opId = DB::table('operaciones')->insertGetId([
                'tipo' => 'ajuste',
                'evento_id' => null,
                'usuario_destino_id' => null,
                'actor_id' => Auth::id(),
                'unidad_id' => $unidadId,
                'fecha' => $fecha,
                'observaciones' => $this->obs_ingreso ?: 'Ingreso inicial de series',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // 2) Un único detalle con cantidad = N series.
            $detId = DB::table('operacion_detalles')->insertGetId([
                'operacion_id' => $opId,
                'articulo_id'  => $this->articulo->id,
                'cantidad'     => count($codes),
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);

            // 3) N series + 4) N enlaces detalle-serie.
            foreach ($codes as $codigo) {
                $serieId = DB::table('articulo_series')->insertGetId([
                    'articulo_id'                 => $this->articulo->id,
                    'unidad_id'                   => $unidadId,
                    'codigo_serie'                => $codigo,
                    'estado'                      => $estado,
                    'condicion_actual'            => $this->condicion_inicial,
                    'operacion_detalle_id_actual' => $detId,
                    'observaciones'               => null,
                    'created_at'                  => $now,
                    'updated_at'                  => $now,
                ]);

                DB::table('operacion_detalle_series')->insert([
                    'operacion_detalle_id' => $detId,
                    'serie_id'             => $serieId,
                    'created_at'           => $now,
                    'updated_at'           => $now,
                ]);
            }
        });

        $total = count($codes);
        session()->flash('success', $total === 1
            ? "Unidad registrada con serie: {$codes[0]}."
            : "{$total} unidades registradas por serie.");

        return redirect()->route('articulos.index');
    }

    public function render()
    {
        $categorias = Categoria::orderBy('nombre')->get(['id', 'nombre']);
        $seriesCount = count($this->parsedSeries());

        return view('livewire.articulos.create', compact('categorias', 'seriesCount'));
    }
}
