<?php

namespace App\Livewire\Users;

use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Storage;
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
    public $foto;
    public $foto_actual = null;

    public function mount(User $user)
    {
        $this->user = $user;
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
            'foto_actual' => $user->foto,
        ]);
    }

    protected function rules()
    {
        return [
            'name' => ['required','string','max:255'],
            'apellido_paterno' => ['nullable','string','max:255'],
            'apellido_materno' => ['nullable','string','max:255'],
            'email' => ['required','email','max:255', Rule::unique('users','email')->ignore($this->user->id)],
            'password' => ['nullable', 'string', Password::min(8)->letters()->numbers()],
            'rango' => ['nullable','string','max:255'],
            'numero_escalafon' => ['nullable','string','max:255'],
            'fecha_ingreso' => ['nullable','date'],
            'role' => ['required','in:policia,furriel,admin'],
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

        if (!empty($data['password'])) {
            $this->user->password = Hash::make($data['password']);
        }

        if (!empty($data['foto'])) {
            if (!empty($this->user->foto)) {
                Storage::disk('public')->delete($this->user->foto);
            }
            $this->user->foto = $data['foto']->store('policias', 'public');
            $this->foto_actual = $this->user->foto;
        }

        $this->user->save();
        $this->user->syncRoles([$data['role']]);

        session()->flash('success','Usuario actualizado correctamente.');
        return redirect()->route('users.index');
    }

    public function render() { return view('livewire.users.update'); }
}
