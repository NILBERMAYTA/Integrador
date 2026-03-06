<?php

namespace App\Livewire\ActivityLogs;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Spatie\Activitylog\Models\Activity;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class Index extends Component
{
    use WithPagination;

    // Filtros
    #[Url(except: '')]
    public string $date_from = '';
    
    #[Url(except: '')]
    public string $date_to = '';

    #[Url(except: 'activity')]
    public string $tab = 'activity';

    // Orden
    #[Url(except: 'created_at')]
    public string $sortField = 'created_at';
    
    #[Url(except: 'desc')]
    public string $sortDirection = 'desc';

    // Resetear pÃ¡gina al cambiar filtros
    public function updatedDateFrom() { $this->resetPage(); }
    public function updatedDateTo() { $this->resetPage(); }
    public function updatedTab() { $this->resetPage(); }

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
        $this->date_from = '';
        $this->date_to = '';
        $this->resetPage();
    }

    public function exportPdf()
    {
        $dir = $this->sortDirection === 'desc' ? 'DESC' : 'ASC';

        $activities = Activity::query()
            ->with(['causer', 'subject'])
            ->when($this->tab === 'logins', fn($q) => $q->where('log_name', 'auth'))
            ->when($this->tab !== 'logins', fn($q) => $q->where('log_name', '!=', 'auth'))
            ->when($this->date_from !== '', fn($q) => $q->whereDate('created_at', '>=', $this->date_from))
            ->when($this->date_to !== '', fn($q) => $q->whereDate('created_at', '<=', $this->date_to))
            ->orderBy($this->sortField, $dir)
            ->get();

        $title = $this->tab === 'logins'
            ? 'Reporte de Logs de Login'
            : 'Reporte de Actividad General';

        $pdf = PDF::loadView('reports.activity-logs', [
                'activities' => $activities,
                'title' => $title,
                'date_from' => $this->date_from,
                'date_to' => $this->date_to,
                'tab' => $this->tab,
            ])
            ->setPaper('a4', 'landscape');

        $suffix = $this->tab === 'logins' ? 'logins' : 'actividad';

        return response()->streamDownload(
            fn() => print($pdf->output()),
            $suffix.'_'.now()->format('Ymd_His').'.pdf'
        );
    }

    public function render()
    {
        $dir = $this->sortDirection === 'desc' ? 'DESC' : 'ASC';

        $baseQuery = Activity::query()
            ->with(['causer', 'subject'])
            ->when($this->tab === 'logins', fn($q) => $q->where('log_name', 'auth'))
            ->when($this->tab !== 'logins', fn($q) => $q->where('log_name', '!=', 'auth'))
            // Filtro por rango de fechas
            ->when($this->date_from !== '', fn($q) => $q->whereDate('created_at', '>=', $this->date_from))
            ->when($this->date_to !== '', fn($q) => $q->whereDate('created_at', '<=', $this->date_to));

        $activities = (clone $baseQuery)
            ->orderBy($this->sortField, $dir)
            ->paginate(20);

        return view('livewire.activity-logs.index', compact('activities'));
    }
}
