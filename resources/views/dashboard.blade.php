<x-layouts.app :title="__('Dashboard')">
    <div class="flex flex-col gap-6">
        {{-- Encabezado --}}
        <div class="relative overflow-hidden rounded-[var(--radius-radius-lg,1.25rem)] border border-[var(--color-outline)] bg-[var(--color-surface)] text-[var(--color-on-surface)] shadow-sm">
            <div class="absolute inset-0 bg-gradient-to-r from-[var(--color-primary)]/10 via-[var(--color-primary)]/5 to-[var(--color-secondary)]/10 pointer-events-none"></div>
            <div class="relative p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-on-surface)]/70">Panel táctico</p>
                    <h1 class="text-3xl font-bold">Estado general</h1>
                    <p class="text-sm text-[var(--color-on-surface)]/70">Operaciones, personal y devoluciones en tiempo real.</p>
                </div>
                <div class="flex items-center gap-2 rounded-full border border-[var(--color-outline)] bg-[var(--color-surface-alt)] px-3 py-2 text-xs font-medium">
                    <span class="w-2 h-2 rounded-full bg-[var(--color-success)] animate-pulse"></span>
                    Sistema en línea
                </div>
            </div>
        </div>

        {{-- Tarjetas KPI --}}
        <div class="grid gap-4 md:grid-cols-3">
            @php
                $cards = [
                    ['title' => 'Armamento en préstamo', 'value' => $prestamosActivos, 'subtitle' => 'Pendientes de devolución', 'color' => 'primary'],
                    ['title' => 'Personal activo', 'value' => $personalActivo, 'subtitle' => 'Oficiales registrados', 'color' => 'secondary'],
                    ['title' => 'Devoluciones pendientes', 'value' => $devolucionesPendientes, 'subtitle' => 'Préstamos por cerrar', 'color' => 'warning'],
                ];
            @endphp
            @foreach($cards as $card)
                @php
                    $bg = "bg-[var(--color-{$card['color']})]/10";
                    $border = "border-[var(--color-{$card['color']})]/40";
                    $text = "text-[var(--color-{$card['color']})]";
                @endphp
                <div class="relative overflow-hidden rounded-[var(--radius-radius-lg,1.25rem)] border {{ $border }} bg-[var(--color-surface)] p-5 shadow-sm">
                    <div class="absolute inset-0 {{ $bg }}"></div>
                    <div class="relative flex items-start justify-between">
                        <div>
                            <p class="text-xs uppercase tracking-wide text-[var(--color-on-surface)]/70">{{ $card['title'] }}</p>
                            <h3 class="mt-2 text-3xl font-extrabold text-[var(--color-on-surface-strong)]">{{ $card['value'] }}</h3>
                            <p class="text-sm text-[var(--color-on-surface)]/70">{{ $card['subtitle'] }}</p>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-[var(--color-surface-alt)] border border-[var(--color-outline)] flex items-center justify-center {{ $text }}">●</div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="grid gap-5 lg:grid-cols-3">
            {{-- Préstamos recientes --}}
            <div class="lg:col-span-2 rounded-[var(--radius-radius-lg,1.25rem)] border border-[var(--color-outline)] bg-[var(--color-surface)] shadow-sm">
                <div class="p-6 flex items-center justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-on-surface)]/70">Movimientos</p>
                        <h2 class="text-xl font-semibold text-[var(--color-on-surface-strong)]">Préstamos recientes</h2>
                    </div>
                    <a href="{{ route('prestamos.index') }}" class="text-sm font-medium text-[var(--color-primary)] hover:underline">Ver todos →</a>
                </div>
                <div class="px-6 pb-6 overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="text-[var(--color-on-surface)]/60 uppercase text-xs">
                            <tr>
                                <th class="py-2 text-left">Oficial</th>
                                <th class="py-2 text-left">Artículo</th>
                                <th class="py-2 text-left">Fecha</th>
                                <th class="py-2 text-left">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[var(--color-outline)]">
                            @forelse($prestamosRecientes as $row)
                                <tr class="hover:bg-[var(--color-surface-alt)] transition-colors">
                                    <td class="py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-full bg-[var(--color-primary)]/15 border border-[var(--color-primary)]/30 text-[var(--color-primary)] flex items-center justify-center text-xs font-bold">
                                                {{ \Illuminate\Support\Str::of($row['policia'])->explode(' ')->map(fn($p)=>\Illuminate\Support\Str::substr($p,0,1))->take(2)->implode('') }}
                                            </div>
                                            <div>
                                                <p class="font-semibold text-[var(--color-on-surface-strong)]">{{ $row['policia'] }}</p>
                                                @if($row['badge'])
                                                    <p class="text-[11px] text-[var(--color-on-surface)]/60">Badge #{{ $row['badge'] }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <p class="font-medium text-[var(--color-on-surface)]">{{ $row['articulo'] }}</p>
                                        @if($row['serie'])
                                            <p class="text-[11px] text-[var(--color-on-surface)]/60">Serie: {{ $row['serie'] }}</p>
                                        @endif
                                    </td>
                                    <td class="py-3 text-[var(--color-on-surface)]/80">{{ $row['fecha'] }}</td>
                                    <td class="py-3">
                                        @if($row['estado'] === 'pendiente')
                                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-[var(--color-warning)]/15 text-[var(--color-warning)] border border-[var(--color-warning)]/40">Pendiente</span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-[var(--color-success)]/15 text-[var(--color-success)] border border-[var(--color-success)]/40">Concluido</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-6 text-center text-[var(--color-on-surface)]/60">Sin préstamos recientes</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Inventario + asistente --}}
            <div class="flex flex-col gap-4">
                <div class="rounded-[var(--radius-radius-lg,1.25rem)] border border-[var(--color-outline)] bg-[var(--color-surface)] shadow-sm p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-on-surface)]/70">Inventario</p>
                            <h3 class="text-lg font-semibold text-[var(--color-on-surface-strong)]">Por categoría</h3>
                        </div>
                        <span class="text-xs text-[var(--color-on-surface)]/60">Total: {{ $totalInventario }}</span>
                    </div>
                    <div class="space-y-3">
                        @forelse($categorias as $cat)
                            @php $pct = $totalInventario > 0 ? round(($cat->total / $totalInventario) * 100) : 0; @endphp
                            <div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="font-medium text-[var(--color-on-surface)]">{{ $cat->categoria->nombre ?? 'Sin categoria' }}</span>
                                    <span class="text-[var(--color-on-surface)]/60">{{ $cat->total }}</span>
                                </div>
                                <div class="h-2 w-full rounded-full bg-[var(--color-surface-alt)] mt-1">
                                    <div class="h-full rounded-full bg-[var(--color-primary)]/70" style="width: {{ $pct }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-[var(--color-on-surface)]/60">Sin datos de categorías.</p>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-[var(--radius-radius-lg,1.25rem)] border border-[var(--color-outline)] bg-[var(--color-surface)] shadow-sm p-4">
                    @livewire('recomendacion.recomendador')
                </div>
            </div>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <div class="rounded-[var(--radius-radius-lg,1.25rem)] border border-[var(--color-outline)] bg-[var(--color-surface)] shadow-sm p-6">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-on-surface)]/70">Alertas</p>
                        <h3 class="text-lg font-semibold text-[var(--color-on-surface-strong)]">Situaciones a supervisar</h3>
                    </div>
                </div>
                <div class="space-y-3">
                    @if($devolucionesPendientes > 0)
                        <div class="flex gap-3 rounded-xl border border-[var(--color-warning)]/30 bg-[var(--color-warning)]/10 p-3">
                            <div class="flex-shrink-0">
                                <span class="w-8 h-8 rounded-full bg-[var(--color-warning)]/20 border border-[var(--color-warning)]/40 flex items-center justify-center text-[var(--color-warning)]">!</span>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-[var(--color-on-surface-strong)]">Devoluciones pendientes</p>
                                <p class="text-sm text-[var(--color-on-surface)]/80">{{ $devolucionesPendientes }} préstamos activos requieren cierre.</p>
                            </div>
                        </div>
                    @endif
                    <div class="flex gap-3 rounded-xl border border-[var(--color-info)]/30 bg-[var(--color-info)]/10 p-3">
                        <div class="flex-shrink-0">
                            <span class="w-8 h-8 rounded-full bg-[var(--color-info)]/20 border border-[var(--color-info)]/40 flex items-center justify-center text-[var(--color-info)]">i</span>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-[var(--color-on-surface-strong)]">Personal activo</p>
                            <p class="text-sm text-[var(--color-on-surface)]/80">{{ $personalActivo }} oficiales disponibles.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-[var(--radius-radius-lg,1.25rem)] border border-[var(--color-outline)] bg-[var(--color-surface)] shadow-sm p-6">
                <div class="mb-3">
                    <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-on-surface)]/70">Actividad</p>
                    <h3 class="text-lg font-semibold text-[var(--color-on-surface-strong)]">Movimientos recientes</h3>
                </div>
                <div class="space-y-4">
                    @forelse($prestamosRecientes as $row)
                        <div class="flex gap-3">
                            <div class="flex-shrink-0">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full {{ $row['estado']==='pendiente' ? 'bg-[var(--color-warning)]/15 text-[var(--color-warning)]' : 'bg-[var(--color-success)]/15 text-[var(--color-success)]' }}">
                                    ●
                                </div>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-[var(--color-on-surface-strong)]">{{ $row['policia'] }}</p>
                                <p class="text-sm text-[var(--color-on-surface)]/70">{{ $row['articulo'] }}</p>
                                <p class="mt-1 text-xs text-[var(--color-on-surface)]/60">{{ $row['fecha'] }} · {{ ucfirst($row['estado']) }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-[var(--color-on-surface)]/60">Sin actividad reciente.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
