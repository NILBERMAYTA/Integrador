<?php

namespace App\Livewire\Mantenimientos;

use App\Models\Mantenimiento;
use App\Models\Articulo;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Carbon\Carbon;

class Index extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $tipo = '';

    #[Url(except: 'fecha_inicio')]
    public string $sortField = 'fecha_inicio';

    #[Url(except: 'desc')]
    public string $sortDirection = 'desc';

    public function updatedSearch() { $this->resetPage(); }
    public function updatedTipo()   { $this->resetPage(); }

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
            $m = Mantenimiento::findOrFail($id);
            $m->delete();
            session()->flash('success', 'Mantenimiento eliminado exitosamente.');
        } catch (\Throwable $e) {
            session()->flash('error', 'No se pudo eliminar el mantenimiento.');
        }
    }

    public function cerrarMantenimiento(int $id, ?string $fechaFin = null, ?string $descripcion = null, $costo = null): void
    {
        try {
            $m = Mantenimiento::findOrFail($id);
            $m->fecha_fin = $fechaFin ? Carbon::parse($fechaFin) : now();
            if (!$m->fecha_inicio) {
                $m->fecha_inicio = now();
            }
            $m->descripcion = $descripcion;
            $m->costo = $costo === '' ? null : $costo;
            $m->save();

            session()->flash('success', 'Mantenimiento cerrado correctamente.');
        } catch (\Throwable $e) {
            session()->flash('error', 'No se pudo cerrar el mantenimiento.');
        }
    }

    public function render()
    {
        $dir = $this->sortDirection === 'desc' ? 'DESC' : 'ASC';

        $mantenimientos = Mantenimiento::query()
            ->with(['articulo', 'serie', 'creador'])
            ->when($this->search !== '', function ($q) {
                $term = "%{$this->search}%";
                $q->whereHas('articulo', fn($qa) => $qa->where('nombre', 'ILIKE', $term))
                  ->orWhereHas('serie', fn($qs) => $qs->where('codigo_serie', 'ILIKE', $term));
            })
            ->when($this->tipo !== '', fn($q) => $q->where('tipo', $this->tipo))
            ->when(true, function ($q) use ($dir) {
                switch ($this->sortField) {
                    case 'tipo':
                        $q->orderByRaw("LOWER(tipo) $dir NULLS LAST");
                        break;
                    case 'fecha_fin':
                        $q->orderByRaw("fecha_fin $dir NULLS LAST");
                        break;
                    case 'articulo':
                        $q->join('articulos', 'mantenimientos.articulo_id', '=', 'articulos.id')
                          ->orderByRaw("LOWER(articulos.nombre) $dir NULLS LAST")
                          ->select('mantenimientos.*');
                        break;
                    default:
                        $q->orderByRaw("fecha_inicio $dir NULLS LAST");
                        break;
                }
            })
            ->paginate(10);

        $tipos = ['preventivo', 'correctivo'];

        $total = Mantenimiento::count();
        $cerrados = Mantenimiento::whereNotNull('fecha_fin')->count();
        $stats = [
            'total' => $total,
            'abiertos' => Mantenimiento::whereNull('fecha_fin')->count(),
            'cerrados' => $cerrados,
            'preventivos' => Mantenimiento::where('tipo', 'preventivo')->count(),
            'correctivos' => Mantenimiento::where('tipo', 'correctivo')->count(),
            'pct_cerrados' => $total > 0 ? (int) round($cerrados / $total * 100) : 0,
        ];

        return view('livewire.mantenimientos.index', compact('mantenimientos', 'tipos', 'stats'));
    }
}
