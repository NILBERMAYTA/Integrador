<?php

namespace App\Livewire\Articulos;

use App\Models\Articulo;
use App\Models\Categoria;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class Index extends Component
{
    use WithPagination;

    // ---------------------------
    // Filtros (persisten en URL)
    // ---------------------------
    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $categoria = '';   // id de categoria

    #[Url(except: '')]
    public string $tipo = '';        // reutilizable|consumible

    #[Url(except: '')]
    public string $seguimiento = ''; // serie|cantidad

    // ---------------------------
    // Orden
    // ---------------------------
    #[Url(except: 'nombre')]
    public string $sortField = 'nombre';

    #[Url(except: 'asc')]
    public string $sortDirection = 'asc';

    // Resetear página al cambiar filtros/búsqueda
    public function updatedSearch()     { $this->resetPage(); }
    public function updatedCategoria()  { $this->resetPage(); }
    public function updatedTipo()       { $this->resetPage(); }
    public function updatedSeguimiento(){ $this->resetPage(); }

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

    /**
     * Eliminar artículo (soft delete)
     */
    public function confirmarEliminacion(int $id): void
    {
        try {
            $art = Articulo::findOrFail($id);
            $art->delete(); // soft
            session()->flash('success', 'Artículo eliminado exitosamente.');
        } catch (\Throwable $e) {
            session()->flash('error', 'Error al eliminar el artículo: '.$e->getMessage());
        }
    }

    /**
     * Exportar PDF respetando filtros/orden actuales
     */
    public function exportPdf()
    {
        $dir = $this->sortDirection === 'desc' ? 'DESC' : 'ASC';

        $articulos = Articulo::query()
            ->with('categoria:id,nombre')
            ->when($this->search !== '', function ($q) {
                $term = "%{$this->search}%";
                $q->where(function ($qq) use ($term) {
                    $qq->where('nombre', 'ILIKE', $term)
                       ->orWhere('unidad_medida', 'ILIKE', $term)
                       ->orWhereHas('categoria', fn($qc)=>$qc->where('nombre', 'ILIKE', $term));
                });
            })
            ->when($this->categoria !== '', fn($q) => $q->where('categoria_id', (int)$this->categoria))
            ->when($this->tipo !== '',       fn($q) => $q->where('tipo', $this->tipo))
            ->when($this->seguimiento !== '',fn($q) => $q->where('seguimiento', $this->seguimiento))
            ->when(true, function ($q) use ($dir) {
                switch ($this->sortField) {
                    case 'categoria':
                        $q->join('categorias','categorias.id','=','articulos.categoria_id')
                          ->orderByRaw("LOWER(categorias.nombre) $dir NULLS LAST")
                          ->select('articulos.*'); // evitar columnas ambiguas
                        break;
                    case 'tipo':
                    case 'seguimiento':
                    case 'unidad_medida':
                    case 'nombre':
                        $q->orderByRaw("LOWER({$this->sortField}) $dir NULLS LAST");
                        break;
                    default:
                        $q->orderByRaw("LOWER(nombre) $dir NULLS LAST");
                }
            })
            ->get();

        $pdf = PDF::loadView('reports.articulos', compact('articulos'))
                  ->setPaper('a4', 'portrait');

        return response()->streamDownload(
            fn() => print($pdf->output()),
            'articulos_'.now()->format('Ymd_His').'.pdf'
        );
    }

    

    public function render()
    {
        $dir = $this->sortDirection === 'desc' ? 'DESC' : 'ASC';

        $query = Articulo::query()
            ->with('categoria:id,nombre')

            // Búsqueda por nombre, unidad o categoría
            ->when($this->search !== '', function ($q) {
                $term = "%{$this->search}%";
                $q->where(function ($qq) use ($term) {
                    $qq->where('nombre', 'ILIKE', $term)
                       ->orWhere('unidad_medida', 'ILIKE', $term)
                       ->orWhereHas('categoria', fn($qc)=>$qc->where('nombre', 'ILIKE', $term));
                });
            })

            // Filtros
            ->when($this->categoria !== '', fn($q) => $q->where('categoria_id', (int)$this->categoria))
            ->when($this->tipo !== '',       fn($q) => $q->where('tipo', $this->tipo))
            ->when($this->seguimiento !== '',fn($q) => $q->where('seguimiento', $this->seguimiento))

            // Orden
            ->when(true, function ($q) use ($dir) {
                switch ($this->sortField) {
                    case 'categoria':
                        $q->join('categorias','categorias.id','=','articulos.categoria_id')
                          ->orderByRaw("LOWER(categorias.nombre) $dir NULLS LAST")
                          ->select('articulos.*');
                        break;
                    case 'tipo':
                    case 'seguimiento':
                    case 'unidad_medida':
                    case 'nombre':
                        $q->orderByRaw("LOWER({$this->sortField}) $dir NULLS LAST");
                        break;
                    default:
                        $q->orderByRaw("LOWER(nombre) $dir NULLS LAST");
                        break;
                }
            });

        $articulos = $query->paginate(10);

        // Catálogo de categorías para el filtro
        $categorias = Categoria::orderBy('nombre')->get(['id','nombre']);

        return view('livewire.articulos.index', compact('articulos','categorias'));
    }
}
