<?php

namespace App\Livewire\Users;

use App\Models\Unidad;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;
use Livewire\WithFileUploads;

class Update extends Component
{
    use WithFileUploads;

    public User $user;

    public $name, $apellido_paterno, $apellido_materno;
    public $email, $password;
    public $rango, $numero_escalafon, $fecha_ingreso;
    public $role, $can_login;
    public $unidad_id;
    public $foto;
    public $foto_actual = null;

    public function mount(User $user)
    {
        abort_unless(
            auth()->user()?->isAdministradorGeneral() || auth()->user()?->unidad_id === $user->unidad_id,
            403
        );

        $this->user = $user->load('unidad');

        $this->fill([
            'name' => $user->name,
            'apellido_paterno' => $user->apellido_paterno,
            'apellido_materno' => $user->apellido_materno,
            'email' => $user->email,
            'rango' => $user->rango,
            'numero_escalafon' => $user->numero_escalafon,
            'fecha_ingreso' => optional($user->fecha_ingreso)->format('Y-m-d'),
            'role' => $user->role,
            'can_login' => (bool) $user->can_login,
            'unidad_id' => $user->unidad_id,
            'foto_actual' => $user->foto,
        ]);
    }

    protected function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'apellido_paterno' => ['nullable', 'string', 'max:255'],
            'apellido_materno' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user->id)],
            'password' => ['nullable', 'string', Password::min(8)->letters()->numbers()],
            'rango' => ['nullable', 'string', 'max:255'],
            'numero_escalafon' => ['nullable', 'string', 'max:255'],
            'fecha_ingreso' => ['nullable', 'date'],
            'role' => ['required', Rule::in($this->allowedRoles())],
            'can_login' => ['boolean'],
            'foto' => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function actualizaruser()
    {
        $data = $this->validate();

        $this->user->fill([
            'name' => $data['name'],
            'apellido_paterno' => $data['apellido_paterno'] ?? null,
            'apellido_materno' => $data['apellido_materno'] ?? null,
            'email' => $data['email'],
            'rango' => $data['rango'] ?? null,
            'numero_escalafon' => $data['numero_escalafon'] ?? null,
            'fecha_ingreso' => $data['fecha_ingreso'] ?? null,
            'role' => $data['role'],
            'can_login' => (bool) $data['can_login'],
        ]);

        if (! empty($data['password'])) {
            $this->user->password = Hash::make($data['password']);
        }

        if (! empty($data['foto'])) {
            if (! empty($this->user->foto)) {
                Storage::disk('public')->delete($this->user->foto);
            }

            $this->user->foto = $data['foto']->store('policias', 'public');
            $this->foto_actual = $this->user->foto;
        }

        $this->user->save();
        $this->user->syncRoles([$data['role']]);

        session()->flash('success', 'Usuario actualizado correctamente.');

        return redirect()->route('users.index');
    }

    public function render()
    {
        $unidades = Unidad::query()
            ->whereIn('id', $this->allowedUnidadIds())
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'sigla']);

        $rolesDisponibles = collect($this->allowedRoles())
            ->map(fn (string $role) => [
                'value' => $role,
                'label' => match ($role) {
                    'administrador_general' => 'Administrador General',
                    'administrador_unidad' => 'Administrador de Unidad',
                    'furriel' => 'Furriel',
                    default => 'Policia',
                },
            ])
            ->values()
            ->all();

        return view('livewire.users.update', compact('unidades', 'rolesDisponibles'));
    }

    private function allowedUnidadIds(): array
    {
        $actor = auth()->user();

        if ($actor->isAdministradorGeneral()) {
            return Unidad::query()->pluck('id')->all();
        }

        return [$actor->unidad_id];
    }

    private function allowedRoles(): array
    {
        if (auth()->user()->isAdministradorGeneral()) {
            return ['administrador_general', 'administrador_unidad', 'furriel', 'policia'];
        }

        return ['administrador_unidad', 'furriel', 'policia'];
    }
}
