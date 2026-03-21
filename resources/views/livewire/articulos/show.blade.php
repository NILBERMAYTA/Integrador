<div class="w-full max-w-7xl mx-auto p-6 space-y-6">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-[var(--color-on-surface-strong)]">{{ $articulo->nombre }}</h1>
            <p class="text-sm text-[var(--color-on-surface)] opacity-70">
                Detalle del articulo, su situacion actual y movimientos recientes.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" wire:click="exportPdf" class="px-3 py-2 rounded border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] text-sm hover:bg-[var(--color-surface-alt)] dark:hover:bg-[var(--color-surface-dark-alt)]">
                Exportar PDF
            </button>
            <a href="{{ route('articulos.index') }}" wire:navigate class="px-3 py-2 rounded bg-[var(--color-surface)] border border-[var(--color-outline)] text-sm">
                Volver
            </a>
        </div>
    </div>

    <x-form.toast_notification :message="session('success')" variant="success" />
    <x-form.toast_notification :message="session('error')" variant="danger" />

    <div class="grid gap-4 lg:grid-cols-3">
        <div class="lg:col-span-2 rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-[var(--color-surface)] p-5">
            <p class="text-xs uppercase tracking-wider opacity-60">Informacion general</p>
            <div class="mt-3 grid gap-4 sm:grid-cols-2">
                <div>
                    <p class="text-xs opacity-60">Categoria</p>
                    <p class="font-medium">{{ $articulo->categoria?->nombre ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs opacity-60">Tipo</p>
                    <p class="font-medium">{{ ucfirst($articulo->tipo) }}</p>
                </div>
                <div>
                    <p class="text-xs opacity-60">Gestion</p>
                    <p class="font-medium">{{ $articulo->seguimientoLabel() }}</p>
                </div>
                <div>
                    <p class="text-xs opacity-60">Descripcion</p>
                    <p class="font-medium">{{ $articulo->descripcion ?: '-' }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-[var(--color-surface)] p-5">
            <p class="text-xs uppercase tracking-wider opacity-60">Situacion actual</p>
            @if($articulo->isSerializado())
                <div class="mt-3 space-y-3">
                    <div>
                        <p class="text-xs opacity-60">Total de series</p>
                        <p class="text-2xl font-semibold">{{ $resumen['total'] }}</p>
                    </div>
                    <div>
                        <p class="text-xs opacity-60">Disponibles</p>
                        <p class="font-medium">{{ $resumen['disponibles'] }}</p>
                    </div>
                    <div>
                        <p class="text-xs opacity-60">Asignadas</p>
                        <p class="font-medium">{{ $resumen['asignados'] }}</p>
                    </div>
                    <div>
                        <p class="text-xs opacity-60">Condicion predominante</p>
                        <p class="font-medium">{{ str_replace('_', ' ', ucfirst($resumen['condicion_predominante'])) }}</p>
                    </div>
                    <div>
                        <p class="text-xs opacity-60">Unidades</p>
                        <p class="font-medium">{{ $resumen['unidades']->isNotEmpty() ? $resumen['unidades']->implode(', ') : '-' }}</p>
                    </div>
                </div>
            @else
                <div class="mt-3 space-y-3">
                    <div>
                        <p class="text-xs opacity-60">Stock disponible</p>
                        <p class="text-2xl font-semibold">{{ number_format($resumen['total'], 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs opacity-60">Estado</p>
                        <p class="font-medium">{{ str_replace('_', ' ', ucfirst($resumen['estado'])) }}</p>
                    </div>
                    <div>
                        <p class="text-xs opacity-60">Entrada acumulada</p>
                        <p class="font-medium">{{ number_format($resumen['entrada'], 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs opacity-60">Salida acumulada</p>
                        <p class="font-medium">{{ number_format($resumen['salida'], 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs opacity-60">Unidades</p>
                        <p class="font-medium">{{ $resumen['unidades']->isNotEmpty() ? $resumen['unidades']->implode(', ') : '-' }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if($articulo->isSerializado())
        <div class="rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-[var(--color-surface)] overflow-hidden">
            <div class="p-4 border-b bg-[var(--color-surface-alt)]">
                <h2 class="font-medium">Series activas</h2>
                <p class="text-xs opacity-70">Consulta operativa de estado, condicion y ubicacion por serie.</p>
            </div>

            <div class="p-4 border-b border-[var(--color-outline)] bg-[var(--color-surface)]">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase mb-1">Buscar serie</label>
                        <input type="text" wire:model.live.debounce.300ms="searchSerie" placeholder="Codigo de serie..." class="w-full rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-[var(--color-surface)] px-3 py-2 text-sm" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase mb-1">Estado</label>
                        <select wire:model.live="estadoFiltro" class="w-full rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-[var(--color-surface)] px-3 py-2 text-sm">
                            <option value="">Todos los estados</option>
                            @foreach($estadosDisponibles as $estado)
                                <option value="{{ $estado }}">{{ str_replace('_', ' ', ucfirst($estado)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase mb-1">Condicion</label>
                        <select wire:model.live="condicionFiltro" class="w-full rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-[var(--color-surface)] px-3 py-2 text-sm">
                            <option value="">Todas las condiciones</option>
                            @foreach($condicionesDisponibles as $condicion)
                                <option value="{{ $condicion }}">{{ str_replace('_', ' ', ucfirst($condicion)) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[980px] text-left">
                    <thead class="bg-[var(--color-surface-alt)] border-b border-[var(--color-outline)]">
                        <tr>
                            <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider">Serie</th>
                            <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider">Estado</th>
                            <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider">Condicion</th>
                            <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider">Unidad</th>
                            <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider">Custodio</th>
                            <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider">Accion</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--color-outline)] bg-[var(--color-surface)]">
                        @forelse($series as $s)
                            @php
                                $custodio = $s->operacionDetalleActual?->operacion?->usuarioDestino;
                            @endphp
                            <tr class="hover:bg-[var(--color-surface-alt)] transition-colors">
                                <td class="px-5 py-4 font-medium">{{ $s->codigo_serie }}</td>
                                <td class="px-5 py-4">{{ str_replace('_', ' ', $s->estado) }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-2">
                                        <select wire:model.defer="condicionesActuales.{{ $s->id }}" class="min-w-[150px] rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-[var(--color-surface)] px-3 py-2 text-sm">
                                            @foreach($condicionesDisponibles as $condicion)
                                                <option value="{{ $condicion }}">{{ str_replace('_', ' ', ucfirst($condicion)) }}</option>
                                            @endforeach
                                        </select>
                                        @can('articulos.manage')
                                            <button type="button" wire:click="guardarCondicion({{ $s->id }})" class="rounded-[var(--radius-radius)] border border-[var(--color-outline)] px-3 py-2 text-xs font-medium hover:bg-[var(--color-surface-alt)]">
                                                Guardar
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                                <td class="px-5 py-4">{{ $s->unidad?->sigla ?? $s->unidad?->nombre ?? '-' }}</td>
                                <td class="px-5 py-4">
                                    {{ $custodio?->nombre_completo ?? 'Sin custodio asignado' }}
                                </td>
                                <td class="px-5 py-4">
                                    <a href="{{ route('articulos.update', $articulo) }}" wire:navigate class="text-sm text-[var(--color-primary)] hover:underline">
                                        Editar articulo
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center opacity-60">No hay series registradas para este articulo.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($series->hasPages())
                <div class="p-4">
                    {{ $series->links('pagination::tailwind') }}
                </div>
            @endif
        </div>

        <div class="rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-[var(--color-surface)] overflow-hidden">
            <div class="p-4 border-b bg-[var(--color-surface-alt)]">
                <h2 class="font-medium">Movimientos recientes</h2>
                <p class="text-xs opacity-70">Ultimas operaciones registradas sobre este articulo.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-[var(--color-surface-alt)] border-b border-[var(--color-outline)]">
                        <tr>
                            <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider">Tipo</th>
                            <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider">Fecha</th>
                            <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider">Serie / Cantidad</th>
                            <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider">Condicion</th>
                            <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider">Observacion</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--color-outline)]">
                        @forelse($movimientosRecientes as $movimiento)
                            <tr>
                                <td class="px-5 py-4">{{ $movimiento->operacion?->tipo ?? '-' }}</td>
                                <td class="px-5 py-4">{{ optional($movimiento->operacion?->fecha)->format('d/m/Y H:i') ?? '-' }}</td>
                                <td class="px-5 py-4">
                                    @if($movimiento->series->count())
                                        {{ $movimiento->series->pluck('serie.codigo_serie')->filter()->implode(', ') }}
                                    @else
                                        {{ $movimiento->cantidad }}
                                    @endif
                                </td>
                                <td class="px-5 py-4">{{ $movimiento->condicion ?? '-' }}</td>
                                <td class="px-5 py-4">{{ $movimiento->observaciones ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center opacity-60">No hay movimientos recientes para este articulo.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-[var(--color-surface)] overflow-hidden">
            <div class="p-4 border-b bg-[var(--color-surface-alt)]">
                <h2 class="font-medium">Existencia por unidad</h2>
                <p class="text-xs opacity-70">Resumen actual del stock disponible y comprometido por unidad.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-[var(--color-surface-alt)] border-b border-[var(--color-outline)]">
                        <tr>
                            <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider">Unidad</th>
                            <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-center">Disponible</th>
                            <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-center">Asignado</th>
                            <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-center">Mantenimiento</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--color-outline)]">
                        @forelse($inventarios as $inventario)
                            <tr>
                                <td class="px-5 py-4">{{ $inventario->unidad?->sigla ?? $inventario->unidad?->nombre ?? '-' }}</td>
                                <td class="px-5 py-4 text-center">{{ number_format((float) $inventario->cantidad_disponible, 2) }}</td>
                                <td class="px-5 py-4 text-center">{{ number_format((float) $inventario->cantidad_asignada, 2) }}</td>
                                <td class="px-5 py-4 text-center">{{ number_format((float) $inventario->cantidad_mantenimiento, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center opacity-60">No hay existencias registradas para este articulo.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-[var(--color-surface)] overflow-hidden">
            <div class="p-4 border-b bg-[var(--color-surface-alt)]">
                <h2 class="font-medium">Movimientos recientes</h2>
                <p class="text-xs opacity-70">Ultimos movimientos que afectaron el stock de este articulo.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-[var(--color-surface-alt)] border-b border-[var(--color-outline)]">
                        <tr>
                            <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider">Tipo</th>
                            <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider">Fecha</th>
                            <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-center">Cantidad</th>
                            <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider">Condicion</th>
                            <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider">Observacion</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--color-outline)]">
                        @forelse($detalles as $d)
                            <tr class="hover:bg-[var(--color-surface-alt)] transition-colors">
                                <td class="px-5 py-4">{{ $d->operacion?->tipo ?? '-' }}</td>
                                <td class="px-5 py-4">{{ optional($d->operacion?->fecha)->format('d/m/Y H:i') ?? ($d->created_at?->format('d/m/Y H:i') ?? '-') }}</td>
                                <td class="px-5 py-4 text-center">{{ $d->cantidad }}</td>
                                <td class="px-5 py-4">{{ $d->condicion ?? '-' }}</td>
                                <td class="px-5 py-4">{{ $d->observaciones ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center opacity-60">No hay movimientos registrados para este articulo.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($detalles->hasPages())
                <div class="p-4">
                    {{ $detalles->links('pagination::tailwind') }}
                </div>
            @endif
        </div>
    @endif
</div>
