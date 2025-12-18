<?php

namespace App\Livewire\Mantenimientos;

use App\Models\Mantenimiento;
use App\Models\Articulo;
use App\Models\ArticuloSerie;
use Livewire\Component;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class Update extends Component
{
    public Mantenimiento $mantenimiento;
    public $articulo_id;
    public $serie_id;
    public $tipo = 'preventivo';
    public $fecha_inicio;
    public $fecha_fin;
    public $descripcion;
    public $costo;
    public $series = [];

    public function mount(Mantenimiento $mantenimiento)
    {
        $this->mantenimiento = $mantenimiento;
        $this->articulo_id = $mantenimiento->articulo_id;
        $this->serie_id = $mantenimiento->serie_id;
        $this->tipo = $mantenimiento->tipo;
        $this->fecha_inicio = optional($mantenimiento->fecha_inicio)->format('Y-m-d H:i');
        $this->fecha_fin = optional($mantenimiento->fecha_fin)->format('Y-m-d H:i');
        $this->descripcion = $mantenimiento->descripcion;
        $this->costo = $mantenimiento->costo;
        $this->loadSeries($this->articulo_id);
    }

    protected function rules(): array
    {
        return [
            'articulo_id' => ['required', 'exists:articulos,id'],
            'serie_id' => ['required', 'exists:articulo_series,id'],
            'tipo' => ['required', Rule::in(['preventivo','correctivo'])],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'descripcion' => ['nullable', 'string', 'max:5000'],
            'costo' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function updatedArticuloId($value): void
    {
        $this->serie_id = null;
        $this->loadSeries($value);
    }

    protected function loadSeries($articuloId): void
    {
        $this->series = ArticuloSerie::query()
            ->where('articulo_id', $articuloId)
            ->orderBy('codigo_serie')
            ->get(['id','codigo_serie'])
            ->map(fn($s) => ['value' => $s->id, 'label' => $s->codigo_serie])
            ->values()
            ->all();
    }

    public function actualizar()
    {
        $data = $this->validate();

        if (!empty($data['fecha_fin']) && !empty($data['fecha_inicio'])) {
            $fin = Carbon::parse($data['fecha_fin']);
            $inicio = Carbon::parse($data['fecha_inicio']);
            if ($fin->lt($inicio)) {
                $this->addError('fecha_fin', 'La fecha de fin no puede ser menor a la fecha de inicio.');
                return;
            }
        }

        $this->mantenimiento->articulo_id = $data['articulo_id'];
        $this->mantenimiento->serie_id = $data['serie_id'] ?? null;
        $this->mantenimiento->tipo = $data['tipo'];
        $this->mantenimiento->fecha_inicio = $data['fecha_inicio'] ?: null;
        $this->mantenimiento->fecha_fin = $data['fecha_fin'] ?: null;
        $this->mantenimiento->descripcion = $data['descripcion'] ?? null;
        $this->mantenimiento->costo = $data['costo'] ?? null;
        $this->mantenimiento->save();

        session()->flash('success', 'Mantenimiento actualizado correctamente.');
        return redirect()->route('mantenimientos.index');
    }

    public function render()
    {
        $articulos = Articulo::where('seguimiento', 'serie')->select('id','nombre')->orderBy('nombre')->get();

        if ($this->articulo_id && empty($this->series)) {
            $this->loadSeries($this->articulo_id);
        }

        return view('livewire.mantenimientos.update', compact('articulos'));
    }
}
