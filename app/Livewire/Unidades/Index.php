<?php

namespace App\Livewire\Unidades;

use App\Models\Unidad;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function confirmarEliminacion(int $id): void
    {
        try {
            $unidad = Unidad::findOrFail($id);
            $unidad->delete();
            $this->resetPage();

            session()->flash('success', 'Unidad eliminada correctamente.');
        } catch (\Throwable $e) {
            session()->flash('error', 'No se pudo eliminar la unidad: '.$e->getMessage());
        }
    }

    public function render()
    {
        $unidades = Unidad::query()
            ->withCount([
                'usuarios as personal_activo_count' => fn ($q) => $q->whereNull('deleted_at'),
                'series as series_activas_count' => fn ($q) => $q->whereNull('deleted_at'),
            ])
            ->when($this->search !== '', function ($q) {
                $term = "%{$this->search}%";
                $q->where(function ($q) use ($term) {
                    $q->where('nombre', 'ILIKE', $term)
                        ->orWhere('sigla', 'ILIKE', $term)
                        ->orWhere('descripcion', 'ILIKE', $term);
                });
            })
            ->latest('id')
            ->paginate(10);

        $totales = Unidad::query()
            ->withCount([
                'usuarios as personal_activo_count' => fn ($q) => $q->whereNull('deleted_at'),
                'series as series_activas_count' => fn ($q) => $q->whereNull('deleted_at'),
            ])
            ->get();

        $stats = [
            'unidades' => $totales->count(),
            'personal' => (int) $totales->sum('personal_activo_count'),
            'series' => (int) $totales->sum('series_activas_count'),
            'sin_personal' => $totales->where('personal_activo_count', 0)->count(),
        ];

        return view('livewire.unidades.index', compact('unidades', 'stats'));
    }
}
