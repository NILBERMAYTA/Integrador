<?php

namespace App\Livewire\Articulos;

use App\Models\Articulo;
use App\Models\ArticuloSerie;
use App\Models\Categoria;
use App\Models\InventarioUnidadArticulo;
use App\Models\Unidad;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
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
        $baseRows = collect($this->buildOperationalRows(false));
        $resumenCondicion = [
            'bueno' => $baseRows->where('row_type', 'reutilizable')->where('condicion', 'bueno')->count(),
            'con_defectos' => $baseRows->where('row_type', 'reutilizable')->where('condicion', 'con_defectos')->count(),
            'malo' => $baseRows->where('row_type', 'reutilizable')->where('condicion', 'malo')->count(),
            'inoperativo' => $baseRows->where('row_type', 'reutilizable')->where('condicion', 'inoperativo')->count(),
            'total' => $baseRows->count(),
        ];

        $rows = $this->estado !== ''
            ? $baseRows->where('estado', $this->estado)->values()
            : $baseRows->values();

        $sorted = $this->sortRows($rows);

        $pageName = 'page';
        $page = Paginator::resolveCurrentPage($pageName);
        $items = $sorted->forPage($page, $this->perPage)->values();

        $articulos = new LengthAwarePaginator(
            $items,
            $sorted->count(),
            $this->perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
                'pageName' => $pageName,
            ]
        );

        $categorias = Categoria::orderBy('nombre')->get(['id', 'nombre']);
        $unidades = Unidad::orderBy('nombre')->get(['id', 'nombre', 'sigla']);

        return view('livewire.articulos.index', compact('articulos', 'categorias', 'unidades', 'resumenCondicion'));
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
