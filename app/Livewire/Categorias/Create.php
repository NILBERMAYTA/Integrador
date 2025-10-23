<?php

namespace App\Livewire\Categorias;

use App\Models\Categoria;
use Livewire\Component;

class Create extends Component
{
    public string $nombre = '';
    public string $descripcion = '';

    protected function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:80', 'unique:categorias,nombre'],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.unique'   => 'Ya existe una categoría con este nombre.',
            'nombre.max'      => 'El nombre no puede superar los 80 caracteres.',
            'descripcion.max' => 'La descripción no puede superar los 255 caracteres.',
        ];
    }

    public function guardarcategoria()
    {
        $this->validate();

        Categoria::create([
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion ?: null,
        ]);

        session()->flash('success', 'Categoría registrada exitosamente.');
        return redirect()->route('categorias.index');
    }

    public function render()
    {
        return view('livewire.categorias.create');
    }
}
