<x-layouts.app :title="__('Inicio')">
    <div class="flex flex-col gap-6">
        {{-- Encabezado con CTA --}}
        <div class="relative overflow-hidden rounded-[20px] border border-[var(--color-outline)] bg-gradient-to-r from-[var(--color-primary)]/12 via-[var(--color-primary)]/6 to-[var(--color-secondary)]/12 shadow-sm">
            <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-[var(--color-primary)]/10 blur-3xl"></div>
            <div class="absolute -left-8 bottom-0 h-28 w-28 rounded-full bg-[var(--color-secondary)]/10 blur-3xl"></div>
            <div class="relative px-6 py-5 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="space-y-2">
                    <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-on-surface)]/70">Panel operativo</p>
                    <h1 class="text-3xl font-bold text-[var(--color-on-surface-strong)]">Resumen tactico</h1>
                    <p class="text-sm text-[var(--color-on-surface)]/75">Prestamos, personal y devoluciones en un vistazo.</p>
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

        <div class="grid gap-5 xl:grid-cols-3">
            {{-- Balance operativo --}}
            <div class="xl:col-span-2 rounded-[20px] border border-[var(--color-outline)] bg-[var(--color-surface)] shadow-sm">
                @php
                    $chartData = collect($prestamosRecientes ?? [])
                        ->groupBy(function ($row) {
                            if (!empty($row['fecha'])) {
                                try {
                                    return \Carbon\Carbon::parse($row['fecha'])->format('M');
                                } catch (\Throwable $th) {
                                    return $row['fecha'];
                                }
                            }
                            return 'N/A';
                        })
                        ->map(function ($group, $label) {
                            $activos = $group->where('estado', 'pendiente')->count();
                            $cerrados = $group->where('estado', 'concluido')->count();
                            return [
                                'label' => $label,
                                'activos' => $activos,
                                'cerrados' => $cerrados,
                            ];
                        })
                        ->values();
                    $chartMax = max(1, $chartData->map(fn($p) => $p['activos'] + $p['cerrados'])->max() ?? 1);
                @endphp
                <div class="p-5 flex items-center justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-on-surface)]/70">Tendencia</p>
                        <h2 class="text-xl font-semibold text-[var(--color-on-surface-strong)]">Movimiento de prestamos</h2>
                    </div>
                    <div class="flex gap-2 text-xs font-semibold bg-[var(--color-surface-alt)] rounded-full px-2 py-1 border border-[var(--color-outline)]">
                        <span class="px-3 py-1 rounded-full bg-[var(--color-primary)]/15 text-[var(--color-primary)]">Activos</span>
                        <span class="px-3 py-1 text-[var(--color-on-surface)]/70">Cerrados</span>
                    </div>
                </div>
                <div class="px-5 pb-5">
                    @if($chartData->isEmpty())
                        <div class="h-48 w-full rounded-[14px] border border-dashed border-[var(--color-outline)] flex items-center justify-center text-sm text-[var(--color-on-surface)]/60">
                            Sin datos recientes para graficar.
                        </div>
                    @else
                        <div class="h-48 w-full rounded-[14px] bg-gradient-to-br from-[var(--color-primary)]/12 to-[var(--color-secondary)]/10 relative overflow-hidden flex items-center justify-center">
                            <svg viewBox="0 0 {{ max(1, $chartData->count()) * 80 + 40 }} 200" class="w-[96%] h-[90%]">
                                <line x1="32" y1="160" x2="{{ max(1, $chartData->count()) * 80 + 20 }}" y2="160" stroke="var(--color-outline)" stroke-width="1" />
                                <line x1="32" y1="40" x2="32" y2="160" stroke="var(--color-outline)" stroke-width="1" />
                                @foreach($chartData as $index => $point)
                                    @php
                                        $x = 40 + ($index * 80);
                                        $actHeight = ($point['activos'] / $chartMax) * 110;
                                        $cerrHeight = ($point['cerrados'] / $chartMax) * 110;
                                        $baseY = 160;
                                    @endphp
                                    <rect x="{{ $x }}" y="{{ $baseY - $actHeight }}" width="20" height="{{ $actHeight }}" rx="4" fill="var(--color-primary)" opacity="0.85" />
                                    <rect x="{{ $x + 28 }}" y="{{ $baseY - $cerrHeight }}" width="20" height="{{ $cerrHeight }}" rx="4" fill="var(--color-secondary)" opacity="0.75" />
                                    <text x="{{ $x + 10 }}" y="175" text-anchor="middle" class="text-[10px] fill-[var(--color-on-surface)]/70">{{ $point['label'] }}</text>
                                    <text x="{{ $x + 10 }}" y="{{ $baseY - $actHeight - 6 }}" text-anchor="middle" class="text-[10px] fill-[var(--color-on-surface)]/70">{{ $point['activos'] }}</text>
                                    <text x="{{ $x + 38 }}" y="{{ $baseY - $cerrHeight - 6 }}" text-anchor="middle" class="text-[10px] fill-[var(--color-on-surface)]/70">{{ $point['cerrados'] }}</text>
                                @endforeach
                                <text x="8" y="52" class="text-[10px] fill-[var(--color-on-surface)]/50" transform="rotate(-90 8 52)">Cantidad</text>
                            </svg>
                        </div>
                    @endif
                    <div class="mt-4 grid gap-3 sm:grid-cols-3">
                        <div class="rounded-[12px] border border-[var(--color-outline)] bg-[var(--color-surface-alt)] px-3 py-2">
                            <p class="text-xs text-[var(--color-on-surface)]/60">Activos</p>
                            <p class="text-lg font-semibold text-[var(--color-on-surface-strong)]">{{ $prestamosActivos }}</p>
                        </div>
                        <div class="rounded-[12px] border border-[var(--color-outline)] bg-[var(--color-surface-alt)] px-3 py-2">
                            <p class="text-xs text-[var(--color-on-surface)]/60">Pendientes</p>
                            <p class="text-lg font-semibold text-[var(--color-warning)]">{{ $devolucionesPendientes }}</p>
                        </div>
                        <div class="rounded-[12px] border border-[var(--color-outline)] bg-[var(--color-surface-alt)] px-3 py-2">
                            <p class="text-xs text-[var(--color-on-surface)]/60">Personal disponible</p>
                            <p class="text-lg font-semibold text-[var(--color-success)]">{{ $personalActivo }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Lista de movimientos --}}
            <div class="rounded-[20px] border border-[var(--color-outline)] bg-[var(--color-surface)] shadow-sm">
                <div class="p-5 flex items-center justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-on-surface)]/70">Movimientos</p>
                        <h3 class="text-lg font-semibold text-[var(--color-on-surface-strong)]">Prestamos recientes</h3>
                    </div>
                    <a href="{{ route('prestamos.index') }}" class="text-xs font-semibold text-[var(--color-primary)] hover:underline">Ver todos</a>
                </div>
                <div class="px-5 pb-5 space-y-3">
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

        <div class="grid gap-5 lg:grid-cols-3">
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
                    </div>
                </div>

                <div class="rounded-[20px] border border-[var(--color-outline)] bg-[var(--color-surface)] shadow-sm p-4">
                    @livewire('recomendacion.recomendador')
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
