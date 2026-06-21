<?php

namespace App\Livewire\Eventos;

use App\Models\Evento;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url(except: 'table')]
    public string $viewMode = 'table';

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
            ->withCount('operaciones')
            ->latest('id')
            ->paginate(10);

        $now = now();
        $stats = [
            'total' => Evento::count(),
            'planificados' => Evento::where('estado', 'planificado')->count(),
            'activos' => Evento::where('estado', 'activo')->count(),
            'cerrados' => Evento::where('estado', 'cerrado')->count(),
        ];

        $severidad = [
            'bajo' => Evento::where('nivel', 'bajo')->count(),
            'medio' => Evento::where('nivel', 'medio')->count(),
            'alto' => Evento::where('nivel', 'alto')->count(),
        ];

        $eventosChartData = [
            'severidad' => [
                'labels' => ['Bajo', 'Medio', 'Alto'],
                'series' => [$severidad['bajo'], $severidad['medio'], $severidad['alto']],
            ],
        ];

        return view('livewire.eventos.index', compact('eventos', 'stats', 'severidad', 'eventosChartData'));
    }
}
