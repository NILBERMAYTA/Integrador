<?php

namespace App\Livewire\Categorias;

use App\Models\Categoria;
use Livewire\Component;
use Livewire\WithPagination;

class Delete extends Component
{
    use WithPagination;

    public function restaurar(int $id): void
    {
        try {
            $categoria = Categoria::onlyTrashed()->findOrFail($id);
            $categoria->restore();

            session()->flash('success', 'Categoría restaurada correctamente.');
        } catch (\Throwable $e) {
            session()->flash('error', 'Error al restaurar: ' . $e->getMessage());
        }
    }

    public function eliminarPermanentemente(int $id): void
    {
        try {
            $categoria = Categoria::onlyTrashed()->findOrFail($id);
            $categoria->forceDelete();

            session()->flash('success', 'Categoría eliminada permanentemente.');
        } catch (\Throwable $e) {
            session()->flash('error', 'Error al eliminar: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $categoriasEliminadas = Categoria::onlyTrashed()
            ->orderBy('deleted_at', 'desc')
            ->paginate(10);

        return view('livewire.categorias.delete', [
            'categoriasEliminadas' => $categoriasEliminadas,
        ]);
    }
}
