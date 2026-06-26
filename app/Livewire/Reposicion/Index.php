<?php

namespace App\Livewire\Reposicion;

use App\Models\Unidad;
use App\Services\ReposicionArmamentoService;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Livewire\Attributes\Url;
use Livewire\Component;

class Index extends Component
{
    #[Url(except: '')]
    public string $unidad = '';

    public array $resumen = [];

    public array $recomendaciones = [];

    public array $resumenUnidades = [];

    public int $horizonteDias = 0;

    public ?string $error = null;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('reposicion.view'), 403);

        if (! auth()->user()->isAdministradorGeneral()) {
            abort_unless(auth()->user()->unidad_id, 403);
            $this->unidad = (string) auth()->user()->unidad_id;
        }

        $this->cargar();
    }

    public function actualizar(): void
    {
        $this->cargar();
        session()->flash('success', 'Calculo de reposicion actualizado correctamente.');
    }

    public function updatedUnidad(): void
    {
        if (! auth()->user()->isAdministradorGeneral()) {
            $this->unidad = (string) auth()->user()->unidad_id;
        }

        $this->cargar();
    }

    public function exportPdf()
    {
        abort_unless(auth()->user()?->can('reposicion.view'), 403);

        $pdf = PDF::loadView('reports.reposicion-armamento', [
            'resumen' => $this->resumen,
            'recomendaciones' => $this->recomendaciones,
            'resumenUnidades' => $this->resumenUnidades,
            'distribucion' => $this->buildDistribucion(),
            'generatedAt' => now(),
            'generatedBy' => auth()->user(),
            'unidadNombre' => $this->selectedUnitName(),
            'horizonteDias' => $this->horizonteDias,
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'reposicion_armamento_'.now()->format('Ymd_His').'.pdf'
        );
    }

    public function render()
    {
        $unidades = Unidad::query()
            ->when(
                ! auth()->user()->isAdministradorGeneral(),
                fn ($query) => $query->whereKey(auth()->user()->unidad_id)
            )
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'sigla']);

        return view('livewire.reposicion.index', [
            'unidades' => $unidades,
            'unidadSeleccionada' => $this->selectedUnitName(),
        ]);
    }

    protected function cargar(): void
    {
        $this->error = null;

        try {
            $payload = app(ReposicionArmamentoService::class)->calcular(
                $this->unidad !== '' ? (int) $this->unidad : null,
            );
            $this->resumen = $payload['resumen'] ?? [];
            $this->recomendaciones = $payload['recomendaciones'] ?? [];
            $this->resumenUnidades = $payload['unidades'] ?? [];
            $this->horizonteDias = (int) ($payload['horizonte_dias'] ?? 0);
        } catch (\Throwable $exception) {
            $this->resumen = [];
            $this->recomendaciones = [];
            $this->resumenUnidades = [];
            $this->horizonteDias = 0;
            $this->error = $exception->getMessage();
            session()->flash('error', $this->error);
        }

        $this->dispatch('reposicion-updated');
    }

    protected function selectedUnitName(): string
    {
        if ($this->unidad === '') {
            return 'Todas las unidades';
        }

        $unidad = Unidad::query()->find((int) $this->unidad);

        return $unidad
            ? trim(($unidad->sigla ? $unidad->sigla.' - ' : '').$unidad->nombre)
            : 'Unidad no disponible';
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
