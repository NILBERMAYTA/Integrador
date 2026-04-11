<?php

namespace App\Livewire\Reposicion;

use App\Services\ReposicionArmamentoService;
use Livewire\Component;

class Index extends Component
{
    public array $resumen = [];

    public array $recomendaciones = [];

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('reposicion.view'), 403);

        $this->cargar();
    }

    public function actualizar(): void
    {
        $this->cargar();
        session()->flash('success', 'Calculo de reposicion actualizado correctamente.');
    }

    public function render()
    {
        return view('livewire.reposicion.index');
    }

    protected function cargar(): void
    {
        $service = app(ReposicionArmamentoService::class);
        $this->resumen = $service->resumenGeneral();
        $this->recomendaciones = $service->recomendaciones()->all();
    }
}
