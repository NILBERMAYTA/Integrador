<?php

namespace App\Livewire\Categorias;

use App\Models\Categoria;
use Livewire\Component;

class Update extends Component
{
    public Categoria $categoria;

    // Props que bindea el _form
    public string $nombre = '';
    public string $descripcion = '';

    // /categorias/{categoria}/update  → usa Route Model Binding
    public function mount(Categoria $categoria): void
    {
        $this->categoria   = $categoria;
        $this->nombre      = (string) $categoria->nombre;
        $this->descripcion = (string) ($categoria->descripcion ?? '');
    }

    protected function rules(): array
    {
        return [
            'nombre'      => ['required', 'string', 'max:80', 'unique:categorias,nombre,' . $this->categoria->id],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.unique'   => 'Ya existe una categoría con este nombre.',
            'nombre.max'      => 'Máximo 80 caracteres.',
            'descripcion.max' => 'Máximo 255 caracteres.',
        ];
    }

    public function actualizarcategoria()
    {
        $this->validate();

        $this->categoria->update([
            'nombre'      => $this->nombre,
            'descripcion' => $this->descripcion ?: null,
        ]);

        session()->flash('success', 'Categoría actualizada con éxito.');
        return redirect()->route('categorias.index');
    }

    public function render()
    {
        return view('livewire.categorias.update');
    }
}
