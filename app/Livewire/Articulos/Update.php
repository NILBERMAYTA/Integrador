<?php

namespace App\Livewire\Articulos;

use App\Models\Articulo;
use App\Models\Categoria;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Update extends Component
{
    public Articulo $articulo;

    // Campos editables (clonados del modelo)
    public $categoria_id;
    public $nombre;
    public $unidad_medida;
    public $descripcion;
    public $tipo;
    public $seguimiento;

    public function mount(Articulo $articulo)
    {
        $this->articulo = $articulo;

        $this->categoria_id  = $articulo->categoria_id;
        $this->nombre        = $articulo->nombre;
        $this->unidad_medida = $articulo->unidad_medida;
        $this->descripcion   = $articulo->descripcion;
        $this->tipo          = $articulo->tipo;
        $this->seguimiento   = $articulo->seguimiento;
    }

    protected function rules()
    {
        return [
            'categoria_id'  => ['required','integer','exists:categorias,id'],
            'nombre'        => [
                'required','string','max:100',
                Rule::unique('articulos','nombre')
                    ->ignore($this->articulo->id)
                    ->where(fn($q) => $q->where('categoria_id', $this->categoria_id ?: 0)),
            ],
            'unidad_medida' => ['nullable','string','max:20'],
            'descripcion'   => ['nullable','string'],
            'tipo'          => ['required', Rule::in(['reutilizable','consumible'])],
            'seguimiento'   => ['required', Rule::in(['serie','cantidad'])],
        ];
    }

    public function updatedSeguimiento($val)
    {
        if ($val === 'serie') {
            $this->tipo = 'reutilizable';
        }
    }

    public function actualizararticulo()
    {
        $data = $this->validate();

        if ($data['seguimiento'] === 'serie' && $data['tipo'] !== 'reutilizable') {
            $this->addError('tipo','Si seguimiento es "serie", el tipo debe ser "reutilizable".');
            return;
        }

        $this->articulo->update([
            'categoria_id'  => $data['categoria_id'],
            'nombre'        => $data['nombre'],
            'unidad_medida' => $data['unidad_medida'] ?: null,
            'descripcion'   => $data['descripcion'] ?: null,
            'tipo'          => $data['tipo'],
            'seguimiento'   => $data['seguimiento'],
        ]);

        session()->flash('success', 'Artículo actualizado correctamente.');
        // te quedas en la misma vista de edición
    }

    public function render()
    {
        $categorias = Categoria::orderBy('nombre')->get(['id','nombre']);
        return view('livewire.articulos.update', compact('categorias'))
               ->with(['articulo' => $this->articulo, 'seguimiento' => $this->seguimiento]);
    }
}

