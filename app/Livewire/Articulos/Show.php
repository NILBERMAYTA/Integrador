<?php

namespace App\Livewire\Articulos;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Articulo;
use App\Models\ArticuloSerie;
use App\Models\OperacionDetalle;

class Show extends Component
{
    use WithPagination;

    public Articulo $articulo;

    // Opcional: número de items por página para series
    public int $perPage = 15;

    public function mount(Articulo $articulo)
    {
        $this->articulo = $articulo;
    }

    public function render()
    {
        // Si el artículo es por series, devolvemos la paginación de series.
        if ($this->articulo->seguimiento === 'serie') {
            $series = ArticuloSerie::query()
                ->where('articulo_id', $this->articulo->id)
                ->whereNull('deleted_at')
                ->orderBy('created_at', 'desc')
                ->paginate($this->perPage);

            return view('livewire.articulos.show', compact('series'));
        }

        // Para artículos por cantidad, mostramos los movimientos (operacion_detalles)
        $detalles = OperacionDetalle::query()
            ->with('operacion')
            ->where('articulo_id', $this->articulo->id)
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.articulos.show', compact('detalles'));
    }
}
