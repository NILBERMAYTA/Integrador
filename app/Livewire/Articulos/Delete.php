<?php

namespace App\Livewire\Articulos;

use App\Models\Articulo;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;

class Delete extends Component
{
    use WithPagination;

    public $search = '';
    public $categoria = '';
    public $tipo = '';
    public $sortField = 'deleted_at';
    public $sortDirection = 'desc';

    protected $queryString = [
        'search' => ['except' => ''],
        'categoria' => ['except' => ''],
        'tipo' => ['except' => ''],
        'sortField' => ['except' => 'deleted_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    /**
     * Resetear paginación cuando cambia la búsqueda
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCategoria()
    {
        $this->resetPage();
    }

    public function updatingTipo()
    {
        $this->resetPage();
    }

    /**
     * Ordenar por campo
     */
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    /**
     * Restaurar artículo
     */
    public function restaurar($articuloId)
    {
        try {
            $articulo = Articulo::onlyTrashed()->findOrFail($articuloId);
            $articulo->restore();

            session()->flash('success', 'Artículo restaurado exitosamente.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al restaurar el artículo: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar permanentemente
     */
    public function eliminarPermanentemente($articuloId)
    {
        try {
            $articulo = Articulo::onlyTrashed()->findOrFail($articuloId);
            if (! empty($articulo->foto_path)) {
                Storage::disk('public')->delete($articulo->foto_path);
            }

            $articulo->forceDelete();

            session()->flash('success', 'Artículo eliminado permanentemente.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar permanentemente: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $query = Articulo::onlyTrashed();

        // Búsqueda
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('nombre', 'ilike', '%' . $this->search . '%')
                  ->orWhere('descripcion', 'ilike', '%' . $this->search . '%');
            });
        }

        // Filtro por categoría
        if ($this->categoria) {
            $query->where('categoria_id', $this->categoria);
        }

        // Filtro por tipo
        if ($this->tipo) {
            $query->where('tipo', $this->tipo);
        }

        // Ordenamiento
        $query->orderBy($this->sortField, $this->sortDirection);

        $articulos = $query->paginate(10);

        // Obtener categorías únicas para los filtros (incluir eliminados)
        $categorias = Articulo::onlyTrashed()
            ->with('categoria')
            ->get()
            ->pluck('categoria')
            ->unique('id')
            ->sortBy('nombre')
            ->values();

        // Tipos de artículos disponibles
        $tipos = ['reutilizable', 'consumible'];

        return view('livewire.articulos.delete', [
            'articulos' => $articulos,
            'categorias' => $categorias,
            'tipos' => $tipos,
        ]);
    }
}
