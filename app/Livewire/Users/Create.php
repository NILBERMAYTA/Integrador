<?php

namespace App\Livewire\Users;

use App\Models\Unidad;
use App\Models\User;
use App\Services\UserTransferService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
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
    public $unidad_id;
    public $foto;
    public $foto_actual = null;

    public function mount(): void
    {
        abort_unless(auth()->check(), 403);

        if (! auth()->user()->isAdministradorGeneral()) {
            $this->unidad_id = auth()->user()->unidad_id;
            $this->role = 'policia';
        }
    }

    protected function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'apellido_paterno' => ['nullable', 'string', 'max:255'],
            'apellido_materno' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', Password::min(8)->letters()->numbers()],
            'rango' => ['nullable', 'string', 'max:255'],
            'numero_escalafon' => ['nullable', 'string', 'max:255'],
            'fecha_ingreso' => ['nullable', 'date'],
            'role' => ['required', Rule::in($this->allowedRoles())],
            'unidad_id' => [
                'required',
                'exists:unidades,id',
                Rule::in($this->allowedUnidadIds()),
            ],
            'foto' => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function guardaruser()
    {
        $data = $this->validate();

        if (! auth()->user()->isAdministradorGeneral()) {
            $data['unidad_id'] = auth()->user()->unidad_id;
        }

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
        $user->can_login = true;
        $user->unidad_id = (int) $data['unidad_id'];

        if (! empty($data['foto'])) {
            $user->foto = $data['foto']->store('policias', 'public');
        }

        $user->save();
        $user->syncRoles([$data['role']]);

        app(UserTransferService::class)->registerInitialAssignment(
            auth()->user(),
            $user,
            (int) $data['unidad_id'],
            'Asignacion inicial de unidad al crear usuario'
        );

        session()->flash('success', 'Usuario creado correctamente.');

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

        return view('livewire.users.create', compact('unidades', 'rolesDisponibles'));
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
