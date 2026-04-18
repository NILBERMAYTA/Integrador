<div class="space-y-6">
    @php
        $totalPredicciones = max(1, count($predicciones));
        $alto = (int) ($stats['alto'] ?? 0);
        $medio = (int) ($stats['medio'] ?? 0);
        $bajo = (int) ($stats['bajo'] ?? 0);
        $inoperativoCount = collect($predicciones)->where('estado_predicho', 'inoperativo')->count();
        $operativoCount = max(0, count($predicciones) - $inoperativoCount);

        $altoDeg = round(($alto / $totalPredicciones) * 360, 2);
        $medioDeg = round(($medio / $totalPredicciones) * 360, 2);
        $inoperativoDeg = round(($inoperativoCount / $totalPredicciones) * 360, 2);
    @endphp

    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div class="space-y-2">
            <h1 class="text-3xl font-bold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">
                Predicciones de armamento
            </h1>
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
                <div
                    class="relative h-36 w-36 shrink-0 rounded-full"
                    style="background: conic-gradient(#f43f5e 0deg {{ $altoDeg }}deg, #f59e0b {{ $altoDeg }}deg {{ $altoDeg + $medioDeg }}deg, #10b981 {{ $altoDeg + $medioDeg }}deg 360deg);"
                >
                    <div class="absolute inset-5 rounded-full bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)]"></div>
                    <div class="absolute inset-0 flex items-center justify-center text-center">
                        <div>
                            <p class="text-xs uppercase tracking-[0.18em] opacity-60">Riesgo</p>
                            <p class="text-2xl font-bold">{{ count($predicciones) }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex-1">
                    <h2 class="text-lg font-semibold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">Distribucion de riesgo</h2>
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
                <div
                    class="relative h-36 w-36 shrink-0 rounded-full"
                    style="background: conic-gradient(#f43f5e 0deg {{ $inoperativoDeg }}deg, #10b981 {{ $inoperativoDeg }}deg 360deg);"
                >
                    <div class="absolute inset-5 rounded-full bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)]"></div>
                    <div class="absolute inset-0 flex items-center justify-center text-center">
                        <div>
                            <p class="text-xs uppercase tracking-[0.18em] opacity-60">Estado</p>
                            <p class="text-2xl font-bold">{{ count($predicciones) }}</p>
                        </div>
                    </div>
                </div>

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
                Limite activo: {{ $limit }}
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

    <div class="grid gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(320px,1fr)]">
        <div class="rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-[var(--color-surface)] shadow-sm dark:border-[var(--color-outline-dark)] dark:bg-[var(--color-surface-dark)]">
            <div class="flex flex-col gap-4 border-b border-[var(--color-outline)] px-6 py-5 dark:border-[var(--color-outline-dark)] lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">Predicciones recientes</h2>
                    <p class="mt-1 text-sm text-[var(--color-on-surface)] opacity-70 dark:text-[var(--color-on-surface-dark)]">
                    </p>
                </div>

                <div class="w-full max-w-[180px]">
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-on-surface)] opacity-60 dark:text-[var(--color-on-surface-dark)]">
                        Registros
                    </label>
                    <select
                        wire:model.live="limit"
                        class="w-full rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-[var(--color-surface)] px-3 py-2 text-sm text-[var(--color-on-surface)] dark:border-[var(--color-outline-dark)] dark:bg-[var(--color-surface-dark)] dark:text-[var(--color-on-surface-dark)]"
                    >
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-[var(--color-surface-alt)] dark:bg-[var(--color-surface-dark-alt)]">
                        <tr>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">Serie</th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">Unidad</th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">Estado</th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">Probabilidad</th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">Riesgo</th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">Recomendacion</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--color-outline)] dark:divide-[var(--color-outline-dark)]">
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
                                    {{ $prediccion['unidad_id'] ?? '--' }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ ($prediccion['estado_predicho'] ?? '') === 'inoperativo' ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }}">
                                        {{ ucfirst($prediccion['estado_predicho'] ?? '--') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm font-semibold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">
                                    {{ isset($prediccion['probabilidad']) ? number_format((float) $prediccion['probabilidad'] * 100, 2) . '%' : '--' }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $riesgoClasses }}">
                                        {{ ucfirst($riesgo) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">
                                    {{ $prediccion['recomendacion'] ?? '--' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
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
</div>
