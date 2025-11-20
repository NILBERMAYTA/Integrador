<?php

namespace App\Livewire\Prestamos;

use Livewire\Component;
use App\Models\Operacion;

class RegisterSeries extends Component
{
    public $operacion_id;
    public $operacion;
    public $detalles = [];
    public $series = []; // ['detalle_id' => [ '', '', ... ]]

    public function mount(Operacion $operacion)
    {
        $this->operacion_id = $operacion->id;
        $this->operacion = $operacion->load([
            'detalles' => function ($q) {
                // Solo detalles cuyos articulos se controlan por serie
                $q->whereHas('articulo', fn ($a) => $a->where('seguimiento', 'serie'))
                  ->with('articulo');
            },
        ]);

        foreach ($this->operacion->detalles as $detalle) {
            $this->series[$detalle->id] = array_fill(0, max(1, (int) $detalle->cantidad), '');
        }
    }

    public function saveSeries()
    {
        // Placeholder: implement validation and persistence later
        session()->flash('success', 'Series guardadas (esqueleto).');
    }

    public function render()
    {
        return view('livewire.prestamos.register-series');
    }
}
