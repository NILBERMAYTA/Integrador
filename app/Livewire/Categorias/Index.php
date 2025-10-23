<?php

namespace App\Livewire\Categorias;

use App\Models\Categoria;
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
            ->latest('id')
            ->paginate(10);

        return view('livewire.categorias.index', compact('categorias'));
    }
}
