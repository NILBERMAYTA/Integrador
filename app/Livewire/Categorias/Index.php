<?php

namespace App\Livewire\Categorias;

use App\Models\Categoria;
use App\Models\Articulo;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;


    public function confirmarEliminacion(int $id): void
{
    try {
        $categoria = Categoria::findOrFail($id);
        $categoria->delete(); 


        $this->resetPage();


        session()->flash('success', 'La categoría se movió a la papelera.');
    } catch (\Throwable $e) {
        session()->flash('error', 'No se pudo eliminar: ' . $e->getMessage());
    }
}


    public function render()
    {
        // Por defecto Eloquent excluye los soft-deleted si el modelo usa SoftDeletes
        $categorias = Categoria::query()
            ->withCount('articulos')
            ->latest('id')
            ->paginate(10);


        $totalCategorias = Categoria::query()->count();
        $conArticulos = Categoria::has('articulos')->count();
        $stats = [
            'total' => $totalCategorias,
            'con_articulos' => $conArticulos,
            'sin_articulos' => max(0, $totalCategorias - $conArticulos),
            'total_articulos' => Articulo::query()->count(),
        ];

        return view('livewire.categorias.index', compact('categorias', 'stats'));
    }
}
