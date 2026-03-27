<x-layouts.app :title="__('Inicio')">
    <div class="flex flex-col gap-6">
        {{-- Encabezado con CTA --}}
        <div class="relative overflow-hidden rounded-[20px] border border-[var(--color-outline)] bg-gradient-to-r from-[var(--color-primary)]/12 via-[var(--color-primary)]/6 to-[var(--color-secondary)]/12 shadow-sm">
            <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-[var(--color-primary)]/10 blur-3xl"></div>
            <div class="absolute -left-8 bottom-0 h-28 w-28 rounded-full bg-[var(--color-secondary)]/10 blur-3xl"></div>
            <div class="relative px-6 py-5 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="space-y-2">
                    <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-on-surface)]/70">Panel operativo</p>
                    <h1 class="text-3xl font-bold text-[var(--color-on-surface-strong)]">Resumen</h1>
                    <p class="text-sm text-[var(--color-on-surface)]/75">Prestamos, personal y devoluciones.</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2 rounded-full bg-[var(--color-surface)]/70 px-3 py-2 text-xs font-semibold border border-[var(--color-outline)]">
                        <span class="w-2 h-2 rounded-full bg-[var(--color-success)] animate-pulse"></span>
                        Sistema en linea
                    </div>
                    <a href="{{ route('prestamos.create') }}" class="inline-flex items-center gap-2 rounded-[12px] bg-[var(--color-primary)] text-[var(--color-on-primary)] px-4 py-2 text-sm font-semibold shadow-sm hover:opacity-90 transition">
                        Nuevo prestamo
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        {{-- KPIs principales --}}
        <div class="grid gap-4 lg:grid-cols-3">
            @php
                $cards = [
                    ['title' => 'Prestamos activos', 'value' => $prestamosActivos, 'subtitle' => 'Armamento asignado', 'tone' => 'from-cyan-400 to-blue-500', 'icon' => 'chart-bar'],
                    ['title' => 'Personal activo', 'value' => $personalActivo, 'subtitle' => 'Oficiales registrados', 'tone' => 'from-emerald-400 to-teal-500', 'icon' => 'user-group'],
                    ['title' => 'Devoluciones pendientes', 'value' => $devolucionesPendientes, 'subtitle' => 'Por cerrar', 'tone' => 'from-amber-400 to-orange-500', 'icon' => 'arrow-path'],
                ];
            @endphp
            @foreach($cards as $card)
                <div class="relative overflow-hidden rounded-[20px] border border-[var(--color-outline)] bg-[var(--color-surface)] shadow-sm">
                    <div class="absolute inset-0 opacity-10 bg-gradient-to-br {{ $card['tone'] }}"></div>
                    <div class="relative p-5 flex items-start justify-between gap-3">
                        <div class="space-y-1">
                            <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-on-surface)]/70">{{ $card['title'] }}</p>
                            <div class="flex items-baseline gap-2">
                                <h3 class="text-3xl font-bold text-[var(--color-on-surface-strong)]">{{ $card['value'] }}</h3>
                            </div>
                            <p class="text-sm text-[var(--color-on-surface)]/70">{{ $card['subtitle'] }}</p>
                        </div>
                        <div class="flex h-11 w-11 items-center justify-center rounded-[14px] bg-[var(--color-surface-alt)] border border-[var(--color-outline)] text-lg text-[var(--color-on-surface-strong)]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                @if($card['icon'] === 'chart-bar')
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 19.5h18M6.75 15V9.75m4.5 5.25V4.5m4.5 10.5v-7.5" />
                                @elseif($card['icon'] === 'user-group')
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 20.25c0-2.071-1.679-3.75-3.75-3.75h-4.5C7.679 16.5 6 18.179 6 20.25m12 0V21a.75.75 0 01-.75.75H6.75A.75.75 0 016 21v-.75m12 0c0-2.9-2.35-5.25-5.25-5.25h-1.5A5.25 5.25 0 006 20.25M15 7.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM19.5 8.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                @else
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.5 12a7.5 7.5 0 0112.728-5.303L21 10.5M4.5 12a7.5 7.5 0 0012.728 5.303L21 13.5" />
                                @endif
                            </svg>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="grid items-start gap-5 xl:grid-cols-3">
            {{-- Balance operativo --}}
            <div class="xl:col-span-2 self-start h-fit rounded-[20px] border border-[var(--color-outline)] bg-[var(--color-surface)] shadow-sm">
                <div class="p-5 flex items-center justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-on-surface)]/70">Situacion del material</p>
                        <h2 class="text-xl font-semibold text-[var(--color-on-surface-strong)]">Condicion actual del armamento</h2>
                    </div>
                    <div class="rounded-full bg-[var(--color-surface-alt)] px-3 py-2 text-xs font-semibold border border-[var(--color-outline)] text-[var(--color-on-surface)]/75">
                        Total evaluado: {{ $totalArmamento }}
                    </div>
                </div>
                <div class="px-5 pb-5">
                    @if($condicionArmamento->sum('total') === 0)
                        <div class="h-48 w-full rounded-[14px] border border-dashed border-[var(--color-outline)] flex items-center justify-center text-sm text-[var(--color-on-surface)]/60">
                            Sin armamento serializado para graficar.
                        </div>
                    @else
                        <div class="rounded-[14px] bg-gradient-to-br from-[var(--color-primary)]/12 to-[var(--color-secondary)]/10 p-4">
                            <div class="space-y-4">
                                @foreach($condicionArmamento as $item)
                                    @php
                                        $width = $condicionMax > 0 ? max(6, round(($item['total'] / $condicionMax) * 100)) : 0;
                                        $share = $totalArmamento > 0 ? round(($item['total'] / $totalArmamento) * 100) : 0;
                                    @endphp
                                    <div class="grid gap-2 sm:grid-cols-[140px_1fr_56px] sm:items-center">
                                        <div class="flex items-center gap-2">
                                            <span class="h-2.5 w-2.5 rounded-full {{ $item['color'] }}"></span>
                                            <span class="text-sm font-semibold text-[var(--color-on-surface-strong)]">{{ $item['label'] }}</span>
                                        </div>
                                        <div class="h-3 w-full overflow-hidden rounded-full bg-[var(--color-surface)]">
                                            <div class="h-full rounded-full {{ $item['color'] }}" style="width: {{ $width }}%"></div>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm font-semibold {{ $item['text'] }}">{{ $item['total'] }}</p>
                                            <p class="text-[11px] text-[var(--color-on-surface)]/60">{{ $share }}%</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    <div class="mt-4 grid gap-3 sm:grid-cols-3">
                        <div class="rounded-[12px] border border-[var(--color-outline)] bg-[var(--color-surface-alt)] px-3 py-2">
                            <p class="text-xs text-[var(--color-on-surface)]/60">Series disponibles</p>
                            <p class="text-lg font-semibold text-[var(--color-success)]">{{ $seriesDisponibles }}</p>
                        </div>
                        <div class="rounded-[12px] border border-[var(--color-outline)] bg-[var(--color-surface-alt)] px-3 py-2">
                            <p class="text-xs text-[var(--color-on-surface)]/60">En mantenimiento</p>
                            <p class="text-lg font-semibold text-[var(--color-warning)]">{{ $seriesMantenimiento }}</p>
                        </div>
                        <div class="rounded-[12px] border border-[var(--color-outline)] bg-[var(--color-surface-alt)] px-3 py-2">
                            <p class="text-xs text-[var(--color-on-surface)]/60">Consumibles en alerta</p>
                            <p class="text-lg font-semibold text-[var(--color-danger)]">{{ $consumiblesBajoStock + $consumiblesAgotados }}</p>
                        </div>
                    </div>

                    <div class="mt-5 rounded-[16px] border border-[var(--color-outline)] bg-[var(--color-surface-alt)]/70 p-4">
                        <div class="mb-3">
                            <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-on-surface)]/70">Asistente</p>
                            <h3 class="text-lg font-semibold text-[var(--color-on-surface-strong)]">Recomendaciones operativas</h3>
                        </div>
                        @livewire('recomendacion.recomendador')
                    </div>
                </div>
            </div>

            {{-- Lista de movimientos --}}
            <div class="self-start h-fit rounded-[20px] border border-[var(--color-outline)] bg-[var(--color-surface)] shadow-sm">
                <div class="p-5 flex items-center justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-on-surface)]/70">Tendencia</p>
                        <h3 class="text-lg font-semibold text-[var(--color-on-surface-strong)]">Prestamos recientes</h3>
                    </div>
                    <a href="{{ route('prestamos.index') }}" class="text-xs font-semibold text-[var(--color-primary)] hover:underline">Ver todos</a>
                </div>
                <div class="px-5 pb-5 space-y-4">
                    @php
                        $trend = collect($prestamosTendencia ?? []);
                        $trendMax = max(1, $trend->max('total') ?? 1);
                        $chartWidth = 320;
                        $chartHeight = 170;
                        $leftPad = 18;
                        $rightPad = 18;
                        $topPad = 20;
                        $bottomPad = 34;
                        $plotWidth = $chartWidth - $leftPad - $rightPad;
                        $plotHeight = $chartHeight - $topPad - $bottomPad;
                        $stepX = $trend->count() > 1 ? $plotWidth / ($trend->count() - 1) : 0;
                        $points = $trend->values()->map(function ($point, $index) use ($leftPad, $topPad, $plotHeight, $trendMax, $stepX) {
                            $x = $leftPad + ($index * $stepX);
                            $y = $topPad + $plotHeight - (($point['total'] / $trendMax) * $plotHeight);

                            return [
                                'x' => round($x, 2),
                                'y' => round($y, 2),
                                'label' => $point['label'],
                                'full_label' => $point['full_label'],
                                'total' => $point['total'],
                            ];
                        });
                        $linePath = $points->map(fn ($point, $index) => ($index === 0 ? 'M' : 'L').$point['x'].' '.$point['y'])->implode(' ');
                        $areaPath = $points->isNotEmpty()
                            ? $linePath.' L '.$points->last()['x'].' '.($topPad + $plotHeight).' L '.$points->first()['x'].' '.($topPad + $plotHeight).' Z'
                            : '';
                    @endphp

                    @if($trend->sum('total') === 0)
                        <div class="h-[220px] rounded-[16px] border border-dashed border-[var(--color-outline)] flex items-center justify-center text-sm text-[var(--color-on-surface)]/60">
                            Sin prestamos recientes para graficar.
                        </div>
                    @else
                        <div class="rounded-[16px] border border-[var(--color-outline)] bg-gradient-to-br from-fuchsia-500/10 via-sky-500/5 to-[var(--color-surface-alt)] p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-[11px] uppercase tracking-[0.18em] text-[var(--color-on-surface)]/60">Ultimos 6 meses</p>
                                    <p class="mt-1 text-2xl font-bold text-[var(--color-on-surface-strong)]">{{ $trend->sum('total') }}</p>
                                    <p class="text-xs text-[var(--color-on-surface)]/70">Prestamos registrados</p>
                                </div>
                                <div class="rounded-full bg-white/70 px-3 py-1 text-[11px] font-semibold text-fuchsia-600 border border-fuchsia-200">
                                    Pico: {{ $trend->max('total') }}
                                </div>
                            </div>

                            <div class="mt-4">
                                <svg viewBox="0 0 {{ $chartWidth }} {{ $chartHeight }}" class="w-full h-[170px] overflow-visible">
                                    <defs>
                                        <linearGradient id="prestamosAreaGradient" x1="0" y1="0" x2="0" y2="1">
                                            <stop offset="0%" stop-color="#d946ef" stop-opacity="0.28" />
                                            <stop offset="100%" stop-color="#d946ef" stop-opacity="0.03" />
                                        </linearGradient>
                                    </defs>

                                    @for($i = 0; $i <= 3; $i++)
                                        @php
                                            $gridY = $topPad + (($plotHeight / 3) * $i);
                                        @endphp
                                        <line x1="{{ $leftPad }}" y1="{{ $gridY }}" x2="{{ $chartWidth - $rightPad }}" y2="{{ $gridY }}" stroke="rgba(148, 163, 184, 0.25)" stroke-dasharray="4 4" />
                                    @endfor

                                    <path d="{{ $areaPath }}" fill="url(#prestamosAreaGradient)" />
                                    <path d="{{ $linePath }}" fill="none" stroke="#d946ef" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />

                                    @foreach($points as $point)
                                        <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="4.5" fill="#ffffff" stroke="#d946ef" stroke-width="2.5" />
                                        <text x="{{ $point['x'] }}" y="{{ $chartHeight - 10 }}" text-anchor="middle" class="fill-[var(--color-on-surface)]/60 text-[10px]">{{ strtoupper($point['label']) }}</text>
                                    @endforeach
                                </svg>
                            </div>

                            <div class="mt-3 grid grid-cols-3 gap-2">
                                @foreach($trend->take(3) as $point)
                                    <div class="rounded-[12px] bg-[var(--color-surface)]/80 border border-[var(--color-outline)] px-3 py-2">
                                        <p class="text-[11px] uppercase tracking-wide text-[var(--color-on-surface)]/60">{{ $point['label'] }}</p>
                                        <p class="text-sm font-semibold text-[var(--color-on-surface-strong)]">{{ $point['total'] }} prestamos</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="space-y-3">
                        @forelse($prestamosRecientes as $row)
                            <div class="flex items-center gap-3 rounded-[14px] border border-[var(--color-outline)] bg-[var(--color-surface-alt)] px-3 py-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-[12px] bg-[var(--color-primary)]/12 text-[var(--color-primary)] font-semibold">
                                    {{ \Illuminate\Support\Str::of($row['policia'])->explode(' ')->map(fn($p)=>\Illuminate\Support\Str::substr($p,0,1))->take(2)->implode('') }}
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-semibold text-[var(--color-on-surface-strong)]">{{ $row['policia'] }}</p>
                                    <p class="text-xs text-[var(--color-on-surface)]/70">{{ $row['articulo'] }}</p>
                                    <p class="text-[11px] text-[var(--color-on-surface)]/60">{{ $row['fecha'] }}</p>
                                </div>
                                <div>
                                    @if($row['estado'] === 'pendiente')
                                        <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[var(--color-warning)]/15 text-[var(--color-warning)] border border-[var(--color-warning)]/40">Pendiente</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[var(--color-success)]/15 text-[var(--color-success)] border border-[var(--color-success)]/40">Concluido</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-[var(--color-on-surface)]/60">Sin prestamos recientes.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="grid items-start gap-5 lg:grid-cols-3">
            {{-- Inventario por categoria --}}
            <div class="lg:col-span-2 rounded-[20px] border border-[var(--color-outline)] bg-[var(--color-surface)] shadow-sm p-5">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-on-surface)]/70">Inventario</p>
                        <h3 class="text-lg font-semibold text-[var(--color-on-surface-strong)]">Distribucion por categoria</h3>
                    </div>
                    <span class="text-xs font-semibold text-[var(--color-on-surface)]/70">Total: {{ $totalInventario }}</span>
                </div>
                <div class="space-y-3">
                    @forelse($categorias as $cat)
                        @php $pct = $totalInventario > 0 ? round(($cat->total / $totalInventario) * 100) : 0; @endphp
                        <div class="flex items-center gap-3 rounded-[14px] border border-[var(--color-outline)] bg-[var(--color-surface-alt)] px-3 py-2">
                            <div class="h-9 w-9 rounded-[12px] flex items-center justify-center bg-gradient-to-br from-[var(--color-primary)]/15 to-[var(--color-secondary)]/10 text-[var(--color-on-surface-strong)] text-sm font-semibold">
                                {{ $pct }}%
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-[var(--color-on-surface-strong)]">{{ $cat->categoria->nombre ?? 'Sin categoria' }}</p>
                                <div class="h-2 w-full rounded-full bg-[var(--color-surface)] mt-1 overflow-hidden">
                                    <div class="h-full rounded-full bg-[var(--color-primary)]/80" style="width: {{ $pct }}%"></div>
                                </div>
                            </div>
                            <span class="text-sm font-semibold text-[var(--color-on-surface)]/70">{{ $cat->total }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-[var(--color-on-surface)]/60">Sin datos de categorias.</p>
                    @endforelse
                </div>
            </div>

            {{-- Alertas + asistente --}}
            <div class="space-y-4">
                <div class="rounded-[20px] border border-[var(--color-outline)] bg-[var(--color-surface)] shadow-sm p-5">
                    <div class="mb-3">
                        <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-on-surface)]/70">Alertas</p>
                        <h3 class="text-lg font-semibold text-[var(--color-on-surface-strong)]">Situaciones a supervisar</h3>
                    </div>
                    <div class="space-y-3">
                        @if($devolucionesPendientes > 0)
                            <div class="flex gap-3 rounded-[14px] border border-[var(--color-warning)]/30 bg-[var(--color-warning)]/10 p-3">
                                <div class="flex-shrink-0">
                                    <span class="w-9 h-9 rounded-full bg-[var(--color-warning)]/25 border border-[var(--color-warning)]/50 flex items-center justify-center text-[var(--color-warning)]">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v4m0 3.5h.01M10.29 3.86c.6-1.04 2.1-1.04 2.7 0l7.27 12.68c.6 1.04-.15 2.36-1.35 2.36H4.37c-1.2 0-1.95-1.32-1.35-2.36L10.29 3.86z" />
                                        </svg>
                                    </span>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-[var(--color-on-surface-strong)]">Devoluciones pendientes</p>
                                    <p class="text-sm text-[var(--color-on-surface)]/80">{{ $devolucionesPendientes }} prestamos activos requieren cierre.</p>
                                </div>
                            </div>
                        @endif
                        <div class="flex gap-3 rounded-[14px] border border-[var(--color-info)]/30 bg-[var(--color-info)]/10 p-3">
                            <div class="flex-shrink-0">
                                <span class="w-9 h-9 rounded-full bg-[var(--color-info)]/25 border border-[var(--color-info)]/50 flex items-center justify-center text-[var(--color-info)]">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 17.25v-6.5m0-3.5h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </span>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-[var(--color-on-surface-strong)]">Personal activo</p>
                                <p class="text-sm text-[var(--color-on-surface)]/80">{{ $personalActivo }} oficiales disponibles.</p>
                            </div>
                        </div>
                        @if($seriesInoperativas > 0)
                            <div class="flex gap-3 rounded-[14px] border border-[var(--color-danger)]/30 bg-[var(--color-danger)]/10 p-3">
                                <div class="flex-shrink-0">
                                    <span class="w-9 h-9 rounded-full bg-[var(--color-danger)]/25 border border-[var(--color-danger)]/50 flex items-center justify-center text-[var(--color-danger)]">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 6h.008v.008H12v-.008z" />
                                        </svg>
                                    </span>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-[var(--color-on-surface-strong)]">Armamento inoperativo</p>
                                    <p class="text-sm text-[var(--color-on-surface)]/80">{{ $seriesInoperativas }} series requieren evaluacion o baja.</p>
                                </div>
                            </div>
                        @endif
                        @if(($consumiblesBajoStock + $consumiblesAgotados) > 0)
                            <div class="flex gap-3 rounded-[14px] border border-[var(--color-warning)]/30 bg-[var(--color-warning)]/10 p-3">
                                <div class="flex-shrink-0">
                                    <span class="w-9 h-9 rounded-full bg-[var(--color-warning)]/25 border border-[var(--color-warning)]/50 flex items-center justify-center text-[var(--color-warning)]">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.75 6.75h16.5M6.75 3.75v6m10.5-6v6M4.5 10.5h15v8.25a1.5 1.5 0 01-1.5 1.5H6a1.5 1.5 0 01-1.5-1.5V10.5z" />
                                        </svg>
                                    </span>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-[var(--color-on-surface-strong)]">Consumibles con alerta</p>
                                    <p class="text-sm text-[var(--color-on-surface)]/80">{{ $consumiblesBajoStock }} con bajo stock y {{ $consumiblesAgotados }} agotados.</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
