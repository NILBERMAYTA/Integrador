<?php

namespace App\Livewire\Eventos;

use App\Models\Evento;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Create extends Component
{
    public string $nombre = '';
    public string $descripcion = '';
    public ?string $fecha_inicio = null;
    public ?string $fecha_fin = null;
    public string $nivel = 'medio';
    public string $estado = 'planificado';

    protected function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:120', 'unique:eventos,nombre'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'nivel' => ['required', Rule::in(Evento::NIVELES)],
            'estado' => ['required', Rule::in(Evento::ESTADOS)],
        ];
    }

    protected function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.unique' => 'Ya existe un evento con ese nombre.',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
        ];
    }

    public function guardarevento()
    {
        $this->validate();

        Evento::create([
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion ?: null,
            'fecha_inicio' => $this->fecha_inicio ?: null,
            'fecha_fin' => $this->fecha_fin ?: null,
            'nivel' => $this->nivel,
            'estado' => $this->estado,
        ]);

        session()->flash('success', 'Evento registrado exitosamente.');
        return redirect()->route('eventos.index');
    }

    public function render()
    {
        return view('livewire.eventos.create');
    }
}
