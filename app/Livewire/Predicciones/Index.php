<?php

namespace App\Livewire\Predicciones;

use App\Services\PrediccionApiService;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Support\Arr;
use Livewire\Attributes\Url;
use Livewire\Component;

class Index extends Component
{
    #[Url(except: 100)]
    public int $limit = 100;

    public array $predicciones = [];

    public ?array $health = null;

    public ?array $trainingSummary = null;

    public ?string $error = null;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('predicciones.view'), 403);

        $this->cargarDatos();
    }

    public function actualizar(): void
    {
        $this->trainingSummary = null;
        $this->cargarDatos();
        session()->flash('success', 'Predicciones actualizadas correctamente.');
    }

    public function entrenarArmamento(): void
    {
        abort_unless(auth()->user()?->can('predicciones.train'), 403);

        try {
            $prediccionApi = app(PrediccionApiService::class);
            $this->trainingSummary = $prediccionApi->entrenarArmamento();
            $this->cargarDatos();
            session()->flash('success', 'Modelo de armamento entrenado correctamente.');
        } catch (\Throwable $exception) {
            $this->error = $exception->getMessage();
            session()->flash('error', $this->error);
        }
    }

    public function updatedLimit(): void
    {
        $this->limit = max(1, min($this->limit, 100));
        $this->cargarDatos();
    }

    public function exportPdf()
    {
        abort_unless(auth()->user()?->can('predicciones.view'), 403);

        $stats = $this->buildStats();
        $pdf = PDF::loadView('reports.predicciones-armamento', [
            'predicciones' => $this->predicciones,
            'health' => $this->health,
            'trainingSummary' => $this->trainingSummary,
            'stats' => $stats,
            'limit' => $this->limit,
            'generatedAt' => now(),
            'generatedBy' => auth()->user(),
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'predicciones_armamento_'.now()->format('Ymd_His').'.pdf'
        );
    }

    public function render()
    {
        $stats = $this->buildStats();

        return view('livewire.predicciones.index', [
            'stats' => $stats,
            'modelReady' => (bool) Arr::get($this->health, 'model_ready', false),
        ]);
    }

    protected function cargarDatos(): void
    {
        $this->error = null;

        try {
            $prediccionApi = app(PrediccionApiService::class);
            $this->health = $prediccionApi->health();
            $this->predicciones = $prediccionApi->listarPrediccionesArmamento($this->limit);
        } catch (\Throwable $exception) {
            $this->health = null;
            $this->predicciones = [];
            $this->error = $exception->getMessage();
            session()->flash('error', $this->error);
        }
    }

    protected function buildStats(): array
    {
        $inoperativo = count(array_filter($this->predicciones, fn (array $item) => ($item['estado_predicho'] ?? null) === 'inoperativo'));

        return [
            'total' => count($this->predicciones),
            'alto' => count(array_filter($this->predicciones, fn (array $item) => ($item['nivel_riesgo'] ?? null) === 'alto')),
            'medio' => count(array_filter($this->predicciones, fn (array $item) => ($item['nivel_riesgo'] ?? null) === 'medio')),
            'bajo' => count(array_filter($this->predicciones, fn (array $item) => ($item['nivel_riesgo'] ?? null) === 'bajo')),
            'inoperativo' => $inoperativo,
            'operativo' => max(0, count($this->predicciones) - $inoperativo),
        ];
    }
}
