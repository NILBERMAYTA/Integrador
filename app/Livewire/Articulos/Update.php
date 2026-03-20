<?php

namespace App\Livewire\Articulos;

use App\Models\Articulo;
use App\Models\Categoria;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Update extends Component
{
    public Articulo $articulo;

    public $categoria_id;
    public $nombre;
    public $descripcion;
    public $tipo;

    public function mount(Articulo $articulo)
    {
        $this->articulo = $articulo;
        $this->categoria_id = $articulo->categoria_id;
        $this->nombre = $articulo->nombre;
        $this->descripcion = $articulo->descripcion;
        $this->tipo = $articulo->tipo;
    }

    protected function rules()
    {
        return [
            'categoria_id' => ['required', 'integer', 'exists:categorias,id'],
            'nombre' => [
                'required',
                'string',
                'max:100',
                Rule::unique('articulos', 'nombre')
                    ->ignore($this->articulo->id)
                    ->where(fn ($q) => $q->where('categoria_id', $this->categoria_id ?: 0)),
            ],
            'descripcion' => ['nullable', 'string'],
            'tipo' => ['required', Rule::in(['reutilizable', 'consumible'])],
        ];
    }

    public function actualizararticulo()
    {
        $data = $this->validate();

        $this->articulo->update([
            'categoria_id' => $data['categoria_id'],
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?: null,
            'tipo' => $data['tipo'],
            'seguimiento' => $data['tipo'] === 'reutilizable' ? 'serie' : 'cantidad',
        ]);

        session()->flash('success', 'Articulo actualizado correctamente.');
    }

    public function render()
    {
        $categorias = Categoria::orderBy('nombre')->get(['id', 'nombre']);

        return view('livewire.articulos.update', compact('categorias'))
            ->with(['articulo' => $this->articulo]);
    }
}
