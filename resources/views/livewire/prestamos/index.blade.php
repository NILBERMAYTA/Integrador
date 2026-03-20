<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">
                Prestamos y asignaciones
            </h1>
            <p class="text-sm text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] opacity-70">
                Historial de prestamos realizados a personal dentro de la unidad.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <x-form.header_button variant="primary" href="{{ route('prestamos.create') }}" wire:navigate>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nueva asignacion
            </x-form.header_button>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="p-4 rounded-[var(--radius-radius)] bg-[var(--color-success)]/10 border border-[var(--color-success)] text-[var(--color-success)] flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] rounded-[var(--radius-radius)] shadow-sm border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] p-4">
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex-1 min-w-[260px]">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input
                        type="text"
                        wire:model.debounce.300ms="search"
                        placeholder="Buscar por policia..."
                        class="w-full pl-10 pr-4 py-2.5 rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] focus:ring-2 focus:ring-[var(--color-primary)] dark:focus:ring-[var(--color-primary-dark)] focus:border-transparent transition-all"
                    />
                </div>
            </div>

            <div class="min-w-[180px]">
                <select wire:model="eventoId" class="w-full rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] px-4 py-2.5 bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">
                    <option value="">Todos los conflictos</option>
                    @foreach($eventos as $ev)
                        <option value="{{ $ev->id }}">{{ $ev->nombre ?? 'ID '.$ev->id }}</option>
                    @endforeach
                </select>
            </div>

            <div class="min-w-[160px]">
                <select wire:model="estado" class="w-full rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] px-4 py-2.5 bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">
                    <option value="">Todos los estados</option>
                    <option value="pendiente">Pendiente</option>
                    <option value="concluido">Concluido</option>
                </select>
            </div>

            <div class="min-w-[200px]">
                <select wire:model="unidadId" class="w-full rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] px-4 py-2.5 bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">
                    <option value="">Todas las unidades</option>
                    @foreach($unidades as $unidad)
                        <option value="{{ $unidad->id }}">{{ ($unidad->sigla ? $unidad->sigla.' - ' : '').$unidad->nombre }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] rounded-[var(--radius-radius)] shadow-sm border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-[var(--color-surface-alt)] dark:bg-[var(--color-surface-dark-alt)] border-b border-[var(--color-outline)] dark:border-[var(--color-outline-dark)]">
                    <tr>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider">Fecha</th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider">Conflicto</th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider">Unidad</th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider">Policia</th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-center">Items</th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-center">Estado</th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider">Observaciones</th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-right">Acciones</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-[var(--color-outline)] dark:divide-[var(--color-outline-dark)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)]">
                    @forelse($operaciones as $operacion)
                        @php
                            $devueltosCantidad = [];
                            foreach ($operacion->devoluciones as $dev) {
                                foreach ($dev->detalles as $detDev) {
                                    if ($detDev->articulo?->isCantidad()) {
                                        $devueltosCantidad[$detDev->articulo_id] = ($devueltosCantidad[$detDev->articulo_id] ?? 0) + (int) $detDev->cantidad;
                                    }
                                }
                            }

                            $pendiente = false;
                            foreach ($operacion->detalles as $detOp) {
                                if ($detOp->articulo?->isSerializado()) {
                                    $asignadas = $detOp->series->filter(fn($s) => optional($s->serie)->operacion_detalle_id_actual === $detOp->id);
                                    if ($asignadas->count() > 0) { $pendiente = true; break; }
                                } else {
                                    $dev = $devueltosCantidad[$detOp->articulo_id] ?? 0;
                                    if ($detOp->cantidad > $dev) { $pendiente = true; break; }
                                }
                            }

                            $estadoPrestamo = $pendiente ? 'pendiente' : 'concluido';
                        @endphp
                        <tr class="hover:bg-[var(--color-surface-alt)] dark:hover:bg-[var(--color-surface-dark-alt)] transition-colors" x-data="{ open:false }" x-cloak>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ optional($operacion->fecha)->format('Y-m-d H:i') }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $operacion->evento->nombre ?? 'No especificado' }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $operacion->unidad?->sigla ?? $operacion->unidad?->nombre ?? 'No definida' }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $operacion->policia->name ?? 'No definido' }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-[var(--color-primary)]/10 text-[var(--color-primary)]">
                                    {{ $operacion->detalles->count() }} articulos
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($estadoPrestamo === 'concluido')
                                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">Concluido</span>
                                @else
                                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">Pendiente</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                {{ $operacion->observaciones ?: '-' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <x-form.outline_button type="button" variant="details" @click="open = true">
                                        Detalles
                                    </x-form.outline_button>

                                    <x-form.outline_button variant="return" href="{{ route('prestamos.devolucion', $operacion) }}" wire:navigate>
                                        Devolver
                                    </x-form.outline_button>
                                </div>

                                <div
                                    x-show="open"
                                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
                                    @click.self="open = false"
                                    x-transition
                                >
                                    <div class="w-full max-w-3xl bg-[var(--color-surface)] rounded-[var(--radius-radius)] shadow-xl border border-[var(--color-outline)]">
                                        <div class="p-4 border-b flex items-center justify-between">
                                            <div>
                                                <p class="text-xs uppercase tracking-wide text-[var(--color-on-surface)] opacity-70">Prestamo</p>
                                                <h3 class="text-lg font-semibold text-[var(--color-on-surface-strong)]">Detalles de la asignacion</h3>
                                            </div>
                                            <button class="text-[var(--color-on-surface)] hover:text-[var(--color-danger)]" @click="open = false">Cerrar</button>
                                        </div>

                                        <div class="p-4 space-y-4 max-h-[70vh] overflow-y-auto">
                                            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-sm">
                                                <div>
                                                    <p class="text-xs opacity-60">Fecha</p>
                                                    <p class="font-medium">{{ optional($operacion->fecha)->format('Y-m-d H:i') }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-xs opacity-60">Conflicto</p>
                                                    <p class="font-medium">{{ $operacion->evento->nombre ?? 'No especificado' }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-xs opacity-60">Unidad</p>
                                                    <p class="font-medium">{{ $operacion->unidad?->sigla ?? $operacion->unidad?->nombre ?? 'No definida' }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-xs opacity-60">Policia</p>
                                                    <p class="font-medium">{{ $operacion->policia->name ?? 'No definido' }}</p>
                                                </div>
                                            </div>

                                            <div>
                                                <p class="text-xs opacity-60 mb-1">Observaciones</p>
                                                <p class="text-[var(--color-on-surface)]">{{ $operacion->observaciones ?: 'Sin observaciones' }}</p>
                                            </div>

                                            <div class="overflow-x-auto">
                                                <table class="w-full text-left text-sm">
                                                    <thead>
                                                        <tr class="text-[var(--color-on-surface)] opacity-80">
                                                            <th class="px-2 py-1">Articulo</th>
                                                            <th class="px-2 py-1 text-center">Cantidad</th>
                                                            <th class="px-2 py-1 text-center">Gestion</th>
                                                            <th class="px-2 py-1">Series / Estado</th>
                                                            <th class="px-2 py-1 text-center">Pendiente</th>
                                                            <th class="px-2 py-1">Condicion</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-[var(--color-outline)]">
                                                        @forelse($operacion->detalles as $detalle)
                                                            @php
                                                                $devueltoCantidad = $devueltosCantidad[$detalle->articulo_id] ?? 0;
                                                                $pendienteCantidad = $detalle->articulo?->isCantidad()
                                                                    ? max(0, ($detalle->cantidad ?? 0) - $devueltoCantidad)
                                                                    : 0;
                                                                $seriesAsignadas = $detalle->series->filter(fn($s) => optional($s->serie)->operacion_detalle_id_actual === $detalle->id);
                                                            @endphp
                                                            <tr>
                                                                <td class="px-2 py-1">{{ $detalle->articulo->nombre ?? 'Articulo' }}</td>
                                                                <td class="px-2 py-1 text-center">{{ $detalle->cantidad }}</td>
                                                                <td class="px-2 py-1 text-center">
                                                                    @if($detalle->articulo?->isSerializado())
                                                                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">Por serie</span>
                                                                    @else
                                                                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-800">Por cantidad</span>
                                                                    @endif
                                                                </td>
                                                                <td class="px-2 py-1">
                                                                    @if($detalle->articulo?->isSerializado())
                                                                        @php $codes = $detalle->series->map(fn($s) => [
                                                                            'codigo' => $s->serie->codigo_serie ?? '',
                                                                            'asignado' => optional($s->serie)->operacion_detalle_id_actual === $detalle->id,
                                                                        ]); @endphp
                                                                        @if($codes->count())
                                                                            <div class="flex flex-wrap gap-1">
                                                                                @foreach($codes as $code)
                                                                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $code['asignado'] ? 'bg-amber-100 text-amber-800' : 'bg-green-100 text-green-800' }}">
                                                                                        {{ $code['codigo'] }} · {{ $code['asignado'] ? 'Asignada' : 'Devuelta' }}
                                                                                    </span>
                                                                                @endforeach
                                                                            </div>
                                                                        @else
                                                                            <span class="text-xs text-[var(--color-on-surface)] opacity-70">Sin series</span>
                                                                        @endif
                                                                    @else
                                                                        <span class="text-xs text-[var(--color-on-surface)] opacity-60">-</span>
                                                                    @endif
                                                                </td>
                                                                <td class="px-2 py-1 text-center">
                                                                    @if($detalle->articulo?->isSerializado())
                                                                        {{ $seriesAsignadas->count() }} pendiente(s)
                                                                    @else
                                                                        {{ $pendienteCantidad }}
                                                                    @endif
                                                                </td>
                                                                <td class="px-2 py-1">{{ $detalle->condicion ?? 'N/D' }}</td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="6" class="px-2 py-2 text-center text-[var(--color-on-surface)] opacity-70">
                                                                    No hay articulos registrados en este prestamo.
                                                                </td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <p class="mt-4 font-medium">No hay operaciones registradas</p>
                                <p class="mt-1 text-sm opacity-60">Intenta ajustar los filtros de busqueda</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($operaciones->hasPages())
            <div class="px-6 py-4 border-t border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] bg-[var(--color-surface-alt)] dark:bg-[var(--color-surface-dark-alt)]">
                {{ $operaciones->links() }}
            </div>
        @endif
    </div>
</div>
