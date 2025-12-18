<?php

namespace App\Livewire\ActivityLogs;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Spatie\Activitylog\Models\Activity;
use App\Models\User;

class Index extends Component
{
    use WithPagination;

    // Filtros
    #[Url(except: '')]
    public string $search = '';
    
    #[Url(except: '')]
    public string $causer_id = '';
    
    #[Url(except: '')]
    public string $subject_type = '';
    
    #[Url(except: '')]
    public string $event = '';
    
    #[Url(except: '')]
    public string $date_from = '';
    
    #[Url(except: '')]
    public string $date_to = '';

    // Orden
    #[Url(except: 'created_at')]
    public string $sortField = 'created_at';
    
    #[Url(except: 'desc')]
    public string $sortDirection = 'desc';

    // Resetear página al cambiar filtros
    public function updatedSearch() { $this->resetPage(); }
    public function updatedCauserId() { $this->resetPage(); }
    public function updatedSubjectType() { $this->resetPage(); }
    public function updatedEvent() { $this->resetPage(); }
    public function updatedDateFrom() { $this->resetPage(); }
    public function updatedDateTo() { $this->resetPage(); }

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

    public function clearFilters(): void
    {
        $this->search = '';
        $this->causer_id = '';
        $this->subject_type = '';
        $this->event = '';
        $this->date_from = '';
        $this->date_to = '';
        $this->resetPage();
    }

    public function render()
    {
        $dir = $this->sortDirection === 'desc' ? 'DESC' : 'ASC';

        $activities = Activity::query()
            ->with(['causer', 'subject'])
            // Búsqueda general en descripción
            ->when($this->search !== '', function ($q) {
                $term = "%{$this->search}%";
                $q->where('description', 'ILIKE', $term);
            })
            // Filtro por usuario que causó la acción
            ->when($this->causer_id !== '', fn($q) => $q->where('causer_id', $this->causer_id))
            // Filtro por tipo de modelo
            ->when($this->subject_type !== '', fn($q) => $q->where('subject_type', $this->subject_type))
            // Filtro por evento (created, updated, deleted)
            ->when($this->event !== '', fn($q) => $q->where('event', $this->event))
            // Filtro por rango de fechas
            ->when($this->date_from !== '', fn($q) => $q->whereDate('created_at', '>=', $this->date_from))
            ->when($this->date_to !== '', fn($q) => $q->whereDate('created_at', '<=', $this->date_to))
            // Ordenamiento
            ->orderBy($this->sortField, $dir)
            ->paginate(20);

        // Obtener listas para filtros
        $users = User::select('id', 'name', 'apellido_paterno', 'apellido_materno')
            ->orderBy('apellido_paterno')
            ->get()
            ->map(fn($u) => [
                'id' => $u->id,
                'nombre_completo' => $u->nombre_completo
            ]);

        $subjectTypes = Activity::select('subject_type')
            ->distinct()
            ->whereNotNull('subject_type')
            ->pluck('subject_type')
            ->map(fn($type) => class_basename($type))
            ->sort()
            ->values();

        $events = Activity::select('event')
            ->distinct()
            ->whereNotNull('event')
            ->pluck('event')
            ->sort()
            ->values();

        return view('livewire.activity-logs.index', compact('activities', 'users', 'subjectTypes', 'events'));
    }
}
