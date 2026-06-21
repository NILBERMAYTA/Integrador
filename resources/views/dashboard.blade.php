<x-layouts.app :title="__('Panel operativo')">
    @php
        $totalPrestamos = $prestamosActivos + $prestamosConcluidos;
        $tasaCierre = $totalPrestamos > 0 ? round(($prestamosConcluidos / $totalPrestamos) * 100) : 0;
        $alertasCriticas = $seriesInoperativas + $consumiblesAgotados;
        $alertasPreventivas = $seriesMantenimiento + $consumiblesBajoStock + $devolucionesPendientes;
        $unidadNombre = auth()->user()?->isAdministradorGeneral()
            ? 'Vista institucional'
            : (auth()->user()?->unidadActual?->nombre ?? 'Unidad asignada');

        $dashboardData = [
            'trend' => [
                'monthly' => [
                    'title' => 'Evolución de los últimos 6 meses',
                    'categories' => collect($prestamosTendencia)->pluck('label')->map(fn ($label) => mb_strtoupper($label))->values(),
                    'series' => collect($prestamosTendencia)->pluck('total')->values(),
                ],
                'weekly' => [
                    'title' => 'Actividad de la semana actual',
                    'categories' => collect($prestamosSemana)->pluck('label')->values(),
                    'series' => collect($prestamosSemana)->pluck('total')->values(),
                ],
            ],
            'condition' => [
                'labels' => collect($condicionArmamento)->pluck('label')->values(),
                'series' => collect($condicionArmamento)->pluck('total')->values(),
            ],
            'inventory' => [
                'categories' => collect($categorias)->map(fn ($item) => $item->categoria->nombre ?? 'Sin categoría')->values(),
                'series' => collect($categorias)->pluck('total')->values(),
            ],
            'seriesStatus' => [
                'labels' => ['Disponibles', 'Asignadas', 'Mantenimiento', 'Inoperativas'],
                'series' => [$seriesDisponibles, $seriesAsignadas, $seriesMantenimiento, $seriesInoperativas],
            ],
        ];
    @endphp

    <div class="dashboard-shell space-y-5">
        <section class="relative overflow-hidden rounded-[24px] border border-[var(--color-outline)] bg-[var(--color-surface)] shadow-sm dark:border-[var(--color-outline-dark)] dark:bg-[var(--color-surface-dark)]">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,color-mix(in_oklab,var(--color-primary)_24%,transparent),transparent_42%),radial-gradient(circle_at_bottom_left,color-mix(in_oklab,var(--color-secondary)_18%,transparent),transparent_38%)]"></div>
            <div class="absolute right-0 top-0 hidden h-full w-[42%] opacity-[0.08] lg:block">
                <svg viewBox="0 0 500 220" class="h-full w-full" fill="none" aria-hidden="true">
                    <path d="M40 180C105 95 155 145 218 75C284 2 340 102 468 30" stroke="currentColor" stroke-width="3"/>
                    <path d="M40 205C115 125 174 188 244 112C315 36 372 138 485 68" stroke="currentColor" stroke-width="2"/>
                    <circle cx="218" cy="75" r="8" fill="currentColor"/>
                    <circle cx="468" cy="30" r="8" fill="currentColor"/>
                </svg>
            </div>

            <div class="relative flex flex-col gap-6 px-5 py-6 sm:px-7 lg:flex-row lg:items-center lg:justify-between lg:px-8 lg:py-7">
                <div>
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-2 rounded-full border border-[var(--color-success)]/30 bg-[var(--color-success)]/10 px-3 py-1 text-xs font-semibold text-[var(--color-success)]">
                            <span class="size-2 rounded-full bg-[var(--color-success)] shadow-[0_0_0_4px_color-mix(in_oklab,var(--color-success)_14%,transparent)]"></span>
                            Operación en línea
                        </span>
                        <span class="rounded-full border border-[var(--color-outline)] bg-[var(--color-surface)]/70 px-3 py-1 text-xs text-[var(--color-on-surface)]/70 dark:border-[var(--color-outline-dark)] dark:bg-[var(--color-surface-dark)]/70 dark:text-[var(--color-on-surface-dark)]/70">
                            {{ $unidadNombre }}
                        </span>
                    </div>
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-[var(--color-primary)] dark:text-[var(--color-primary-dark)]">ARMUTOP · Control institucional</p>
                    <h1 class="mt-2 text-2xl font-bold tracking-tight text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)] sm:text-3xl">
                        Panorama operativo
                    </h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-[var(--color-on-surface)]/70 dark:text-[var(--color-on-surface-dark)]/70">
                        Estado consolidado de préstamos, personal, armamento e inventario para facilitar decisiones rápidas.
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('articulos.inventario') }}" wire:navigate class="inline-flex items-center gap-2 rounded-xl border border-[var(--color-outline)] bg-[var(--color-surface)]/80 px-4 py-2.5 text-sm font-semibold text-[var(--color-on-surface-strong)] transition hover:-translate-y-0.5 hover:bg-[var(--color-surface-alt)] dark:border-[var(--color-outline-dark)] dark:bg-[var(--color-surface-dark)]/80 dark:text-[var(--color-on-surface-dark-strong)] dark:hover:bg-[var(--color-surface-dark-alt)]">
                        <flux:icon.archive-box class="size-4" />
                        Ver inventario
                    </a>
                    <a href="{{ route('prestamos.create') }}" wire:navigate class="inline-flex items-center gap-2 rounded-xl bg-[var(--color-primary)] px-4 py-2.5 text-sm font-semibold text-[var(--color-on-primary)] shadow-lg shadow-[var(--color-primary)]/20 transition hover:-translate-y-0.5 hover:brightness-110 dark:bg-[var(--color-primary-dark)] dark:text-[var(--color-on-primary-dark)]">
                        <flux:icon.plus class="size-4" />
                        Nuevo préstamo
                    </a>
                </div>
            </div>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4 kiro-stagger">
            @php
                $kpis = [
                    [
                        'label' => 'Préstamos activos',
                        'value' => $prestamosActivos,
                        'detail' => "{$prestamosConcluidos} concluidos",
                        'icon' => 'arrows-right-left',
                        'glow' => 'bg-sky-500/10 group-hover:bg-sky-500/20',
                        'iconClass' => 'bg-sky-500/12 text-sky-600 ring-sky-500/20 dark:text-sky-400',
                        'barClass' => 'bg-sky-500',
                        'progress' => $totalPrestamos > 0 ? round(($prestamosActivos / $totalPrestamos) * 100) : 0,
                    ],
                    [
                        'label' => 'Tasa de cierre',
                        'value' => $tasaCierre.'%',
                        'detail' => "{$totalPrestamos} operaciones evaluadas",
                        'icon' => 'check-circle',
                        'glow' => 'bg-emerald-500/10 group-hover:bg-emerald-500/20',
                        'iconClass' => 'bg-emerald-500/12 text-emerald-600 ring-emerald-500/20 dark:text-emerald-400',
                        'barClass' => 'bg-emerald-500',
                        'progress' => $tasaCierre,
                    ],
                    [
                        'label' => 'Series disponibles',
                        'value' => $seriesDisponibles,
                        'detail' => "{$seriesAsignadas} actualmente asignadas",
                        'icon' => 'shield-check',
                        'glow' => 'bg-violet-500/10 group-hover:bg-violet-500/20',
                        'iconClass' => 'bg-violet-500/12 text-violet-600 ring-violet-500/20 dark:text-violet-400',
                        'barClass' => 'bg-violet-500',
                        'progress' => ($seriesDisponibles + $seriesAsignadas) > 0 ? round(($seriesDisponibles / ($seriesDisponibles + $seriesAsignadas)) * 100) : 0,
                    ],
                    [
                        'label' => 'Alertas críticas',
                        'value' => $alertasCriticas,
                        'detail' => "{$alertasPreventivas} preventivas",
                        'icon' => 'exclamation-triangle',
                        'glow' => 'bg-amber-500/10 group-hover:bg-amber-500/20',
                        'iconClass' => 'bg-amber-500/12 text-amber-600 ring-amber-500/20 dark:text-amber-400',
                        'barClass' => 'bg-amber-500',
                        'progress' => min(100, $alertasCriticas * 10),
                    ],
                ];
            @endphp

            @foreach($kpis as $kpi)
                <article class="dashboard-card group relative overflow-hidden p-5">
                    <div class="absolute -right-8 -top-8 size-28 rounded-full blur-2xl transition {{ $kpi['glow'] }}"></div>
                    <div class="relative">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[var(--color-on-surface)]/60 dark:text-[var(--color-on-surface-dark)]/60">{{ $kpi['label'] }}</p>
                                <p class="mt-2 text-3xl font-bold tracking-tight text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">{{ $kpi['value'] }}</p>
                            </div>
                            <div class="flex size-11 items-center justify-center rounded-2xl ring-1 {{ $kpi['iconClass'] }}">
                                <flux:icon :name="$kpi['icon']" class="size-5" />
                            </div>
                        </div>
                        <div class="mt-5 h-1.5 overflow-hidden rounded-full bg-[var(--color-surface-alt)] dark:bg-[var(--color-surface-dark-alt)]">
                            <div class="h-full rounded-full {{ $kpi['barClass'] }}" style="width: {{ $kpi['progress'] }}%"></div>
                        </div>
                        <p class="mt-2 text-xs text-[var(--color-on-surface)]/65 dark:text-[var(--color-on-surface-dark)]/65">{{ $kpi['detail'] }}</p>
                    </div>
                </article>
            @endforeach
        </section>

        <section class="grid gap-5 xl:grid-cols-12">
            <article class="dashboard-card xl:col-span-8">
                <header class="dashboard-card-header">
                    <div>
                        <p class="dashboard-eyebrow">Actividad de préstamos</p>
                        <h2 class="dashboard-title" data-trend-title>Actividad de la semana actual</h2>
                    </div>
                    <div class="flex flex-col items-end gap-3">
                        <div class="inline-flex rounded-xl border border-[var(--color-outline)] bg-[var(--color-surface-alt)] p-1 dark:border-[var(--color-outline-dark)] dark:bg-[var(--color-surface-dark-alt)]" role="group" aria-label="Periodo del gráfico">
                            <button type="button" data-trend-period="monthly" aria-pressed="false" class="rounded-lg px-3 py-1.5 text-xs font-semibold text-[var(--color-on-surface)]/65 transition hover:text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark)]/65 dark:hover:text-[var(--color-on-surface-dark-strong)]">
                                6 meses
                            </button>
                            <button type="button" data-trend-period="weekly" aria-pressed="true" class="rounded-lg bg-[var(--color-surface)] px-3 py-1.5 text-xs font-semibold text-[var(--color-on-surface-strong)] shadow-sm transition dark:bg-[var(--color-surface-dark)] dark:text-[var(--color-on-surface-dark-strong)]">
                                Semana
                            </button>
                        </div>
                        <div class="text-right">
                            <p data-trend-total class="text-2xl font-bold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">{{ collect($prestamosSemana)->sum('total') }}</p>
                            <p class="text-xs text-[var(--color-on-surface)]/60 dark:text-[var(--color-on-surface-dark)]/60">operaciones registradas</p>
                        </div>
                    </div>
                </header>
                <div class="px-2 pb-3 sm:px-4">
                    <div data-dashboard-chart="trend" class="min-h-[315px]"></div>
                </div>
            </article>

            <article class="dashboard-card xl:col-span-4">
                <header class="dashboard-card-header">
                    <div>
                        <p class="dashboard-eyebrow">Condición técnica</p>
                        <h2 class="dashboard-title">Estado del armamento</h2>
                    </div>
                    <span class="rounded-full bg-[var(--color-surface-alt)] px-3 py-1 text-xs font-semibold dark:bg-[var(--color-surface-dark-alt)]">{{ $totalArmamento }} series</span>
                </header>
                <div data-dashboard-chart="condition" class="min-h-[255px] px-2"></div>
                <div class="grid grid-cols-2 gap-2 px-5 pb-5">
                    @foreach($condicionArmamento as $index => $item)
                        @php
                            $dotColors = ['bg-emerald-500', 'bg-amber-500', 'bg-orange-500', 'bg-rose-500'];
                        @endphp
                        <div class="rounded-xl bg-[var(--color-surface-alt)]/70 px-3 py-2 dark:bg-[var(--color-surface-dark-alt)]/70">
                            <div class="flex items-center gap-2 text-xs text-[var(--color-on-surface)]/65 dark:text-[var(--color-on-surface-dark)]/65">
                                <span class="size-2 rounded-full {{ $dotColors[$index] }}"></span>
                                {{ $item['label'] }}
                            </div>
                            <p class="mt-1 text-lg font-bold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">{{ $item['total'] }}</p>
                        </div>
                    @endforeach
                </div>
            </article>
        </section>

        <section class="grid gap-5 xl:grid-cols-12">
            <article class="dashboard-card xl:col-span-7">
                <header class="dashboard-card-header">
                    <div>
                        <p class="dashboard-eyebrow">Composición del inventario</p>
                        <h2 class="dashboard-title">Artículos por categoría</h2>
                    </div>
                    <span class="rounded-full border border-[var(--color-outline)] px-3 py-1 text-xs font-semibold dark:border-[var(--color-outline-dark)]">{{ $totalInventario }} tipos</span>
                </header>
                @if($categorias->isEmpty())
                    <div class="dashboard-empty">No hay categorías con artículos registrados.</div>
                @else
                    <div data-dashboard-chart="inventory" class="min-h-[330px] px-2 pb-3 sm:px-4"></div>
                @endif
            </article>

            <article class="dashboard-card xl:col-span-5">
                <header class="dashboard-card-header">
                    <div>
                        <p class="dashboard-eyebrow">Trazabilidad</p>
                        <h2 class="dashboard-title">Movimientos recientes</h2>
                    </div>
                    <a href="{{ route('prestamos.index') }}" wire:navigate class="text-xs font-semibold text-[var(--color-primary)] hover:underline dark:text-[var(--color-primary-dark)]">Ver todos</a>
                </header>

                <div class="space-y-1 px-3 pb-4 kiro-stagger">
                    @forelse($prestamosRecientes->take(5) as $row)
                        <a href="{{ route('prestamos.index') }}" wire:navigate class="group flex items-center gap-3 rounded-2xl px-3 py-3 transition hover:bg-[var(--color-surface-alt)] dark:hover:bg-[var(--color-surface-dark-alt)]">
                            <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-[var(--color-primary)]/10 text-xs font-bold text-[var(--color-primary)] dark:bg-[var(--color-primary-dark)]/10 dark:text-[var(--color-primary-dark)]">
                                {{ \Illuminate\Support\Str::of($row['policia'])->explode(' ')->filter()->map(fn ($part) => \Illuminate\Support\Str::substr($part, 0, 1))->take(2)->implode('') }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="truncate text-sm font-semibold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">{{ $row['policia'] }}</p>
                                    <span class="shrink-0 text-[11px] text-[var(--color-on-surface)]/50 dark:text-[var(--color-on-surface-dark)]/50">{{ $row['fecha'] }}</span>
                                </div>
                                <p class="mt-0.5 truncate text-xs text-[var(--color-on-surface)]/65 dark:text-[var(--color-on-surface-dark)]/65">
                                    {{ $row['articulo'] }}{{ $row['serie'] ? ' · '.$row['serie'] : '' }}
                                </p>
                            </div>
                            <span @class([
                                'size-2.5 shrink-0 rounded-full',
                                'bg-amber-500 shadow-[0_0_0_4px_rgba(245,158,11,.12)]' => $row['estado'] === 'pendiente',
                                'bg-emerald-500 shadow-[0_0_0_4px_rgba(16,185,129,.12)]' => $row['estado'] !== 'pendiente',
                            ]) title="{{ ucfirst($row['estado']) }}"></span>
                        </a>
                    @empty
                        <div class="dashboard-empty">Todavía no hay movimientos registrados.</div>
                    @endforelse
                </div>
            </article>
        </section>

        <section class="grid items-start gap-5 xl:grid-cols-12">
            <article class="dashboard-card xl:col-span-3">
                <header class="dashboard-card-header">
                    <div>
                        <p class="dashboard-eyebrow">Disponibilidad</p>
                        <h2 class="dashboard-title">Situación de las series</h2>
                    </div>
                </header>
                <div data-dashboard-chart="series-status" class="min-h-[245px] px-2"></div>
            </article>

            <article class="dashboard-card xl:col-span-4">
                <header class="dashboard-card-header">
                    <div>
                        <p class="dashboard-eyebrow">Atención requerida</p>
                        <h2 class="dashboard-title">Alertas operativas</h2>
                    </div>
                    <span class="flex size-8 items-center justify-center rounded-full bg-amber-500/10 text-sm font-bold text-amber-600 dark:text-amber-400">{{ $alertasCriticas + $alertasPreventivas }}</span>
                </header>
                <div class="space-y-3 px-5 pb-5 kiro-stagger">
                    @php
                        $alerts = [
                            ['value' => $devolucionesPendientes, 'label' => 'Devoluciones pendientes', 'detail' => 'Operaciones que requieren cierre', 'class' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400'],
                            ['value' => $seriesMantenimiento, 'label' => 'En mantenimiento', 'detail' => 'Series fuera de disponibilidad', 'class' => 'bg-sky-500/10 text-sky-600 dark:text-sky-400'],
                            ['value' => $seriesInoperativas, 'label' => 'Series inoperativas', 'detail' => 'Requieren evaluación o baja', 'class' => 'bg-rose-500/10 text-rose-600 dark:text-rose-400'],
                            ['value' => $consumiblesBajoStock + $consumiblesAgotados, 'label' => 'Alertas de consumibles', 'detail' => "{$consumiblesBajoStock} bajos · {$consumiblesAgotados} agotados", 'class' => 'bg-violet-500/10 text-violet-600 dark:text-violet-400'],
                        ];
                    @endphp
                    @foreach($alerts as $alert)
                        <div class="flex items-center gap-3 rounded-2xl border border-[var(--color-outline)]/70 p-3 dark:border-[var(--color-outline-dark)]/70">
                            <div class="flex size-10 shrink-0 items-center justify-center rounded-xl text-sm font-bold {{ $alert['class'] }}">
                                {{ $alert['value'] }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">{{ $alert['label'] }}</p>
                                <p class="truncate text-xs text-[var(--color-on-surface)]/60 dark:text-[var(--color-on-surface-dark)]/60">{{ $alert['detail'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="dashboard-card xl:col-span-5">
                <header class="dashboard-card-header">
                    <div>
                        <p class="dashboard-eyebrow">Apoyo a decisiones</p>
                        <h2 class="dashboard-title">Recomendaciones</h2>
                    </div>
                    <div class="flex size-9 items-center justify-center rounded-xl bg-[var(--color-primary)]/10 text-[var(--color-primary)] dark:bg-[var(--color-primary-dark)]/10 dark:text-[var(--color-primary-dark)]">
                        <flux:icon.sparkles class="size-4" />
                    </div>
                </header>
                <div class="px-5 pb-5">
                    @livewire('recomendacion.recomendador')
                </div>
            </article>
        </section>
    </div>

    <script type="application/json" data-dashboard-data>@json($dashboardData)</script>
</x-layouts.app>
