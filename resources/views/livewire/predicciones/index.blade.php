<div class="prediction-shell space-y-6" wire:init="cargarExplicabilidadGlobal">
    @php
        $alto = (int) ($stats['alto'] ?? 0);
        $medio = (int) ($stats['medio'] ?? 0);
        $bajo = (int) ($stats['bajo'] ?? 0);
        $inoperativoCount = (int) ($stats['inoperativo'] ?? 0);
        $operativoCount = (int) ($stats['operativo'] ?? 0);

        $predictionChartData = [
            'risk' => [
                'labels' => ['Alto', 'Medio', 'Bajo'],
                'series' => [$alto, $medio, $bajo],
            ],
            'status' => [
                'labels' => ['Operativo', 'Inoperativo'],
                'series' => [$operativoCount, $inoperativoCount],
            ],
        ];
    @endphp

    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div class="space-y-2">
            <h1 class="text-3xl font-bold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">
                Predicciones de armamento
            </h1>
            <p class="text-sm opacity-70">
                Resumen general de todas las series del alcance seleccionado.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <x-form.header_button variant="export" type="button" wire:click="exportPdf">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 16l4-4m-4 4l-4-4m4 4V4m0 12v4m-7 0h14" />
                </svg>
                Exportar PDF
            </x-form.header_button>

            <x-form.header_button variant="neutral" type="button" wire:click="actualizar">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Actualizar
            </x-form.header_button>

            @can('reposicion.view')
                <x-form.header_button variant="neutral" href="{{ route('reposicion.index') }}" wire:navigate>
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V7a2 2 0 00-2-2H6a2 2 0 00-2 2v6m16 0l-8 8-8-8" />
                    </svg>
                    Cálculo de reposiciones
                </x-form.header_button>
            @endcan

            @can('predicciones.train')
                <x-form.header_button variant="primary" type="button" wire:click="entrenarArmamento">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6m3 6V7m3 10v-4m3 8H6a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 01-2 2z" />
                    </svg>
                    Reentrenar modelo
                </x-form.header_button>
            @endcan
        </div>
    </div>

    <x-form.toast_notification :message="session('success')" variant="success" />
    <x-form.toast_notification :message="session('error')" variant="danger" />

    <div class="grid gap-6 xl:grid-cols-2">
        <div class="rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-[var(--color-surface)] p-6 shadow-sm dark:border-[var(--color-outline-dark)] dark:bg-[var(--color-surface-dark)]">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
                <div data-prediction-chart="risk" class="min-h-[220px] w-full shrink-0 sm:w-[250px]" wire:ignore></div>

                <div class="flex-1">
                    <h2 class="text-lg font-semibold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">Distribución de riesgo</h2>
                    <div class="mt-4 space-y-3 text-sm">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="h-3 w-3 rounded-full bg-rose-500"></span>
                                <span>Alto</span>
                            </div>
                            <span class="font-semibold">{{ $alto }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="h-3 w-3 rounded-full bg-amber-500"></span>
                                <span>Medio</span>
                            </div>
                            <span class="font-semibold">{{ $medio }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="h-3 w-3 rounded-full bg-emerald-500"></span>
                                <span>Bajo</span>
                            </div>
                            <span class="font-semibold">{{ $bajo }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-[var(--color-surface)] p-6 shadow-sm dark:border-[var(--color-outline-dark)] dark:bg-[var(--color-surface-dark)]">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
                <div data-prediction-chart="status" class="min-h-[220px] w-full shrink-0 sm:w-[250px]" wire:ignore></div>

                <div class="flex-1">
                    <h2 class="text-lg font-semibold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">Estado predicho</h2>
                    <div class="mt-4 space-y-3 text-sm">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="h-3 w-3 rounded-full bg-emerald-500"></span>
                                <span>Operativo</span>
                            </div>
                            <span class="font-semibold">{{ $operativoCount }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="h-3 w-3 rounded-full bg-rose-500"></span>
                                <span>Inoperativo</span>
                            </div>
                            <span class="font-semibold">{{ $inoperativoCount }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-[var(--color-surface)] p-5 shadow-sm dark:border-[var(--color-outline-dark)] dark:bg-[var(--color-surface-dark)]">
            <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-on-surface)] opacity-60 dark:text-[var(--color-on-surface-dark)]">Estado</p>
            <div class="mt-3 flex items-center gap-3">
                <span class="inline-flex h-3 w-3 rounded-full {{ $modelReady ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                <p class="text-lg font-semibold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">
                    {{ $modelReady ? 'Modelo disponible' : 'Modelo no entrenado' }}
                </p>
            </div>
        </div>

        <div class="rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-[var(--color-surface)] p-5 shadow-sm dark:border-[var(--color-outline-dark)] dark:bg-[var(--color-surface-dark)]">
            <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-on-surface)] opacity-60 dark:text-[var(--color-on-surface-dark)]">Series analizadas</p>
            <p class="mt-3 text-3xl font-bold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">{{ $stats['total'] }}</p>
            <p class="mt-2 text-sm text-[var(--color-on-surface)] opacity-75 dark:text-[var(--color-on-surface-dark)]">
                {{ $unidadSeleccionada }}
            </p>
        </div>

        <div class="rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-[var(--color-surface)] p-5 shadow-sm dark:border-[var(--color-outline-dark)] dark:bg-[var(--color-surface-dark)]">
            <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-on-surface)] opacity-60 dark:text-[var(--color-on-surface-dark)]">Riesgo alto</p>
            <p class="mt-3 text-3xl font-bold text-rose-600">{{ $stats['alto'] }}</p>
            <p class="mt-2 text-sm text-[var(--color-on-surface)] opacity-75 dark:text-[var(--color-on-surface-dark)]">
                Articulos con atencion inmediata recomendada.
            </p>
        </div>

        <div class="rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-[var(--color-surface)] p-5 shadow-sm dark:border-[var(--color-outline-dark)] dark:bg-[var(--color-surface-dark)]">
            <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-on-surface)] opacity-60 dark:text-[var(--color-on-surface-dark)]">Riesgo medio/bajo</p>
            <p class="mt-3 text-3xl font-bold text-amber-600">{{ $stats['medio'] + $stats['bajo'] }}</p>
            <p class="mt-2 text-sm text-[var(--color-on-surface)] opacity-75 dark:text-[var(--color-on-surface-dark)]">
                Seguimiento preventivo o monitoreo rutinario.
            </p>
        </div>
    </div>

    <section class="space-y-5">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold">Explicabilidad del modelo con SHAP</h2>
                <p class="mt-1 text-sm opacity-70">
                    Qué variables influyen, en qué dirección y cómo se construye cada predicción.
                </p>
            </div>
            @if($shapGlobal)
                <div class="text-sm opacity-70">
                    Muestra SHAP: {{ $shapGlobal['sample_size'] ?? 0 }} de {{ $shapGlobal['total_records'] ?? 0 }} series
                </div>
            @endif
        </div>

        <div wire:loading wire:target="cargarExplicabilidadGlobal,updatedUnidad" class="w-full">
            <div class="grid gap-5 xl:grid-cols-2">
                <div class="skeleton h-80 w-full"></div>
                <div class="skeleton h-80 w-full"></div>
            </div>
        </div>

        @if($shapGlobal)
            @php
                $shapFeatures = collect($shapGlobal['importance'] ?? [])->take(10)->values();
                $shapChartData = [
                    'importance' => [
                        'labels' => $shapFeatures->pluck('label')->all(),
                        'series' => $shapFeatures->pluck('importance')->map(fn($value) => (float) $value)->all(),
                    ],
                    'direction' => [
                        'labels' => $shapFeatures->pluck('label')->all(),
                        'positive' => $shapFeatures->pluck('positive_impact')->map(fn($value) => (float) $value)->all(),
                        'negative' => $shapFeatures->pluck('negative_impact')->map(fn($value) => (float) $value)->all(),
                    ],
                ];
            @endphp

            <div class="grid gap-5 xl:grid-cols-2">
                <div class="card card-border bg-base-100">
                    <div class="card-body">
                        <h3 class="card-title">Importancia global de variables</h3>
                        <p class="text-sm opacity-70">
                            Promedio del impacto absoluto de cada variable sobre la probabilidad de inoperatividad.
                        </p>
                        <div data-shap-chart="importance" class="mt-3 min-h-[360px]" wire:ignore></div>
                    </div>
                </div>

                <div class="card card-border bg-base-100">
                    <div class="card-body">
                        <h3 class="card-title">Impacto positivo y negativo</h3>
                        <p class="text-sm opacity-70">
                            Rojo aumenta el riesgo predicho; azul reduce la probabilidad de inoperatividad.
                        </p>
                        <div data-shap-chart="direction" class="mt-3 min-h-[360px]" wire:ignore></div>
                    </div>
                </div>

                <div class="card card-border bg-base-100">
                    <div class="card-body">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <h3 class="card-title">SHAP beeswarm</h3>
                            <span class="badge badge-info badge-soft">Resumen profesional</span>
                        </div>
                        <p class="text-sm opacity-70">
                            Cada punto representa una serie. La posición indica cuánto empuja la variable hacia mayor o menor riesgo.
                        </p>
                        @if($shapGlobal['beeswarm_url'] ?? null)
                            <img
                                src="{{ $shapGlobal['beeswarm_url'] }}"
                                alt="Gráfico SHAP beeswarm"
                                class="mt-3 w-full rounded-box bg-white object-contain"
                            />
                        @endif
                    </div>
                </div>

                <div class="card card-border bg-base-100">
                    <div class="card-body">
                        <h3 class="card-title">Dependencia de la variable dominante</h3>
                        <p class="text-sm opacity-70">
                            Relación entre el valor observado y su impacto SHAP. Variable destacada:
                            <strong>{{ $shapGlobal['top_feature'] ?? '—' }}</strong>.
                        </p>
                        @if($shapGlobal['dependence_url'] ?? null)
                            <img
                                src="{{ $shapGlobal['dependence_url'] }}"
                                alt="Gráfico SHAP de dependencia"
                                class="mt-3 w-full rounded-box bg-white object-contain"
                            />
                        @endif
                    </div>
                </div>
            </div>

            <script type="application/json" data-shap-chart-data>@json($shapChartData)</script>
        @elseif($shapError)
            <div class="alert alert-warning">
                <span>No se pudo cargar SHAP: {{ $shapError }}</span>
            </div>
        @endif
    </section>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(320px,1fr)]">
        <div class="rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-[var(--color-surface)] shadow-sm dark:border-[var(--color-outline-dark)] dark:bg-[var(--color-surface-dark)]">
            <div class="flex flex-col gap-4 border-b border-[var(--color-outline)] px-6 py-5 dark:border-[var(--color-outline-dark)] lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">Predicciones recientes</h2>
                    <p class="mt-1 text-sm text-[var(--color-on-surface)] opacity-70 dark:text-[var(--color-on-surface-dark)]">
                        Se muestran 10 series por página; las gráficas y tarjetas usan el total completo.
                    </p>
                </div>

                <div class="w-full max-w-sm">
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-on-surface)] opacity-60 dark:text-[var(--color-on-surface-dark)]">
                        Unidad
                    </label>
                    <select
                        wire:model.live="unidad"
                        class="select w-full"
                    >
                        @if(auth()->user()->isAdministradorGeneral())
                            <option value="">Todas las unidades</option>
                        @endif
                        @foreach($unidades as $unidadItem)
                            <option value="{{ $unidadItem->id }}">
                                {{ trim(($unidadItem->sigla ? $unidadItem->sigla.' - ' : '').$unidadItem->nombre) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead class="bg-[var(--color-surface-alt)] dark:bg-[var(--color-surface-dark-alt)]">
                        <tr>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">Serie</th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">Unidad</th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">Predicción</th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">Probabilidad</th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">Explicación</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--color-outline)] dark:divide-[var(--color-outline-dark)] kiro-stagger">
                        @forelse ($predicciones as $prediccion)
                            @php
                                $riesgo = $prediccion['nivel_riesgo'] ?? 'bajo';
                                $riesgoClasses = match ($riesgo) {
                                    'alto' => 'bg-rose-100 text-rose-700',
                                    'medio' => 'bg-amber-100 text-amber-700',
                                    default => 'bg-emerald-100 text-emerald-700',
                                };
                            @endphp
                            <tr class="align-top hover:bg-[var(--color-surface-alt)] dark:hover:bg-[var(--color-surface-dark-alt)]">
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">
                                        {{ $prediccion['codigo_serie'] ?? '--' }}
                                    </p>
                                    <p class="mt-1 text-xs text-[var(--color-on-surface)] opacity-60 dark:text-[var(--color-on-surface-dark)]">
                                        Serie ID: {{ $prediccion['serie_id'] ?? '--' }}
                                    </p>
                                </td>
                                <td class="px-6 py-4 text-sm text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">
                                    {{ $prediccion['unidad_nombre'] ?? ($nombresUnidades[$prediccion['unidad_id'] ?? 0] ?? 'Sin unidad') }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-2">
                                    <span class="badge badge-sm {{ ($prediccion['estado_predicho'] ?? '') === 'inoperativo' ? 'badge-error' : 'badge-success' }}">
                                        {{ ucfirst($prediccion['estado_predicho'] ?? '--') }}
                                    </span>
                                    <span class="badge badge-sm badge-soft {{ $riesgo === 'alto' ? 'badge-error' : ($riesgo === 'medio' ? 'badge-warning' : 'badge-success') }}">
                                        {{ ucfirst($riesgo) }}
                                    </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm font-semibold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">
                                    {{ isset($prediccion['probabilidad']) ? number_format((float) $prediccion['probabilidad'] * 100, 2) . '%' : '--' }}
                                </td>
                                <td class="px-6 py-4">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline"
                                        wire:click="explicarSerie({{ $prediccion['serie_id'] }})"
                                    >
                                        Explicar
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <p class="font-medium text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">
                                        No hay predicciones disponibles.
                                    </p>
                                    <p class="mt-1 text-sm text-[var(--color-on-surface)] opacity-60 dark:text-[var(--color-on-surface-dark)]">
                                        Verifica que `ml_service` este levantado y que el modelo haya sido entrenado.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($ultimaPagina > 1)
                <div class="flex flex-col gap-3 border-t border-[var(--color-outline)] px-5 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-[var(--color-outline-dark)]">
                    <p class="text-sm opacity-70">
                        Página {{ $pagina }} de {{ $ultimaPagina }}
                    </p>
                    <div class="join">
                        <button
                            type="button"
                            class="btn btn-sm join-item"
                            wire:click="paginaAnterior"
                            @disabled($pagina <= 1)
                        >
                            Anterior
                        </button>
                        <button
                            type="button"
                            class="btn btn-sm join-item"
                            wire:click="paginaSiguiente"
                            @disabled($pagina >= $ultimaPagina)
                        >
                            Siguiente
                        </button>
                    </div>
                </div>
            @endif
        </div>

        <div class="space-y-6">
            <div class="rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-[var(--color-surface)] p-6 shadow-sm dark:border-[var(--color-outline-dark)] dark:bg-[var(--color-surface-dark)]">
                <h2 class="text-lg font-semibold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">Estado del servicio</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-[var(--color-on-surface)] opacity-70 dark:text-[var(--color-on-surface-dark)]">Estado</dt>
                        <dd class="font-semibold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">
                            {{ $health['status'] ?? 'sin respuesta' }}
                        </dd>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-[var(--color-on-surface)] opacity-70 dark:text-[var(--color-on-surface-dark)]">Modelo</dt>
                        <dd class="font-semibold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">
                            {{ !empty($health['model_ready']) ? 'listo' : 'pendiente' }}
                        </dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-[var(--color-surface)] p-6 shadow-sm dark:border-[var(--color-outline-dark)] dark:bg-[var(--color-surface-dark)]">
                <h2 class="text-lg font-semibold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">Ultimo entrenamiento</h2>

                @if ($trainingSummary)
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <div class="rounded-xl bg-[var(--color-surface-alt)] p-4 dark:bg-[var(--color-surface-dark-alt)]">
                            <p class="text-xs uppercase tracking-[0.16em] opacity-60">Accuracy</p>
                            <p class="mt-2 text-2xl font-bold">{{ number_format(((float) ($trainingSummary['accuracy'] ?? 0)) * 100, 2) }}%</p>
                        </div>
                        <div class="rounded-xl bg-[var(--color-surface-alt)] p-4 dark:bg-[var(--color-surface-dark-alt)]">
                            <p class="text-xs uppercase tracking-[0.16em] opacity-60">F1</p>
                            <p class="mt-2 text-2xl font-bold">{{ number_format(((float) ($trainingSummary['f1'] ?? 0)) * 100, 2) }}%</p>
                        </div>
                        <div class="rounded-xl bg-[var(--color-surface-alt)] p-4 dark:bg-[var(--color-surface-dark-alt)]">
                            <p class="text-xs uppercase tracking-[0.16em] opacity-60">Precision</p>
                            <p class="mt-2 text-xl font-bold">{{ number_format(((float) ($trainingSummary['precision'] ?? 0)) * 100, 2) }}%</p>
                        </div>
                        <div class="rounded-xl bg-[var(--color-surface-alt)] p-4 dark:bg-[var(--color-surface-dark-alt)]">
                            <p class="text-xs uppercase tracking-[0.16em] opacity-60">Recall</p>
                            <p class="mt-2 text-xl font-bold">{{ number_format(((float) ($trainingSummary['recall'] ?? 0)) * 100, 2) }}%</p>
                        </div>
                    </div>

                    <p class="mt-4 text-sm text-[var(--color-on-surface)] opacity-75 dark:text-[var(--color-on-surface-dark)]">
                        Registros usados: {{ $trainingSummary['total_registros'] ?? '--' }}. Version del modelo: {{ $trainingSummary['model_version'] ?? '--' }}.
                    </p>
                @else
                    <p class="mt-4 text-sm text-[var(--color-on-surface)] opacity-70 dark:text-[var(--color-on-surface-dark)]">
                    </p>
                @endif
            </div>

            @if ($error)
                <div class="rounded-[var(--radius-radius)] border border-rose-200 bg-rose-50 p-6 text-sm text-rose-800 shadow-sm">
                    <h2 class="font-semibold">Error de integracion</h2>
                    <p class="mt-2">{{ $error }}</p>
                </div>
            @endif
        </div>
    </div>

    @if($shapIndividual)
        @php
            $individualContributions = collect($shapIndividual['contributions'] ?? [])->take(10);
        @endphp
        <section class="card card-border bg-base-100">
            <div class="card-body">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="card-title">Explicación individual</h2>
                            <span class="badge badge-primary badge-soft">{{ $shapIndividual['codigo_serie'] }}</span>
                            <span class="badge {{ ($shapIndividual['risk_level'] ?? '') === 'alto' ? 'badge-error' : (($shapIndividual['risk_level'] ?? '') === 'medio' ? 'badge-warning' : 'badge-success') }}">
                                Riesgo {{ $shapIndividual['risk_level'] ?? '—' }}
                            </span>
                        </div>
                        <p class="mt-2 text-sm opacity-70">
                            {{ $shapIndividual['unidad_nombre'] ?? 'Sin unidad' }} ·
                            Probabilidad de inoperatividad:
                            <strong>{{ number_format(((float) ($shapIndividual['probability'] ?? 0)) * 100, 2) }}%</strong>
                        </p>
                    </div>
                    <button type="button" class="btn btn-sm btn-ghost" wire:click="cerrarExplicacionIndividual">
                        Cerrar
                    </button>
                </div>

                <div class="mt-4 grid gap-5 xl:grid-cols-[minmax(0,1.35fr)_minmax(360px,0.65fr)]">
                    <div>
                        @if($shapIndividual['waterfall_url'] ?? null)
                            <img
                                src="{{ $shapIndividual['waterfall_url'] }}"
                                alt="Gráfico SHAP waterfall"
                                class="w-full rounded-box bg-white object-contain"
                            />
                        @endif
                    </div>

                    <div>
                        <h3 class="font-semibold">Principales contribuciones</h3>
                        <div class="mt-3 overflow-x-auto">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Variable</th>
                                        <th>Valor</th>
                                        <th>Impacto</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($individualContributions as $contribution)
                                        <tr>
                                            <td>{{ $contribution['label'] }}</td>
                                            <td class="max-w-32 truncate" title="{{ $contribution['feature_value'] }}">
                                                {{ $contribution['feature_value'] }}
                                            </td>
                                            <td>
                                                <span class="badge badge-sm badge-soft {{ $contribution['direction'] === 'aumenta' ? 'badge-error' : 'badge-info' }}">
                                                    {{ $contribution['direction'] === 'aumenta' ? '+' : '−' }}
                                                    {{ number_format(abs((float) $contribution['shap_value']), 4) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="mt-3 rounded-box bg-base-200 p-4 text-sm">
                    <strong>Recomendación:</strong> {{ $shapIndividual['recommendation'] ?? '—' }}
                </div>
            </div>
        </section>
    @endif

    <script type="application/json" data-prediction-chart-data>@json($predictionChartData)</script>
</div>
