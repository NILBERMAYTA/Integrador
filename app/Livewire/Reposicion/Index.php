<?php

namespace App\Livewire\Reposicion;

use App\Services\ReposicionArmamentoService;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
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

    public function exportPdf()
    {
        abort_unless(auth()->user()?->can('reposicion.view'), 403);

        $pdf = PDF::loadView('reports.reposicion-armamento', [
            'resumen' => $this->resumen,
            'recomendaciones' => $this->recomendaciones,
            'distribucion' => $this->buildDistribucion(),
            'generatedAt' => now(),
            'generatedBy' => auth()->user(),
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'reposicion_armamento_'.now()->format('Ymd_His').'.pdf'
        );
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

        $this->dispatch('reposicion-updated');
    }

    protected function buildDistribucion(): array
    {
        $urgencias = collect($this->recomendaciones)->countBy('urgencia');

        return [
            'inmediata' => (int) ($urgencias['inmediata'] ?? 0),
            'proxima' => (int) ($urgencias['proxima'] ?? 0),
            'planificada' => (int) ($urgencias['planificada'] ?? 0),
            'estable' => (int) ($urgencias['estable'] ?? 0),
            'ahora' => (int) ($urgencias['inmediata'] ?? 0),
            'pronto' => (int) ($urgencias['proxima'] ?? 0),
            'luego' => (int) (($urgencias['planificada'] ?? 0) + ($urgencias['estable'] ?? 0)),
        ];
    }
}
