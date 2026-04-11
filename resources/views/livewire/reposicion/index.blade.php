<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div class="space-y-2">
            <h1 class="text-3xl font-bold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">
                Reposicion general de armamento
            </h1>
        </div>

        <div class="flex flex-wrap items-center gap-3">
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

    <div class="rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-[var(--color-surface)] shadow-sm dark:border-[var(--color-outline-dark)] dark:bg-[var(--color-surface-dark)]">
        <div class="border-b border-[var(--color-outline)] px-6 py-5 dark:border-[var(--color-outline-dark)]">
            <h2 class="text-lg font-semibold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">Recomendacion por articulo</h2>
            <p class="mt-1 text-sm text-[var(--color-on-surface)] opacity-70 dark:text-[var(--color-on-surface-dark)]">
                Resumen corto para decidir que armamento conviene reponer primero.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-[var(--color-surface-alt)] dark:bg-[var(--color-surface-dark-alt)]">
                    <tr>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">Articulo</th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">Estado</th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">Urgencia</th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">Cuando pedir</th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">Cuanto pedir</th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">Resumen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--color-outline)] dark:divide-[var(--color-outline-dark)]">
                    @forelse ($recomendaciones as $item)
                        @php
                            $urgenciaClasses = match ($item['urgencia']) {
                                'inmediata' => 'bg-rose-100 text-rose-700',
                                'proxima' => 'bg-amber-100 text-amber-700',
                                'planificada' => 'bg-sky-100 text-sky-700',
                                default => 'bg-emerald-100 text-emerald-700',
                            };
                            $cuandoTexto = match ($item['urgencia']) {
                                'inmediata' => 'Ahora a 30 dias',
                                'proxima' => '30 a 60 dias',
                                'planificada' => '60 a 90 dias',
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
                            <td class="px-6 py-4 text-sm text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">
                                <p class="font-semibold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">
                                    {{ number_format((float) $item['salud_operativa'], 1) }}% operativo
                                </p>
                                <p class="mt-1">Total: {{ $item['total_series'] }}</p>
                                <p>{{ $item['inoperativas'] }} inoperativas</p>
                                <p>{{ $item['observadas'] }} observadas</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $urgenciaClasses }}">
                                    {{ ucfirst($item['urgencia']) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">
                                <p class="font-semibold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">{{ $cuandoTexto }}</p>
                                <p class="mt-1">Desde {{ $item['fecha_sugerida_desde'] }}</p>
                            </td>
                            <td class="px-6 py-4 text-sm font-semibold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">
                                {{ $item['cantidad_sugerida'] }}
                            </td>
                            <td class="px-6 py-4 text-sm text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">
                                <p>{{ $item['inoperativas'] }} inoperativas, {{ $item['observadas'] }} con desgaste.</p>
                                <p class="mt-1">{{ $item['incidencias_90d'] }} incidencias en 90 dias.</p>
                                @if ($item['mantenimientos_abiertos'] > 0)
                                    <p class="mt-1">{{ $item['mantenimientos_abiertos'] }} en mantenimiento abierto.</p>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
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
</div>
