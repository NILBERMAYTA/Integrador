<x-layouts.app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        {{-- Tarjetas de estadisticas principales --}}
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            {{-- Armamento en prestamo --}}
            <div class="relative overflow-hidden rounded-xl border border-outline dark:border-outline-dark bg-surface dark:bg-surface-dark p-6">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-on-surface/70 dark:text-on-surface-dark/70">Armamento en prestamo</p>
                        <h3 class="mt-2 text-3xl font-bold text-on-surface-strong dark:text-on-surface-dark-strong">{{ $prestamosActivos }}</h3>
                        <p class="mt-1 text-sm text-on-surface/60 dark:text-on-surface-dark/60">Pendientes de devolucion</p>
                    </div>
                    <div class="rounded-lg bg-primary/10 dark:bg-primary-dark/10 p-3">
                        <svg class="h-6 w-6 text-primary dark:text-primary-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Personal Activo --}}
            <div class="relative overflow-hidden rounded-xl border border-outline dark:border-outline-dark bg-surface dark:bg-surface-dark p-6">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-on-surface/70 dark:text-on-surface-dark/70">Personal Activo</p>
                        <h3 class="mt-2 text-3xl font-bold text-on-surface-strong dark:text-on-surface-dark-strong">{{ $personalActivo }}</h3>
                        <p class="mt-1 text-sm text-on-surface/60 dark:text-on-surface-dark/60">Oficiales registrados</p>
                    </div>
                    <div class="rounded-lg bg-secondary/10 dark:bg-secondary-dark/10 p-3">
                        <svg class="h-6 w-6 text-secondary dark:text-secondary-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Devoluciones pendientes --}}
            <div class="relative overflow-hidden rounded-xl border border-outline dark:border-outline-dark bg-surface dark:bg-surface-dark p-6">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-on-surface/70 dark:text-on-surface-dark/70">Devoluciones pendientes</p>
                        <h3 class="mt-2 text-3xl font-bold text-on-surface-strong dark:text-on-surface-dark-strong">{{ $devolucionesPendientes }}</h3>
                        <p class="mt-1 text-sm text-on-surface/60 dark:text-on-surface-dark/60">Prestamos activos por cerrar</p>
                    </div>
                    <div class="rounded-lg bg-warning/10 p-3">
                        <svg class="h-6 w-6 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Seccion principal con datos --}}
        <div class="grid gap-4 lg:grid-cols-3">
            {{-- Prestamos Recientes --}}
            <div class="lg:col-span-2 relative overflow-hidden rounded-xl border border-outline dark:border-outline-dark bg-surface dark:bg-surface-dark">
                <div class="p-6">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-on-surface-strong dark:text-on-surface-dark-strong">Prestamos recientes</h2>
                        <a href="{{ route('prestamos.index') }}" class="text-sm font-medium text-primary dark:text-primary-dark hover:underline">Ver todos</a>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-outline dark:border-outline-dark">
                                    <th class="pb-3 text-left text-xs font-medium uppercase tracking-wider text-on-surface/70 dark:text-on-surface-dark/70">Oficial</th>
                                    <th class="pb-3 text-left text-xs font-medium uppercase tracking-wider text-on-surface/70 dark:text-on-surface-dark/70">Arma</th>
                                    <th class="pb-3 text-left text-xs font-medium uppercase tracking-wider text-on-surface/70 dark:text-on-surface-dark/70">Fecha</th>
                                    <th class="pb-3 text-left text-xs font-medium uppercase tracking-wider text-on-surface/70 dark:text-on-surface-dark/70">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-outline dark:divide-outline-dark">
                                @forelse($prestamosRecientes as $row)
                                    <tr class="group hover:bg-surface-alt dark:hover:bg-surface-dark-alt transition-colors">
                                        <td class="py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 dark:bg-primary-dark/10 text-primary dark:text-primary-dark font-semibold">
                                                    {{ \Illuminate\Support\Str::of($row['policia'])->explode(' ')->map(fn($p)=>\Illuminate\Support\Str::substr($p,0,1))->take(2)->implode('') }}
                                                </div>
                                                <div>
                                                    <p class="font-medium text-on-surface-strong dark:text-on-surface-dark-strong">{{ $row['policia'] }}</p>
                                                    @if($row['badge'])
                                                        <p class="text-sm text-on-surface/60 dark:text-on-surface-dark/60">Badge #{{ $row['badge'] }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-4">
                                            <p class="font-medium text-on-surface dark:text-on-surface-dark">{{ $row['articulo'] }}</p>
                                            @if($row['serie'])
                                                <p class="text-sm text-on-surface/60 dark:text-on-surface-dark/60">Serie: {{ $row['serie'] }}</p>
                                            @endif
                                        </td>
                                        <td class="py-4 text-sm text-on-surface/70 dark:text-on-surface-dark/70">{{ $row['fecha'] }}</td>
                                        <td class="py-4">
                                            @if($row['estado'] === 'pendiente')
                                                <span class="inline-flex items-center rounded-full bg-warning/10 px-2.5 py-0.5 text-xs font-medium text-warning">Pendiente</span>
                                            @else
                                                <span class="inline-flex items-center rounded-full bg-success/10 px-2.5 py-0.5 text-xs font-medium text-success">Concluido</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-6 text-center text-on-surface/60">Sin prestamos recientes</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Inventario por Categoria --}}
            <div class="lg:col-span-1 flex flex-col gap-4">
                <div class="relative overflow-hidden rounded-xl border border-outline dark:border-outline-dark bg-surface dark:bg-surface-dark">
                    <div class="p-6">
                        <h2 class="mb-4 text-lg font-semibold text-on-surface-strong dark:text-on-surface-dark-strong">Inventario por categoria</h2>
                        <div class="space-y-4">
                            @forelse($categorias as $cat)
                                @php $pct = $totalInventario > 0 ? round(($cat->total / $totalInventario) * 100) : 0; @endphp
                                <div>
                                    <div class="mb-2 flex items-center justify-between">
                                        <span class="text-sm font-medium text-on-surface dark:text-on-surface-dark">{{ $cat->categoria->nombre ?? 'Sin categoria' }}</span>
                                        <span class="text-sm font-semibold text-on-surface-strong dark:text-on-surface-dark-strong">{{ $cat->total }}</span>
                                    </div>
                                    <div class="h-2 w-full overflow-hidden rounded-full bg-surface-alt dark:bg-surface-dark-alt">
                                        <div class="h-full rounded-full bg-primary dark:bg-primary-dark" style="width: {{ $pct }}%"></div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-on-surface/60">Sin datos de categorias.</p>
                            @endforelse
                        </div>
                        <div class="mt-6 rounded-lg bg-primary/5 dark:bg-primary-dark/5 p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-on-surface/70 dark:text-on-surface-dark/70">Total inventario</p>
                                    <p class="mt-1 text-2xl font-bold text-primary dark:text-primary-dark">{{ $totalInventario }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-on-surface/70 dark:text-on-surface-dark/70">Prestamos concluidos</p>
                                    <p class="mt-1 text-2xl font-bold text-on-surface-strong dark:text-on-surface-dark-strong">{{ $prestamosConcluidos }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Asistente tactico --}}
                <div class="relative overflow-hidden rounded-xl border border-outline dark:border-outline-dark bg-surface dark:bg-surface-dark p-4">
                    @livewire('recomendacion.recomendador')
                </div>
            </div>
        </div>

        {{-- Alertas y Actividad --}}
        <div class="grid gap-4 lg:grid-cols-2">
            <div class="relative overflow-hidden rounded-xl border border-outline dark:border-outline-dark bg-surface dark:bg-surface-dark p-6">
                <h2 class="mb-4 text-lg font-semibold text-on-surface-strong dark:text-on-surface-dark-strong">Alertas importantes</h2>
                <div class="space-y-3">
                    @if($devolucionesPendientes > 0)
                        <div class="flex gap-3 rounded-lg border border-warning/20 bg-warning/5 p-3">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-warning" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-warning">Devoluciones pendientes</p>
                                <p class="mt-1 text-sm text-on-surface/70 dark:text-on-surface-dark/70">{{ $devolucionesPendientes }} prestamos activos requieren cierre.</p>
                            </div>
                        </div>
                    @endif
                    <div class="flex gap-3 rounded-lg border border-info/20 bg-info/5 p-3">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-info" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-info">Personal activo</p>
                            <p class="mt-1 text-sm text-on-surface/70 dark:text-on-surface-dark/70">{{ $personalActivo }} oficiales disponibles.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-xl border border-outline dark:border-outline-dark bg-surface dark:bg-surface-dark p-6">
                <h2 class="mb-4 text-lg font-semibold text-on-surface-strong dark:text-on-surface-dark-strong">Actividad reciente</h2>
                <div class="space-y-4">
                    @forelse($prestamosRecientes as $row)
                        <div class="flex gap-3">
                            <div class="flex-shrink-0">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full {{ $row['estado']==='pendiente' ? 'bg-warning/10' : 'bg-success/10' }}">
                                    <svg class="h-4 w-4 {{ $row['estado']==='pendiente' ? 'text-warning' : 'text-success' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-on-surface dark:text-on-surface-dark">{{ $row['policia'] }}</p>
                                <p class="text-sm text-on-surface/60 dark:text-on-surface-dark/60">{{ $row['articulo'] }}</p>
                                <p class="mt-1 text-xs text-on-surface/50 dark:text-on-surface-dark/50">{{ $row['fecha'] }} · {{ ucfirst($row['estado']) }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-on-surface/60">Sin actividad reciente.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
