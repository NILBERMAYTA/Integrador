<?php

namespace App\Livewire\Predicciones;

use App\Models\Unidad;
use App\Services\PrediccionApiService;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Url;
use Livewire\Component;

class Index extends Component
{
    #[Url(except: '')]
    public string $unidad = '';

    #[Url(except: 1)]
    public int $pagina = 1;

    public int $ultimaPagina = 1;

    public int $porPagina = 10;

    public array $predicciones = [];

    public array $stats = [
        'total' => 0,
        'alto' => 0,
        'medio' => 0,
        'bajo' => 0,
        'actual' => [],
        'futura' => [],
        'cobertura' => [],
        'horizonte_dias' => 0,
    ];

    public ?array $health = null;

    public ?array $trainingSummary = null;

    public ?array $shapGlobal = null;

    public ?array $shapIndividual = null;

    public ?string $shapError = null;

    public ?string $error = null;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('predicciones.view'), 403);

        if (! auth()->user()->isAdministradorGeneral()) {
            abort_unless(auth()->user()->unidad_id, 403);
            $this->unidad = (string) auth()->user()->unidad_id;
        }

        $this->cargarDatos();
    }

    public function actualizar(): void
    {
        $this->trainingSummary = null;
        $this->cargarDatos();
        $this->cargarExplicabilidadGlobal();
        session()->flash('success', 'Predicciones actualizadas correctamente.');
    }

    public function entrenarArmamento(): void
    {
        abort_unless(auth()->user()?->can('predicciones.train'), 403);

        try {
            $prediccionApi = app(PrediccionApiService::class);
            $this->trainingSummary = $prediccionApi->entrenarArmamento();
            $this->cargarDatos();
            $this->cargarExplicabilidadGlobal();
            session()->flash('success', 'Modelo de armamento entrenado correctamente.');
        } catch (\Throwable $exception) {
            $this->error = $exception->getMessage();
            session()->flash('error', $this->error);
        }
    }

    public function updatedUnidad(): void
    {
        if (! auth()->user()->isAdministradorGeneral()) {
            $this->unidad = (string) auth()->user()->unidad_id;
        }

        $this->pagina = 1;
        $this->shapIndividual = null;
        $this->cargarDatos();
        $this->cargarExplicabilidadGlobal();
    }

    public function cargarExplicabilidadGlobal(): void
    {
        $this->shapError = null;

        try {
            $payload = app(PrediccionApiService::class)->explicabilidadGlobalArmamento(
                $this->unidad !== '' ? (int) $this->unidad : null,
                500,
            );

            $payload['beeswarm_url'] = $this->persistShapImage(
                $payload['beeswarm_image'] ?? null,
                'beeswarm',
            );
            $payload['dependence_url'] = $this->persistShapImage(
                $payload['dependence_image'] ?? null,
                'dependence',
            );
            unset($payload['beeswarm_image'], $payload['dependence_image']);

            $this->shapGlobal = $payload;
        } catch (\Throwable $exception) {
            $this->shapGlobal = null;
            $this->shapError = $exception->getMessage();
        }

        $this->dispatch('shap-updated');
    }

    public function explicarSerie(int $serieId): void
    {
        $this->shapError = null;

        try {
            $payload = app(PrediccionApiService::class)
                ->explicarSerieArmamento($serieId);
            $payload['waterfall_url'] = $this->persistShapImage(
                $payload['waterfall_image'] ?? null,
                "waterfall-{$serieId}",
            );
            unset($payload['waterfall_image']);

            $this->shapIndividual = $payload;
        } catch (\Throwable $exception) {
            $this->shapIndividual = null;
            $this->shapError = $exception->getMessage();
        }
    }

    public function cerrarExplicacionIndividual(): void
    {
        $this->shapIndividual = null;
    }

    public function paginaAnterior(): void
    {
        if ($this->pagina > 1) {
            $this->pagina--;
            $this->cargarDatos();
        }
    }

    public function paginaSiguiente(): void
    {
        if ($this->pagina < $this->ultimaPagina) {
            $this->pagina++;
            $this->cargarDatos();
        }
    }

    public function exportPdf()
    {
        abort_unless(auth()->user()?->can('predicciones.view'), 403);

        $pdf = PDF::loadView('reports.predicciones-armamento', [
            'predicciones' => $this->predicciones,
            'health' => $this->health,
            'trainingSummary' => $this->trainingSummary,
            'stats' => $this->stats,
            'unidadNombre' => $this->selectedUnitName(),
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
        $unidades = Unidad::query()
            ->when(
                ! auth()->user()->isAdministradorGeneral(),
                fn ($query) => $query->whereKey(auth()->user()->unidad_id)
            )
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'sigla']);

        return view('livewire.predicciones.index', [
            'stats' => $this->stats,
            'unidades' => $unidades,
            'nombresUnidades' => $unidades
                ->mapWithKeys(fn (Unidad $unidad) => [
                    $unidad->id => trim(($unidad->sigla ? $unidad->sigla.' - ' : '').$unidad->nombre),
                ])
                ->all(),
            'unidadSeleccionada' => $this->selectedUnitName(),
            'modelReady' => (bool) Arr::get($this->health, 'model_ready', false),
        ]);
    }

    protected function cargarDatos(): void
    {
        $this->error = null;

        try {
            $prediccionApi = app(PrediccionApiService::class);
            $this->health = $prediccionApi->health();
            $this->trainingSummary ??= [
                'current_metrics' => $this->health['current_metrics'] ?? [],
                'future_metrics' => $this->health['future_metrics'] ?? [],
                'total_historial_futuro' => $this->health['total_historial_futuro'] ?? 0,
                'horizon_days' => $this->health['horizon_days'] ?? 0,
                'model_version' => $this->health['model_version'] ?? null,
                'total_registros' => data_get($this->health, 'current_metrics.total_registros', 0),
            ];
            $summary = $prediccionApi->resumenPrediccionesArmamento(
                $this->unidad !== '' ? (int) $this->unidad : null,
                $this->pagina,
                $this->porPagina,
            );
            $this->predicciones = $summary['items'] ?? [];
            $this->pagina = (int) ($summary['page'] ?? 1);
            $this->ultimaPagina = (int) ($summary['last_page'] ?? 1);
            $this->stats = [
                'total' => (int) ($summary['total'] ?? 0),
                'alto' => (int) data_get($summary, 'riesgo.alto', 0),
                'medio' => (int) data_get($summary, 'riesgo.medio', 0),
                'bajo' => (int) data_get($summary, 'riesgo.bajo', 0),
                'actual' => (array) ($summary['condicion_actual'] ?? []),
                'futura' => (array) ($summary['condicion_futura'] ?? []),
                'cobertura' => (array) ($summary['cobertura'] ?? []),
                'horizonte_dias' => (int) ($summary['horizonte_dias'] ?? 0),
            ];
        } catch (\Throwable $exception) {
            $this->health = null;
            $this->predicciones = [];
            $this->stats = [
                'total' => 0,
                'alto' => 0,
                'medio' => 0,
                'bajo' => 0,
                'actual' => [],
                'futura' => [],
                'cobertura' => [],
                'horizonte_dias' => 0,
            ];
            $this->error = $exception->getMessage();
            session()->flash('error', $this->error);
        }

        $this->dispatch('predictions-updated');
    }

    protected function selectedUnitName(): string
    {
        if ($this->unidad === '') {
            return 'Todas las unidades';
        }

        $unit = Unidad::query()->find((int) $this->unidad);

        return $unit
            ? trim(($unit->sigla ? $unit->sigla.' - ' : '').$unit->nombre)
            : 'Unidad no disponible';
    }

    protected function persistShapImage(?string $dataUri, string $prefix): ?string
    {
        if (! $dataUri || ! str_contains($dataUri, ',')) {
            return null;
        }

        [, $encoded] = explode(',', $dataUri, 2);
        $contents = base64_decode($encoded, true);
        if ($contents === false) {
            return null;
        }

        $path = 'shap/'.preg_replace('/[^a-zA-Z0-9_-]/', '-', $prefix)
            .'-'.substr(hash('sha256', $contents), 0, 16).'.png';
        Storage::disk('public')->put($path, $contents);

        return Storage::disk('public')->url($path);
    }
}
