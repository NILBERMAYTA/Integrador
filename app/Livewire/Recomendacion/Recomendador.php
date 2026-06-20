<?php

namespace App\Livewire\Recomendacion;

use App\Services\OrdenPublicoDecisionService;
use Livewire\Component;

class Recomendador extends Component
{
    public string $situacion = '';

    public string $legalidad = '';

    public string $conducta = '';

    public string $magnitud = '';

    public bool $planificado = false;

    public bool $vulnerables = false;

    public bool $zona_sensible = false;

    public ?array $resultado = null;

    public function updated(): void
    {
        if ($this->situacion && $this->legalidad && $this->conducta && $this->magnitud) {
            $this->evaluar();
        }
    }

    public function evaluar(): void
    {
        $this->validate([
            'situacion' => ['required', 'in:'.implode(',', array_keys(config('orden_publico.situaciones')))],
            'legalidad' => ['required', 'in:'.implode(',', array_keys(config('orden_publico.legalidades')))],
            'conducta' => ['required', 'in:'.implode(',', array_keys(config('orden_publico.conductas')))],
            'magnitud' => ['required', 'in:'.implode(',', array_keys(config('orden_publico.magnitudes')))],
            'planificado' => ['boolean'],
            'vulnerables' => ['boolean'],
            'zona_sensible' => ['boolean'],
        ]);

        $this->resultado = app(OrdenPublicoDecisionService::class)->recomendar([
            'situacion' => $this->situacion,
            'legalidad' => $this->legalidad,
            'conducta' => $this->conducta,
            'magnitud' => $this->magnitud,
            'planificado' => $this->planificado,
            'vulnerables' => $this->vulnerables,
            'zona_sensible' => $this->zona_sensible,
        ]);
    }

    public function resetForm(): void
    {
        $this->reset();
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.recomendacion.recomendador', [
            'situaciones' => config('orden_publico.situaciones'),
            'legalidades' => config('orden_publico.legalidades'),
            'conductas' => config('orden_publico.conductas'),
            'magnitudes' => config('orden_publico.magnitudes'),
        ]);
    }
}
