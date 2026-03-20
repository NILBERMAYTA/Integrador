<?php

namespace App\Livewire\Unidades;

use App\Models\Unidad;
use Livewire\Component;

class Create extends Component
{
    public string $nombre = '';
    public ?string $sigla = null;
    public ?string $descripcion = null;

    protected function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:150', 'unique:unidades,nombre'],
            'sigla' => ['nullable', 'string', 'max:50', 'unique:unidades,sigla'],
            'descripcion' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function guardarUnidad()
    {
        $data = $this->validate();

        Unidad::create([
            'nombre' => $data['nombre'],
            'sigla' => $data['sigla'] ?: null,
            'descripcion' => $data['descripcion'] ?: null,
        ]);

        session()->flash('success', 'Unidad creada correctamente.');
        return redirect()->route('unidades.index');
    }

    public function render()
    {
        return view('livewire.unidades.create');
    }
}
