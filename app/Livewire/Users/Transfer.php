<?php

namespace App\Livewire\Users;

use App\Models\Unidad;
use App\Models\User;
use App\Services\UserTransferService;
use Livewire\Component;

class Transfer extends Component
{
    public User $user;

    public $unidad_destino_id;
    public $motivo = '';

    public function mount(User $user): void
    {
        abort_unless(auth()->user()?->isAdministradorGeneral(), 403);

        $this->user = $user->load(['unidad', 'asignacionesUnidad.unidadOrigen', 'asignacionesUnidad.unidadDestino', 'asignacionesUnidad.transferidoPor']);
        $this->unidad_destino_id = $user->unidad_id;
    }

    protected function rules(): array
    {
        return [
            'unidad_destino_id' => ['required', 'exists:unidades,id', 'different:user.unidad_id'],
            'motivo' => ['required', 'string', 'max:255'],
        ];
    }

    public function transferir(UserTransferService $service)
    {
        $data = $this->validate();

        $service->transfer(
            auth()->user(),
            $this->user,
            (int) $data['unidad_destino_id'],
            $data['motivo']
        );

        session()->flash('success', 'Usuario transferido correctamente.');

        return redirect()->route('users.index');
    }

    public function render()
    {
        $unidades = Unidad::query()
            ->where('id', '<>', $this->user->unidad_id)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'sigla']);

        $historial = $this->user->asignacionesUnidad()->latest('fecha_transferencia')->get();

        return view('livewire.users.transfer', compact('unidades', 'historial'));
    }
}
