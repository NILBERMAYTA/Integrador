<?php

namespace App\Livewire\Articulos;

use App\Models\Articulo;
use App\Models\Categoria;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
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

    #[Url(except: 'nombre')]
    public string $sortField = 'nombre';

    #[Url(except: 'asc')]
    public string $sortDirection = 'asc';

    public function updatedSearch() { $this->resetPage(); }
    public function updatedCategoria() { $this->resetPage(); }
    public function updatedTipo() { $this->resetPage(); }

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

    public function exportPdf()
    {
        $dir = $this->sortDirection === 'desc' ? 'DESC' : 'ASC';

        $articulos = Articulo::query()
            ->with('categoria:id,nombre')
            ->when($this->search !== '', function ($q) {
                $term = "%{$this->search}%";
                $q->where(function ($qq) use ($term) {
                    $qq->where('nombre', 'ILIKE', $term)
                        ->orWhereHas('categoria', fn ($qc) => $qc->where('nombre', 'ILIKE', $term));
                });
            })
            ->when($this->categoria !== '', fn ($q) => $q->where('categoria_id', (int) $this->categoria))
            ->when($this->tipo !== '', fn ($q) => $q->where('tipo', $this->tipo))
            ->when(true, function ($q) use ($dir) {
                switch ($this->sortField) {
                    case 'categoria':
                        $q->join('categorias', 'categorias.id', '=', 'articulos.categoria_id')
                            ->orderByRaw("LOWER(categorias.nombre) $dir NULLS LAST")
                            ->select('articulos.*');
                        break;
                    case 'tipo':
                    case 'nombre':
                        $q->orderByRaw("LOWER({$this->sortField}) $dir NULLS LAST");
                        break;
                    default:
                        $q->orderByRaw("LOWER(nombre) $dir NULLS LAST");
                }
            })
            ->get();

        $pdf = PDF::loadView('reports.articulos', compact('articulos'))->setPaper('a4', 'portrait');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'articulos_'.now()->format('Ymd_His').'.pdf'
        );
    }

    public function render()
    {
        $dir = $this->sortDirection === 'desc' ? 'DESC' : 'ASC';

        $query = Articulo::query()
            ->with('categoria:id,nombre')
            ->when($this->search !== '', function ($q) {
                $term = "%{$this->search}%";
                $q->where(function ($qq) use ($term) {
                    $qq->where('nombre', 'ILIKE', $term)
                        ->orWhereHas('categoria', fn ($qc) => $qc->where('nombre', 'ILIKE', $term));
                });
            })
            ->when($this->categoria !== '', fn ($q) => $q->where('categoria_id', (int) $this->categoria))
            ->when($this->tipo !== '', fn ($q) => $q->where('tipo', $this->tipo))
            ->when(true, function ($q) use ($dir) {
                switch ($this->sortField) {
                    case 'categoria':
                        $q->join('categorias', 'categorias.id', '=', 'articulos.categoria_id')
                            ->orderByRaw("LOWER(categorias.nombre) $dir NULLS LAST")
                            ->select('articulos.*');
                        break;
                    case 'tipo':
                    case 'nombre':
                        $q->orderByRaw("LOWER({$this->sortField}) $dir NULLS LAST");
                        break;
                    default:
                        $q->orderByRaw("LOWER(nombre) $dir NULLS LAST");
                        break;
                }
            });

        $articulos = $query->paginate(10);
        $categorias = Categoria::orderBy('nombre')->get(['id', 'nombre']);

        return view('livewire.articulos.index', compact('articulos', 'categorias'));
    }

    public function abrirAjuste(int $id): void
    {
        $this->ajusteArticulo = Articulo::find($id);
    }

    public function closeAjuste(): void
    {
        $this->ajusteArticulo = null;
    }
}
