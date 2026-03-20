<?php

namespace App\Livewire\Unidades;

use App\Models\Unidad;
use Livewire\Component;

class Update extends Component
{
    public Unidad $unidad;

    public string $nombre = '';
    public ?string $sigla = null;
    public ?string $descripcion = null;

    public function mount(Unidad $unidad): void
    {
        $this->unidad = $unidad;
        $this->nombre = (string) $unidad->nombre;
        $this->sigla = $unidad->sigla;
        $this->descripcion = $unidad->descripcion;
    }

    protected function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:150', 'unique:unidades,nombre,'.$this->unidad->id],
            'sigla' => ['nullable', 'string', 'max:50', 'unique:unidades,sigla,'.$this->unidad->id],
            'descripcion' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function actualizarUnidad()
    {
        $data = $this->validate();

        $this->unidad->update([
            'nombre' => $data['nombre'],
            'sigla' => $data['sigla'] ?: null,
            'descripcion' => $data['descripcion'] ?: null,
        ]);

        session()->flash('success', 'Unidad actualizada correctamente.');
        return redirect()->route('unidades.index');
    }

    public function render()
    {
        return view('livewire.unidades.update');
    }
}
