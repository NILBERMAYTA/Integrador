<?php

namespace App\Livewire\Users;

use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;

    public $name, $apellido_paterno, $apellido_materno;
    public $email, $password;
    public $rango, $numero_escalafon, $fecha_ingreso;
    public $role = 'policia';
    public $can_login = true;
    public $foto;
    public $foto_actual = null;

    protected function rules()
    {
        return [
            'name' => ['required','string','max:255'],
            'apellido_paterno' => ['nullable','string','max:255'],
            'apellido_materno' => ['nullable','string','max:255'],
            'email' => ['required','email','max:255', Rule::unique('users','email')],
            'password' => ['required', 'string', Password::min(8)->letters()->numbers()],
            'rango' => ['nullable','string','max:255'],
            'numero_escalafon' => ['nullable','string','max:255'],
            'fecha_ingreso' => ['nullable','date'],
            'role' => ['required','in:policia,furriel,admin'],
            'can_login' => ['boolean'],
            'foto' => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function guardaruser()
    {
        $data = $this->validate();
        $user = new User();
        $user->name = $data['name'];
        $user->apellido_paterno = $data['apellido_paterno'] ?? null;
        $user->apellido_materno = $data['apellido_materno'] ?? null;
        $user->email = $data['email'];
        $user->password = Hash::make($data['password']);
        $user->rango = $data['rango'] ?? null;
        $user->numero_escalafon = $data['numero_escalafon'] ?? null;
        $user->fecha_ingreso = $data['fecha_ingreso'] ?? null;
        $user->role = $data['role'];
        $user->can_login = (bool) $data['can_login'];
        if (!empty($data['foto'])) {
            $user->foto = $data['foto']->store('policias', 'public');
        }
        $user->save();
        $user->syncRoles([$data['role']]);

        session()->flash('success','Usuario creado correctamente.');
        return redirect()->route('users.index');
    }

    public function render() { return view('livewire.users.create'); }
}
