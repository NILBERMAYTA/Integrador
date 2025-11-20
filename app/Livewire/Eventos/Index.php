<?php

namespace App\Livewire\Eventos;

use App\Models\Evento;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public function confirmarEliminacion(int $id): void
    {
        try {
            $evento = Evento::findOrFail($id);
            $evento->delete();

            $this->resetPage();

            session()->flash('success', 'El evento se movió a la papelera.');
        } catch (\Throwable $e) {
            session()->flash('error', 'No se pudo eliminar: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $eventos = Evento::query()
            ->latest('id')
            ->paginate(10);

        return view('livewire.eventos.index', compact('eventos'));
    }
}
