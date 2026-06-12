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
            'bueno' => $baseRows->where('row_type', 'serie')->where('condicion', 'bueno')->count(),
            'con_defectos' => $baseRows->where('row_type', 'serie')->where('condicion', 'con_defectos')->count(),
            'malo' => $baseRows->where('row_type', 'serie')->where('condicion', 'malo')->count(),
            'inoperativo' => $baseRows->where('row_type', 'serie')->where('condicion', 'inoperativo')->count(),
            'total' => $baseRows->where('row_type', 'serie')->count(),
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

        $inventarios = InventarioUnidadArticulo::query()
            ->with([
                'articulo.categoria:id,nombre',
                'unidad:id,nombre,sigla',
            ])
            ->whereHas('articulo', fn ($query) => $query->whereNull('deleted_at'))
            ->when($unidadId, fn ($query) => $query->where('unidad_id', $unidadId))
            ->when($this->categoria !== '', fn ($query) => $query->whereHas('articulo', fn ($q) => $q->where('categoria_id', (int) $this->categoria)))
            ->when($this->tipo !== '', fn ($query) => $query->whereHas('articulo', fn ($q) => $q->where('tipo', $this->tipo)))
            ->get();

        foreach ($inventarios as $inventario) {
            $articulo = $inventario->articulo;
            if (! $articulo) {
                continue;
            }

            if ($this->search !== '' && ! $this->matchesSearch(
                $this->search,
                [$articulo->nombre, $articulo->descripcion, $articulo->categoria?->nombre, $inventario->unidad?->nombre, $inventario->unidad?->sigla]
            )) {
                continue;
            }

            $cantidadDisponible = (float) $inventario->cantidad_disponible;
            $estado = $cantidadDisponible <= 0 ? 'agotado' : ($cantidadDisponible <= 5 ? 'bajo_stock' : 'disponible');

            if ($applyEstado && $this->estado !== '' && $estado !== $this->estado) {
                continue;
            }

            $rows[] = [
                'row_type' => 'consumible',
                'id' => 'INV-'.$inventario->id,
                'articulo_id' => $articulo->id,
                'operativo_nombre' => $articulo->nombre,
                'nombre' => $articulo->nombre,
                'categoria' => $articulo->categoria?->nombre ?? '-',
                'tipo' => $articulo->tipo,
                'estado' => $estado,
                'condicion' => $estado === 'agotado' ? 'sin_stock' : 'operativo',
                'unidad' => $inventario->unidad?->sigla ?? $inventario->unidad?->nombre ?? '-',
                'unidad_id' => $inventario->unidad_id,
                'cantidad_serie' => number_format($cantidadDisponible, 2),
                'detalle_principal' => 'Disponible: '.number_format($cantidadDisponible, 2),
                'detalle_secundario' => 'Asignado: '.number_format((float) $inventario->cantidad_asignada, 2).' | Mant.: '.number_format((float) $inventario->cantidad_mantenimiento, 2),
                'ultimo_movimiento' => null,
            ];
        }

        $series = ArticuloSerie::query()
            ->with([
                'articulo.categoria:id,nombre',
                'unidad:id,nombre,sigla',
            ])
            ->whereHas('articulo', fn ($query) => $query->whereNull('deleted_at'))
            ->whereNull('deleted_at')
            ->when($unidadId, fn ($query) => $query->where('unidad_id', $unidadId))
            ->when($this->categoria !== '', fn ($query) => $query->whereHas('articulo', fn ($q) => $q->where('categoria_id', (int) $this->categoria)))
            ->when($this->tipo !== '', fn ($query) => $query->whereHas('articulo', fn ($q) => $q->where('tipo', $this->tipo)))
            ->get();

        foreach ($series as $serie) {
            $articulo = $serie->articulo;
            if (! $articulo) {
                continue;
            }

            if ($this->search !== '' && ! $this->matchesSearch(
                $this->search,
                [$articulo->nombre, $articulo->descripcion, $articulo->categoria?->nombre, $serie->codigo_serie, $serie->unidad?->nombre, $serie->unidad?->sigla]
            )) {
                continue;
            }

            if ($applyEstado && $this->estado !== '' && $serie->estado !== $this->estado) {
                continue;
            }

            $rows[] = [
                'row_type' => 'serie',
                'id' => 'SER-'.$serie->id,
                'articulo_id' => $articulo->id,
                'serie_id' => $serie->id,
                'operativo_nombre' => $articulo->nombre.' '.$serie->codigo_serie,
                'nombre' => $articulo->nombre,
                'categoria' => $articulo->categoria?->nombre ?? '-',
                'tipo' => $articulo->tipo,
                'estado' => $serie->estado,
                'condicion' => $serie->condicion_actual ?? 'bueno',
                'unidad' => $serie->unidad?->sigla ?? $serie->unidad?->nombre ?? '-',
                'unidad_id' => $serie->unidad_id,
                'cantidad_serie' => $serie->codigo_serie,
                'detalle_principal' => 'Serie: '.$serie->codigo_serie,
                'detalle_secundario' => $serie->observaciones ?: null,
                'ultimo_movimiento' => null,
            ];
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
