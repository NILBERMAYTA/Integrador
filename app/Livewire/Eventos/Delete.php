<?php

namespace App\Livewire\Eventos;

use App\Models\Evento;
use Livewire\Component;
use Livewire\WithPagination;

class Delete extends Component
{
    use WithPagination;

    public function restaurar(int $id): void
    {
        try {
            $evento = Evento::onlyTrashed()->findOrFail($id);
            $evento->restore();

            session()->flash('success', 'Evento restaurado correctamente.');
        } catch (\Throwable $e) {
            session()->flash('error', 'Error al restaurar: ' . $e->getMessage());
        }
    }

    public function eliminarPermanentemente(int $id): void
    {
        try {
            $evento = Evento::onlyTrashed()->findOrFail($id);
            $evento->forceDelete();

            session()->flash('success', 'Evento eliminado permanentemente.');
        } catch (\Throwable $e) {
            session()->flash('error', 'Error al eliminar: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $eventosEliminados = Evento::onlyTrashed()
            ->orderBy('deleted_at', 'desc')
            ->paginate(10);

        return view('livewire.eventos.delete', [
            'eventosEliminados' => $eventosEliminados,
        ]);
    }
}
