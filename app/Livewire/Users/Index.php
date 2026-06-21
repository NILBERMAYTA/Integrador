<?php

namespace App\Livewire\Users;

use App\Models\Unidad;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $rango = '';

    #[Url(except: '')]
    public string $rol = '';

    #[Url(except: '')]
    public string $unidad = '';

    #[Url(except: 'apellidos')]
    public string $sortField = 'apellidos';

    #[Url(except: 'asc')]
    public string $sortDirection = 'asc';

    #[Url(except: 'table')]
    public string $viewMode = 'table';

    public function updatedSearch() { $this->resetPage(); }
    public function updatedRango() { $this->resetPage(); }
    public function updatedRol() { $this->resetPage(); }
    public function updatedUnidad() { $this->resetPage(); }

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

    public function confirmarEliminacion(int $userId): void
    {
        try {
            $user = User::findOrFail($userId);
            $user->delete();

            session()->flash('success', 'Usuario eliminado exitosamente.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar el usuario: '.$e->getMessage());
        }
    }

    public function exportPdf()
    {
        $dir = $this->sortDirection === 'desc' ? 'DESC' : 'ASC';

        $users = $this->baseQuery()
            ->when(true, function ($q) use ($dir) {
                switch ($this->sortField) {
                    case 'apellidos':
                        $q->orderByRaw("LOWER(apellido_paterno) $dir NULLS LAST")
                            ->orderByRaw("LOWER(apellido_materno) $dir NULLS LAST");
                        break;
                    case 'name':
                    case 'numero_escalafon':
                    case 'rango':
                    case 'role':
                        $q->orderByRaw("LOWER({$this->sortField}) $dir NULLS LAST");
                        break;
                    default:
                        $q->orderByRaw("LOWER(apellido_paterno) $dir NULLS LAST")
                            ->orderByRaw("LOWER(apellido_materno) $dir NULLS LAST");
                }
            })
            ->get();

        $pdf = PDF::loadView('reports.users', compact('users'))->setPaper('a4', 'portrait');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'usuarios_'.now()->format('Ymd_His').'.pdf'
        );
    }

    public function render()
    {
        $dir = $this->sortDirection === 'desc' ? 'DESC' : 'ASC';

        $users = $this->baseQuery()
            ->when(true, function ($q) use ($dir) {
                switch ($this->sortField) {
                    case 'apellidos':
                        $q->orderByRaw("LOWER(apellido_paterno) $dir NULLS LAST")
                            ->orderByRaw("LOWER(apellido_materno) $dir NULLS LAST");
                        break;
                    case 'name':
                    case 'numero_escalafon':
                    case 'rango':
                    case 'role':
                        $q->orderByRaw("LOWER({$this->sortField}) $dir NULLS LAST");
                        break;
                    default:
                        $q->orderByRaw("LOWER(apellido_paterno) $dir NULLS LAST")
                            ->orderByRaw("LOWER(apellido_materno) $dir NULLS LAST");
                        break;
                }
            })
            ->paginate(10);

        $rangos = User::select('rango')->distinct()->whereNotNull('rango')->pluck('rango')->sort()->values();
        $roles = User::select('role')->distinct()->whereNotNull('role')->pluck('role')->sort()->values();
        $unidades = Unidad::query()
            ->when(! auth()->user()?->isAdministradorGeneral(), fn ($q) => $q->where('id', auth()->user()?->unidad_id))
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'sigla']);

        $scope = User::query()->visibleTo(auth()->user());
        $stats = [
            'total' => (clone $scope)->count(),
            'policias' => (clone $scope)->where('role', 'policia')->count(),
            'furrieles' => (clone $scope)->where('role', 'furriel')->count(),
            'admins' => (clone $scope)->whereIn('role', ['administrador_general', 'administrador_unidad'])->count(),
            'sin_unidad' => (clone $scope)->whereNull('unidad_id')->count(),
        ];

        return view('livewire.users.index', compact('users', 'rangos', 'roles', 'unidades', 'stats'));
    }

    private function baseQuery()
    {
        return User::query()
            ->with('unidad')
            ->visibleTo(auth()->user())
            ->when($this->search !== '', function ($q) {
                $term = "%{$this->search}%";
                $q->where(function ($q) use ($term) {
                    $q->where('name', 'ILIKE', $term)
                        ->orWhere('apellido_paterno', 'ILIKE', $term)
                        ->orWhere('apellido_materno', 'ILIKE', $term)
                        ->orWhereRaw("(apellido_paterno || ' ' || apellido_materno) ILIKE ?", [$term])
                        ->orWhere('numero_escalafon', 'ILIKE', $term);
                });
            })
            ->when($this->rango !== '', fn ($q) => $q->where('rango', $this->rango))
            ->when($this->rol !== '', fn ($q) => $q->where('role', $this->rol))
            ->when($this->unidad !== '', fn ($q) => $q->where('unidad_id', $this->unidad));
    }
}
