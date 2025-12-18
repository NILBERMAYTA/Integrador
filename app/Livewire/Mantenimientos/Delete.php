<?php

namespace App\Livewire\Mantenimientos;

use App\Models\Mantenimiento;
use Livewire\Component;
use Livewire\WithPagination;

class Delete extends Component
{
    use WithPagination;

    public function restaurar(int $id): void
    {
        if ($m = Mantenimiento::onlyTrashed()->find($id)) {
            $m->restore();
            session()->flash('success', 'Mantenimiento restaurado.');
        }
    }

    public function eliminarPermanentemente(int $id): void
    {
        if ($m = Mantenimiento::onlyTrashed()->find($id)) {
            $m->forceDelete();
            session()->flash('success', 'Mantenimiento eliminado definitivamente.');
        }
    }

    public function render()
    {
        $mantenimientos = Mantenimiento::onlyTrashed()
            ->with(['articulo','serie','creador'])
            ->orderByDesc('deleted_at')
            ->paginate(10);

        return view('livewire.mantenimientos.delete', compact('mantenimientos'));
    }
}
