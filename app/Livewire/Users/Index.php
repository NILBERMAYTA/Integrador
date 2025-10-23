<?php

namespace App\Livewire\Users;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;

class Index extends Component
{
    use WithPagination;

    // Filtros
    #[Url(except: '')]
    public string $search = '';
    
    #[Url(except: '')]
    public string $rango = '';
    
    #[Url(except: '')]
    public string $rol = '';

    // Orden
    #[Url(except: 'apellidos')]
    public string $sortField = 'apellidos';
    
    #[Url(except: 'asc')]
    public string $sortDirection = 'asc';

    // Resetear página al cambiar filtros/búsqueda
    public function updatedSearch() { $this->resetPage(); }
    public function updatedRango()  { $this->resetPage(); }
    public function updatedRol()    { $this->resetPage(); }

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

    /**
     * Eliminar usuario (soft delete)
     */
    public function confirmarEliminacion(int $userId): void
    {
        try {
            $user = User::findOrFail($userId);
            $user->delete(); 

            session()->flash('success', 'Usuario eliminado exitosamente.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar el usuario: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $dir = $this->sortDirection === 'desc' ? 'DESC' : 'ASC';

        $users = User::query()
            // Buscar por nombre, apellidos o número de escalafón
            ->when($this->search !== '', function ($q) {
                $term = "%{$this->search}%";
                $q->where(function ($q) use ($term) {
                    $q->where('name', 'ILIKE', $term)
                      ->orWhere('apellido_paterno', 'ILIKE', $term)
                      ->orWhere('apellido_materno', 'ILIKE', $term)
                      ->orWhereRaw("CONCAT(apellido_paterno, ' ', apellido_materno) ILIKE ?", [$term])
                      ->orWhere('numero_escalafon', 'ILIKE', $term);
                });
            })
            // Filtros por rango y rol
            ->when($this->rango !== '', fn($q) => $q->where('rango', $this->rango))
            ->when($this->rol !== '',   fn($q) => $q->where('role',  $this->rol))

            // Ordenamiento
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

        // Catálogos para selects
        $rangos = User::select('rango')->distinct()->whereNotNull('rango')->pluck('rango')->sort()->values();
        $roles  = User::select('role')->distinct()->whereNotNull('role')->pluck('role')->sort()->values();

        return view('livewire.users.index', compact('users', 'rangos', 'roles'));
    }
}