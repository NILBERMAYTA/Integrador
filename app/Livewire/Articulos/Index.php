<?php

namespace App\Livewire\Articulos;

use App\Models\Articulo;
use App\Models\ArticuloSerie;
use App\Models\Categoria;
use App\Models\InventarioUnidadArticulo;
use App\Models\Unidad;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    protected $listeners = [
        'closeAjuste' => 'closeAjuste',
    ];

    public ?Articulo $ajusteArticulo = null;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $categoria = '';

    #[Url(except: '')]
    public string $tipo = '';

    #[Url(except: '')]
    public string $unidad = '';

    #[Url(except: '')]
    public string $estado = '';

    #[Url(except: 'operativo_nombre')]
    public string $sortField = 'operativo_nombre';

    #[Url(except: 'asc')]
    public string $sortDirection = 'asc';

    #[Url(except: 'table')]
    public string $viewMode = 'table';

    public int $perPage = 12;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategoria(): void
    {
        $this->resetPage();
    }

    public function updatedTipo(): void
    {
        $this->resetPage();
    }

    public function updatedUnidad(): void
    {
        $this->resetPage();
    }

    public function updatedEstado(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'categoria', 'tipo', 'unidad', 'estado']);
        $this->sortField = 'operativo_nombre';
        $this->sortDirection = 'asc';
        $this->resetPage();
    }

    public function confirmarEliminacion(int $id): void
    {
        try {
            $art = Articulo::findOrFail($id);
            $art->delete();
            session()->flash('success', 'Articulo eliminado exitosamente.');
        } catch (\Throwable $e) {
            session()->flash('error', 'Error al eliminar el articulo: '.$e->getMessage());
        }
    }

    public function abrirAjuste(int $id): void
    {
        $this->ajusteArticulo = Articulo::find($id);
    }

    public function closeAjuste(): void
    {
        $this->ajusteArticulo = null;
    }

    public function exportPdf()
    {
        $rows = collect($this->buildOperationalRows());

        $pdf = PDF::loadView('reports.articulos', [
            'articulos' => $rows,
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'articulos_'.now()->format('Ymd_His').'.pdf'
        );
    }

    public function render()
    {
        $baseQuery = $this->operationalRowsQuery(false);
        $stats = DB::query()
            ->fromSub(clone $baseQuery, 'operational_rows')
            ->selectRaw("
                COUNT(*) AS total,
                COUNT(*) FILTER (
                    WHERE row_type = 'reutilizable' AND condicion = 'bueno'
                ) AS bueno,
                COUNT(*) FILTER (
                    WHERE row_type = 'reutilizable' AND condicion = 'con_defectos'
                ) AS con_defectos,
                COUNT(*) FILTER (
                    WHERE row_type = 'reutilizable' AND condicion = 'malo'
                ) AS malo,
                COUNT(*) FILTER (
                    WHERE row_type = 'reutilizable' AND condicion = 'inoperativo'
                ) AS inoperativo
            ")
            ->first();

        $resumenCondicion = [
            'bueno' => (int) ($stats->bueno ?? 0),
            'con_defectos' => (int) ($stats->con_defectos ?? 0),
            'malo' => (int) ($stats->malo ?? 0),
            'inoperativo' => (int) ($stats->inoperativo ?? 0),
            'total' => (int) ($stats->total ?? 0),
        ];

        $rowsQuery = DB::query()->fromSub(
            $this->operationalRowsQuery(true),
            'operational_rows'
        );
        $sortField = in_array($this->sortField, ['operativo_nombre', 'estado', 'condicion'], true)
            ? $this->sortField
            : 'operativo_nombre';
        $direction = $this->sortDirection === 'desc' ? 'desc' : 'asc';

        $articulos = $rowsQuery
            ->orderBy($sortField, $direction)
            ->orderBy('id')
            ->paginate($this->perPage)
            ->through(function ($row) {
                $data = (array) $row;
                $data['foto_url'] = ! empty($data['foto_path'])
                    && Storage::disk('public')->exists($data['foto_path'])
                        ? Storage::disk('public')->url($data['foto_path'])
                        : null;
                unset($data['foto_path']);

                return $data;
            });

        $categorias = Categoria::orderBy('nombre')->get(['id', 'nombre']);
        $unidades = Unidad::orderBy('nombre')->get(['id', 'nombre', 'sigla']);

        return view('livewire.articulos.index', compact('articulos', 'categorias', 'unidades', 'resumenCondicion'));
    }

    private function operationalRowsQuery(bool $applyEstado = true): Builder
    {
        $user = auth()->user();
        $unidadId = $this->unidad !== '' ? (int) $this->unidad : null;

        if ($user && ! $user->isAdministradorGeneral()) {
            $unidadId = $user->unidad_id;
        }

        $series = DB::table('articulo_series as serie')
            ->join('articulos as articulo', 'articulo.id', '=', 'serie.articulo_id')
            ->join('categorias as categoria', 'categoria.id', '=', 'articulo.categoria_id')
            ->leftJoin('unidades as unidad', 'unidad.id', '=', 'serie.unidad_id')
            ->whereNull('serie.deleted_at')
            ->whereNull('articulo.deleted_at')
            ->when($unidadId, fn ($query) => $query->where('serie.unidad_id', $unidadId))
            ->when($this->categoria !== '', fn ($query) => $query->where('articulo.categoria_id', (int) $this->categoria))
            ->when($this->tipo !== '', fn ($query) => $query->whereRaw('articulo.tipo::text = ?', [$this->tipo]))
            ->when($applyEstado && $this->estado !== '', fn ($query) => $query->whereRaw('serie.estado::text = ?', [$this->estado]))
            ->when($this->search !== '', function ($query) {
                $term = '%'.mb_strtolower(trim($this->search)).'%';
                $query->where(function ($query) use ($term) {
                    $query->whereRaw('LOWER(articulo.nombre) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(COALESCE(articulo.descripcion, ?)) LIKE ?', ['', $term])
                        ->orWhereRaw('LOWER(categoria.nombre) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(serie.codigo_serie) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(serie.estado::text) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(serie.condicion_actual::text) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(COALESCE(unidad.nombre, ?)) LIKE ?', ['', $term])
                        ->orWhereRaw('LOWER(COALESCE(unidad.sigla, ?)) LIKE ?', ['', $term]);
                });
            })
            ->selectRaw("
                'reutilizable'::text AS row_type,
                'SER-' || serie.id AS id,
                articulo.id AS articulo_id,
                serie.id AS serie_id,
                serie.codigo_serie,
                articulo.nombre || ' ' || serie.codigo_serie AS operativo_nombre,
                articulo.nombre,
                articulo.foto_path,
                categoria.nombre AS categoria,
                articulo.tipo::text AS tipo,
                serie.estado::text AS estado,
                serie.condicion_actual::text AS condicion,
                COALESCE(unidad.sigla, unidad.nombre, '-') AS unidad,
                serie.unidad_id,
                serie.codigo_serie AS cantidad_serie,
                'Serie: ' || serie.codigo_serie AS detalle_principal,
                COALESCE(serie.observaciones, 'Sin observaciones registradas') AS detalle_secundario,
                NULL::timestamp AS ultimo_movimiento
            ");

        $stockActual = 'SUM(inventario.cantidad_disponible)';
        $stockMinimo = 'SUM(inventario.stock_minimo)';
        $stockEstado = "CASE
            WHEN {$stockActual} <= 0 THEN 'agotado'
            WHEN {$stockMinimo} > 0 AND {$stockActual} <= {$stockMinimo} THEN 'bajo_stock'
            ELSE 'disponible'
        END";

        $consumables = DB::table('inventario_unidad_articulos as inventario')
            ->join('articulos as articulo', 'articulo.id', '=', 'inventario.articulo_id')
            ->join('categorias as categoria', 'categoria.id', '=', 'articulo.categoria_id')
            ->join('unidades as unidad', 'unidad.id', '=', 'inventario.unidad_id')
            ->whereNull('articulo.deleted_at')
            ->whereRaw("articulo.seguimiento::text = 'cantidad'")
            ->when($unidadId, fn ($query) => $query->where('inventario.unidad_id', $unidadId))
            ->when($this->categoria !== '', fn ($query) => $query->where('articulo.categoria_id', (int) $this->categoria))
            ->when($this->tipo !== '', fn ($query) => $query->whereRaw('articulo.tipo::text = ?', [$this->tipo]))
            ->when($this->search !== '', function ($query) {
                $term = '%'.mb_strtolower(trim($this->search)).'%';
                $query->where(function ($query) use ($term) {
                    $query->whereRaw('LOWER(articulo.nombre) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(COALESCE(articulo.descripcion, ?)) LIKE ?', ['', $term])
                        ->orWhereRaw('LOWER(categoria.nombre) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(unidad.nombre) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(COALESCE(unidad.sigla, ?)) LIKE ?', ['', $term]);
                });
            })
            ->groupBy([
                'articulo.id',
                'articulo.nombre',
                'articulo.foto_path',
                'articulo.tipo',
                'categoria.nombre',
            ])
            ->when(
                $applyEstado && $this->estado !== '',
                fn ($query) => $query->havingRaw("{$stockEstado} = ?", [$this->estado])
            )
            ->selectRaw("
                'consumible'::text AS row_type,
                'ART-' || articulo.id AS id,
                articulo.id AS articulo_id,
                NULL::bigint AS serie_id,
                NULL::varchar AS codigo_serie,
                articulo.nombre AS operativo_nombre,
                articulo.nombre,
                articulo.foto_path,
                categoria.nombre AS categoria,
                articulo.tipo::text AS tipo,
                {$stockEstado} AS estado,
                CASE WHEN {$stockActual} <= 0 THEN 'sin_stock' ELSE 'operativo' END AS condicion,
                COALESCE(
                    STRING_AGG(DISTINCT COALESCE(unidad.sigla, unidad.nombre), ', '),
                    '-'
                ) AS unidad,
                ".($unidadId ? 'MIN(inventario.unidad_id)' : 'NULL::bigint')." AS unidad_id,
                TO_CHAR({$stockActual}, 'FM999999999990.00') || ' unidades' AS cantidad_serie,
                'Stock actual: ' || TO_CHAR({$stockActual}, 'FM999999999990.00') AS detalle_principal,
                'Stock minimo: ' || TO_CHAR({$stockMinimo}, 'FM999999999990.00')
                    || ' | Estado: ' || REPLACE({$stockEstado}, '_', ' ') AS detalle_secundario,
                NULL::timestamp AS ultimo_movimiento
            ");

        return $consumables->unionAll($series);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildOperationalRows(bool $applyEstado = true): array
    {
        $user = auth()->user();
        $unidadId = $this->unidad !== '' ? (int) $this->unidad : null;

        if ($user && ! $user->isAdministradorGeneral()) {
            $unidadId = $user->unidad_id;
        }

        $rows = [];

        $articulos = Articulo::query()
            ->with([
                'categoria:id,nombre',
                'inventariosUnidad.unidad:id,nombre,sigla',
                'series.unidad:id,nombre,sigla',
            ])
            ->when($this->categoria !== '', fn ($query) => $query->where('categoria_id', (int) $this->categoria))
            ->when($this->tipo !== '', fn ($query) => $query->where('tipo', $this->tipo))
            ->get();

        foreach ($articulos as $articulo) {
            if ($articulo->isCantidad()) {
                $inventarios = $articulo->inventariosUnidad
                    ->when($unidadId, fn ($items) => $items->where('unidad_id', $unidadId))
                    ->values();

                if ($unidadId && $inventarios->isEmpty()) {
                    continue;
                }

                if ($this->search !== '' && ! $this->matchesSearch($this->search, [
                    $articulo->nombre,
                    $articulo->descripcion,
                    $articulo->categoria?->nombre,
                    $inventarios->pluck('unidad.nombre')->implode(' '),
                    $inventarios->pluck('unidad.sigla')->implode(' '),
                ])) {
                    continue;
                }

                $stockActual = (float) $inventarios->sum('cantidad_disponible');
                $stockMinimo = (float) $inventarios->sum('stock_minimo');
                $estado = $stockActual <= 0 ? 'agotado' : ($stockMinimo > 0 && $stockActual <= $stockMinimo ? 'bajo_stock' : 'disponible');

                if ($applyEstado && $this->estado !== '' && $estado !== $this->estado) {
                    continue;
                }

                $unidades = $inventarios
                    ->map(fn ($inventario) => $inventario->unidad?->sigla ?? $inventario->unidad?->nombre)
                    ->filter()
                    ->unique()
                    ->values();

                $rows[] = [
                    'row_type' => 'consumible',
                    'id' => 'ART-'.$articulo->id,
                    'articulo_id' => $articulo->id,
                    'serie_id' => null,
                    'codigo_serie' => null,
                    'operativo_nombre' => $articulo->nombre,
                    'nombre' => $articulo->nombre,
                    'foto_url' => $articulo->foto_url,
                    'categoria' => $articulo->categoria?->nombre ?? '-',
                    'tipo' => $articulo->tipo,
                    'estado' => $estado,
                    'condicion' => $estado === 'agotado' ? 'sin_stock' : 'operativo',
                    'unidad' => $unidades->isNotEmpty() ? $unidades->implode(', ') : '-',
                    'unidad_id' => $unidadId,
                    'cantidad_serie' => number_format($stockActual, 2).' unidades',
                    'detalle_principal' => 'Stock actual: '.number_format($stockActual, 2),
                    'detalle_secundario' => 'Stock minimo: '.number_format($stockMinimo, 2).' | Estado: '.str_replace('_', ' ', $estado),
                    'ultimo_movimiento' => null,
                ];

                continue;
            }

            $series = $articulo->series
                ->whereNull('deleted_at')
                ->when($unidadId, fn ($items) => $items->where('unidad_id', $unidadId))
                ->values();

            foreach ($series as $serie) {
                if ($applyEstado && $this->estado !== '' && $serie->estado !== $this->estado) {
                    continue;
                }

                if ($this->search !== '' && ! $this->matchesSearch($this->search, [
                    $articulo->nombre,
                    $articulo->descripcion,
                    $articulo->categoria?->nombre,
                    $serie->codigo_serie,
                    $serie->estado,
                    $serie->condicion_actual,
                    $serie->unidad?->nombre,
                    $serie->unidad?->sigla,
                ])) {
                    continue;
                }

                $rows[] = [
                    'row_type' => 'reutilizable',
                    'id' => 'SER-'.$serie->id,
                    'articulo_id' => $articulo->id,
                    'serie_id' => $serie->id,
                    'codigo_serie' => $serie->codigo_serie,
                    'operativo_nombre' => $articulo->nombre.' '.$serie->codigo_serie,
                    'nombre' => $articulo->nombre,
                    'foto_url' => $articulo->foto_url,
                    'categoria' => $articulo->categoria?->nombre ?? '-',
                    'tipo' => $articulo->tipo,
                    'estado' => $serie->estado,
                    'condicion' => $serie->condicion_actual,
                    'unidad' => $serie->unidad?->sigla ?? $serie->unidad?->nombre ?? '-',
                    'unidad_id' => $serie->unidad_id,
                    'cantidad_serie' => $serie->codigo_serie,
                    'detalle_principal' => 'Serie: '.$serie->codigo_serie,
                    'detalle_secundario' => $serie->observaciones ?: 'Sin observaciones registradas',
                    'ultimo_movimiento' => null,
                ];
            }
        }

        return $rows;
    }

    private function matchesSearch(string $search, array $fields): bool
    {
        $search = mb_strtolower(trim($search));

        foreach ($fields as $field) {
            if ($field !== null && str_contains(mb_strtolower((string) $field), $search)) {
                return true;
            }
        }

        return false;
    }

    private function sortRows(\Illuminate\Support\Collection $rows): \Illuminate\Support\Collection
    {
        $field = $this->sortField;
        $descending = $this->sortDirection === 'desc';

        $sorted = $rows->sortBy(function (array $row) use ($field) {
            return mb_strtolower((string) ($row[$field] ?? ''));
        });

        return $descending ? $sorted->reverse()->values() : $sorted->values();
    }
}
