<div class="reposicion-shell space-y-6">
    @php
        $totalRecomendaciones = max(1, count($recomendaciones));
        $urgenciaConteo = collect($recomendaciones)->countBy('urgencia');
        $inmediata = (int) ($urgenciaConteo['inmediata'] ?? 0);
        $proxima = (int) ($urgenciaConteo['proxima'] ?? 0);
        $planificada = (int) ($urgenciaConteo['planificada'] ?? 0);
        $estable = (int) ($urgenciaConteo['estable'] ?? 0);

        $inmediataDeg = round(($inmediata / $totalRecomendaciones) * 360, 2);
        $proximaDeg = round(($proxima / $totalRecomendaciones) * 360, 2);
        $planificadaDeg = round(($planificada / $totalRecomendaciones) * 360, 2);

        $ahora = $inmediata;
        $pronto = $proxima;
        $luego = $planificada + $estable;
        $ahoraDeg = round(($ahora / $totalRecomendaciones) * 360, 2);
        $prontoDeg = round(($pronto / $totalRecomendaciones) * 360, 2);
        $articulosReposicion = collect($recomendaciones)
            ->sortByDesc(fn ($item) => (float) ($item['reposicion_esperada'] ?? 0))
            ->values();

        $reposicionChartData = [
            'urgencia' => [
                'labels' => ['Inmediata', 'Proxima', 'Planificada', 'Estable'],
                'series' => [$inmediata, $proxima, $planificada, $estable],
            ],
            'ventana' => [
                'labels' => ['Ahora', 'Pronto', 'Luego'],
                'series' => [$ahora, $pronto, $luego],
            ],
            'articulos' => [
                'labels' => $articulosReposicion
                    ->map(fn ($item) => trim(($item['articulo'] ?? 'Sin articulo').' · '.($item['unidad_nombre'] ?? 'Sin unidad')))
                    ->all(),
                'cantidad' => $articulosReposicion
                    ->map(fn ($item) => (int) ($item['cantidad_sugerida'] ?? 0))
                    ->all(),
                'reposicion' => $articulosReposicion
                    ->map(fn ($item) => round((float) ($item['reposicion_esperada'] ?? 0), 2))
                    ->all(),
            ],
        ];
    @endphp

    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div class="space-y-2">
            <h1 class="text-3xl font-bold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">
                Reposicion general de armamento
            </h1>
            <p class="text-sm opacity-70">
                Solicitud esperada calculada con Random Forest para reposición a {{ $horizonteDias }} días.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <x-form.header_button variant="export" type="button" wire:click="exportPdf">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 16l4-4m-4 4l-4-4m4 4V4m0 12v4m-7 0h14" />
                </svg>
                Exportar PDF
            </x-form.header_button>

            <x-form.header_button variant="neutral" href="{{ route('predicciones.index') }}" wire:navigate>
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Volver
            </x-form.header_button>

            <x-form.header_button variant="neutral" type="button" wire:click="actualizar">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Actualizar  
            </x-form.header_button>
        </div>
    </div>

    <x-form.toast_notification :message="session('success')" variant="success" />
    <x-form.toast_notification :message="session('error')" variant="danger" />

    <div class="card card-border bg-base-100">
        <div class="card-body gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="card-title">Alcance de reposición</h2>
                <p class="text-sm opacity-70">{{ $unidadSeleccionada }}</p>
            </div>
            <label class="block w-full max-w-md">
                <span class="mb-2 text-xs font-semibold uppercase tracking-wider opacity-60">Unidad</span>
                <select wire:model.live="unidad" class="select w-full">
                    @if(auth()->user()->isAdministradorGeneral())
                        <option value="">Todas las unidades</option>
                    @endif
                    @foreach($unidades as $unidadItem)
                        <option value="{{ $unidadItem->id }}">
                            {{ trim(($unidadItem->sigla ? $unidadItem->sigla.' - ' : '').$unidadItem->nombre) }}
                        </option>
                    @endforeach
                </select>
            </label>
        </div>
    </div>

    <div role="alert" class="alert alert-info alert-soft">
        <span>
            La cantidad sugerida es el valor esperado de reposición estimado por Random Forest; el estado del armamento se mantiene con lógica difusa.
        </span>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <div class="rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-[var(--color-surface)] p-6 shadow-sm dark:border-[var(--color-outline-dark)] dark:bg-[var(--color-surface-dark)]">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
                <div data-reposicion-chart="urgencia" class="min-h-[220px] w-full shrink-0 sm:w-[230px]" wire:ignore></div>

                <div class="flex-1">
                    <h2 class="text-lg font-semibold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">Distribucion de urgencia</h2>
                    <div class="mt-4 space-y-3 text-sm">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-rose-500"></span><span>Inmediata</span></div>
                            <span class="font-semibold">{{ $inmediata }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-amber-500"></span><span>Proxima</span></div>
                            <span class="font-semibold">{{ $proxima }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-sky-400"></span><span>Planificada</span></div>
                            <span class="font-semibold">{{ $planificada }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-emerald-500"></span><span>Estable</span></div>
                            <span class="font-semibold">{{ $estable }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-[var(--color-surface)] p-6 shadow-sm dark:border-[var(--color-outline-dark)] dark:bg-[var(--color-surface-dark)]">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
                <div data-reposicion-chart="ventana" class="min-h-[220px] w-full shrink-0 sm:w-[230px]" wire:ignore></div>

                <div class="flex-1">
                    <h2 class="text-lg font-semibold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">Cuando conviene pedir</h2>
                    <div class="mt-4 space-y-3 text-sm">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-rose-500"></span><span>Ahora</span></div>
                            <span class="font-semibold">{{ $ahora }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-amber-500"></span><span>Pronto</span></div>
                            <span class="font-semibold">{{ $pronto }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-emerald-500"></span><span>Luego</span></div>
                            <span class="font-semibold">{{ $luego }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-[var(--color-surface)] p-6 shadow-sm dark:border-[var(--color-outline-dark)] dark:bg-[var(--color-surface-dark)]">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">Reposición por artículo</h2>
                <p class="text-sm opacity-70">Incluye todos los artículos del alcance seleccionado, ordenados por reposición esperada.</p>
            </div>
            <span class="badge badge-info badge-soft">{{ count($recomendaciones) }} artículos</span>
        </div>
        <div class="mt-5 max-h-[42rem] overflow-y-auto pr-2">
            <div data-reposicion-chart="articulos" class="min-h-[360px] w-full" wire:ignore></div>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-[var(--color-surface)] p-5 shadow-sm dark:border-[var(--color-outline-dark)] dark:bg-[var(--color-surface-dark)]">
            <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-on-surface)] opacity-60 dark:text-[var(--color-on-surface-dark)]">Tipos evaluados</p>
            <p class="mt-3 text-3xl font-bold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">
                {{ $resumen['articulos_evaluados'] ?? 0 }}
            </p>
        </div>

        <div class="rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-[var(--color-surface)] p-5 shadow-sm dark:border-[var(--color-outline-dark)] dark:bg-[var(--color-surface-dark)]">
            <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-on-surface)] opacity-60 dark:text-[var(--color-on-surface-dark)]">Reposicion inmediata</p>
            <p class="mt-3 text-3xl font-bold text-rose-600">
                {{ $resumen['reposicion_inmediata'] ?? 0 }}
            </p>
            <p class="mt-2 text-sm text-[var(--color-on-surface)] opacity-75 dark:text-[var(--color-on-surface-dark)]">
                Articulos que conviene pedir ahora.
            </p>
        </div>

        <div class="rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-[var(--color-surface)] p-5 shadow-sm dark:border-[var(--color-outline-dark)] dark:bg-[var(--color-surface-dark)]">
            <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-on-surface)] opacity-60 dark:text-[var(--color-on-surface-dark)]">Pedir pronto</p>
            <p class="mt-3 text-3xl font-bold text-amber-600">
                {{ $resumen['reposicion_proxima'] ?? 0 }}
            </p>
            <p class="mt-2 text-sm text-[var(--color-on-surface)] opacity-75 dark:text-[var(--color-on-surface-dark)]">
                Articulos para la siguiente ventana.
            </p>
        </div>

        <div class="rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-[var(--color-surface)] p-5 shadow-sm dark:border-[var(--color-outline-dark)] dark:bg-[var(--color-surface-dark)]">
            <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-on-surface)] opacity-60 dark:text-[var(--color-on-surface-dark)]">Cuanto pedir</p>
            <p class="mt-3 text-3xl font-bold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">
                {{ $resumen['cantidad_sugerida_total'] ?? 0 }}
            </p>
            <p class="mt-2 text-sm text-[var(--color-on-surface)] opacity-75 dark:text-[var(--color-on-surface-dark)]">
                Total recomendado para reponer.
            </p>
        </div>
    </div>

    @if(auth()->user()->isAdministradorGeneral() && $unidad === '')
        <div class="card card-border bg-base-100">
            <div class="card-body">
                <h2 class="card-title">Reposición esperada por unidad</h2>
                <p class="text-sm opacity-70">
                    Las unidades sin historial aparecen como “sin datos”; no se les asigna una cantidad artificial.
                </p>
                <div class="mt-3 max-h-[34rem] overflow-auto">
                    <table class="table table-sm table-pin-rows">
                        <thead>
                            <tr>
                                <th>Unidad</th>
                                <th>Series</th>
                                <th>Con historial</th>
                                <th>Reposición esperada</th>
                                <th>Cantidad sugerida</th>
                                <th>Prioridad</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($resumenUnidades as $unidadResumen)
                                <tr>
                                    <td class="font-semibold">{{ $unidadResumen['unidad_nombre'] ?? 'Sin unidad' }}</td>
                                    <td>{{ $unidadResumen['total_series'] ?? 0 }}</td>
                                    <td>{{ number_format((float) ($unidadResumen['cobertura_historica_pct'] ?? 0), 1) }}%</td>
                                    <td>{{ number_format((float) ($unidadResumen['reposicion_esperada'] ?? 0), 2) }}</td>
                                    <td>{{ $unidadResumen['cantidad_sugerida'] ?? 0 }}</td>
                                    <td>
                                        <span class="badge badge-sm badge-soft {{ ($unidadResumen['urgencia'] ?? '') === 'sin_datos' ? 'badge-neutral' : 'badge-warning' }}">
                                            {{ ucfirst(str_replace('_', ' ', $unidadResumen['urgencia'] ?? 'sin_datos')) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <div class="rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-[var(--color-surface)] shadow-sm dark:border-[var(--color-outline-dark)] dark:bg-[var(--color-surface-dark)]">
        <div class="border-b border-[var(--color-outline)] px-6 py-5 dark:border-[var(--color-outline-dark)]">
            <h2 class="text-lg font-semibold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">Recomendación por unidad y artículo</h2>
            <p class="mt-1 text-sm text-[var(--color-on-surface)] opacity-70 dark:text-[var(--color-on-surface-dark)]">
                Resumen corto para decidir que armamento conviene reponer primero.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="table table-sm">
                <thead class="bg-[var(--color-surface-alt)] dark:bg-[var(--color-surface-dark-alt)]">
                    <tr>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">Articulo</th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">Unidad</th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">Estado</th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">Urgencia</th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">Cuando pedir</th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">Cuanto pedir</th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">Resumen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--color-outline)] dark:divide-[var(--color-outline-dark)] kiro-stagger">
                    @forelse ($recomendaciones as $item)
                        @php
                            $urgenciaClasses = match ($item['urgencia']) {
                                'inmediata' => 'bg-rose-100 text-rose-700',
                                'proxima' => 'bg-amber-100 text-amber-700',
                                'planificada' => 'bg-sky-100 text-sky-700',
                                'sin_datos' => 'bg-base-300 text-base-content',
                                default => 'bg-emerald-100 text-emerald-700',
                            };
                            $cuandoTexto = match ($item['urgencia']) {
                                'inmediata' => 'Ahora a 30 dias',
                                'proxima' => '30 a 60 dias',
                                'planificada' => '60 a 90 dias',
                                'sin_datos' => 'Pendiente de historial',
                                default => 'Mas de 90 dias',
                            };
                        @endphp
                        <tr class="align-top hover:bg-[var(--color-surface-alt)] dark:hover:bg-[var(--color-surface-dark-alt)]">
                            <td class="px-6 py-4">
                                <p class="font-semibold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">
                                    {{ $item['articulo'] }}
                                </p>
                                <p class="mt-1 text-xs text-[var(--color-on-surface)] opacity-60 dark:text-[var(--color-on-surface-dark)]">
                                    {{ $item['categoria'] ?? 'Sin categoria' }}
                                </p>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                {{ $item['unidad_nombre'] ?? 'Sin unidad' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">
                                <p class="font-semibold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">
                                    @if (($item['urgencia'] ?? '') === 'inmediata')
                                        Estado critico
                                    @elseif (($item['urgencia'] ?? '') === 'proxima')
                                        Requiere atencion
                                    @elseif (($item['urgencia'] ?? '') === 'planificada')
                                        Seguimiento preventivo
                                    @elseif (($item['urgencia'] ?? '') === 'sin_datos')
                                        Predicción no disponible
                                    @else
                                        Estado estable
                                    @endif
                                </p>
                                <p class="mt-1">Total: {{ $item['total_series'] }}</p>
                                <p>{{ $item['futuro_inoperativo'] ?? 0 }} futuras inoperativas</p>
                                <p>{{ ($item['futuro_con_defectos'] ?? 0) + ($item['futuro_malo'] ?? 0) }} con deterioro futuro</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $urgenciaClasses }}">
                                    {{ ucfirst($item['urgencia']) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">
                                <p class="font-semibold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">{{ $cuandoTexto }}</p>
                                @if($item['fecha_sugerida_desde'] ?? null)
                                    <p class="mt-1">Desde {{ $item['fecha_sugerida_desde'] }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm font-semibold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">
                                {{ $item['cantidad_sugerida'] }}
                            </td>
                            <td class="px-6 py-4 text-sm text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">
                                <p>{{ $item['motivo'] ?? 'Sin detalle.' }}</p>
                                <p class="mt-1">Confianza media: {{ number_format(((float) ($item['confianza_futura_promedio'] ?? 0)) * 100, 1) }}%.</p>
                                <p class="mt-1">Cobertura histórica: {{ number_format((float) ($item['cobertura_historica_pct'] ?? 0), 1) }}%.</p>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <p class="font-medium text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">
                                    No hay armamento serializado suficiente para calcular reposicion.
                                </p>
                                <p class="mt-1 text-sm text-[var(--color-on-surface)] opacity-60 dark:text-[var(--color-on-surface-dark)]">
                                    El calculo requiere articulos reutilizables con seguimiento por serie.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script type="application/json" data-reposicion-chart-data>@json($reposicionChartData)</script>
</div>
