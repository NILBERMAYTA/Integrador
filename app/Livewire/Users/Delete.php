<?php

namespace App\Livewire\Users;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class Delete extends Component
{
    use WithPagination;

    public $search = '';
    public $rango = '';
    public $rol = '';
    public $sortField = 'deleted_at';
    public $sortDirection = 'desc';

    protected $queryString = [
        'search' => ['except' => ''],
        'rango' => ['except' => ''],
        'rol' => ['except' => ''],
        'sortField' => ['except' => 'deleted_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    /**
     * Resetear paginación cuando cambia la búsqueda
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingRango()
    {
        $this->resetPage();
    }

    public function updatingRol()
    {
        $this->resetPage();
    }

    /**
     * Ordenar por campo
     */
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    /**
     * Restaurar usuario
     */
    public function restaurar($userId)
    {
        try {
            $user = User::onlyTrashed()->findOrFail($userId);
            $user->restore();

            session()->flash('success', 'Usuario restaurado exitosamente.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al restaurar el usuario: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar permanentemente
     */
    public function eliminarPermanentemente($userId)
    {
        try {
            $user = User::onlyTrashed()->findOrFail($userId);
            $user->forceDelete();

            session()->flash('success', 'Usuario eliminado permanentemente.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar permanentemente: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $query = User::onlyTrashed();

        // Búsqueda
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'ilike', '%' . $this->search . '%')
                  ->orWhere('apellido_paterno', 'ilike', '%' . $this->search . '%')
                  ->orWhere('apellido_materno', 'ilike', '%' . $this->search . '%')
                  ->orWhere('numero_escalafon', 'ilike', '%' . $this->search . '%')
                  ->orWhere('email', 'ilike', '%' . $this->search . '%');
            });
        }

        // Filtro por rango
        if ($this->rango) {
            $query->where('rango', $this->rango);
        }

        // Filtro por rol
        if ($this->rol) {
            $query->where('role', $this->rol);
        }

        // Ordenamiento
        if ($this->sortField === 'apellidos') {
            $query->orderByApellidos($this->sortDirection);
        } else {
            $query->orderBy($this->sortField, $this->sortDirection);
        }

        $users = $query->paginate(10);

        // Obtener rangos y roles únicos para los filtros (incluir eliminados)
        $rangos = User::onlyTrashed()
            ->whereNotNull('rango')
            ->distinct()
            ->pluck('rango')
            ->sort()
            ->values();

        $roles = ['admin', 'furriel', 'policia'];

        return view('livewire.users.delete', [
            'users' => $users,
            'rangos' => $rangos,
            'roles' => $roles,
        ]);
    }
}