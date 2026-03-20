<div class="w-full max-w-7xl mx-auto p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">{{ $articulo->nombre }}</h1>
            <p class="text-sm text-muted">Categoria: {{ $articulo->categoria?->nombre ?? '—' }} • Tipo: {{ ucfirst($articulo->tipo) }} • Gestion: {{ $articulo->seguimientoLabel() }}</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" wire:click="exportPdf" class="px-3 py-2 rounded border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] text-sm hover:bg-[var(--color-surface-alt)] dark:hover:bg-[var(--color-surface-dark-alt)]">
                Exportar PDF
            </button>
            <a href="{{ route('articulos.index') }}" wire:navigate class="px-3 py-2 rounded bg-[var(--color-surface)] border">Volver</a>
        </div>
    </div>

    <x-form.toast_notification :message="session('success')" variant="success" />
    <x-form.toast_notification :message="session('error')" variant="danger" />

    <div class="rounded-[var(--radius-radius)] border border-[var(--color-outline)] overflow-hidden bg-[var(--color-surface)]">
        @if($articulo->isSerializado())
            <div class="p-4 border-b bg-[var(--color-surface-alt)]">
                <h2 class="font-medium">Seguimiento de armamento / series</h2>
                <p class="text-xs text-[var(--color-on-surface)] opacity-70">Estado logistico y condicion fisica actual por cada serie.</p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-3 p-4 border-b border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] bg-[var(--color-surface)]">
                <div class="rounded-[var(--radius-radius)] border p-4">
                    <p class="text-xs uppercase opacity-70">Buenas</p>
                    <p class="text-2xl font-semibold text-emerald-600">{{ $resumen['cond_bueno'] }}</p>
                </div>
                <div class="rounded-[var(--radius-radius)] border p-4">
                    <p class="text-xs uppercase opacity-70">Con defectos</p>
                    <p class="text-2xl font-semibold text-amber-600">{{ $resumen['cond_defectos'] }}</p>
                </div>
                <div class="rounded-[var(--radius-radius)] border p-4">
                    <p class="text-xs uppercase opacity-70">Malas</p>
                    <p class="text-2xl font-semibold text-orange-600">{{ $resumen['cond_malo'] }}</p>
                </div>
                <div class="rounded-[var(--radius-radius)] border p-4">
                    <p class="text-xs uppercase opacity-70">Inoperativas</p>
                    <p class="text-2xl font-semibold text-rose-600">{{ $resumen['cond_inoperativo'] }}</p>
                </div>
                <div class="rounded-[var(--radius-radius)] border p-4">
                    <p class="text-xs uppercase opacity-70">Total series</p>
                    <p class="text-2xl font-semibold">{{ $resumen['total'] }}</p>
                </div>
            </div>

            <div class="p-4 border-b border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] bg-[var(--color-surface)]">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase mb-1">Buscar serie</label>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="searchSerie"
                            placeholder="Codigo de serie..."
                            class="w-full rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] bg-[var(--color-surface)] px-3 py-2 text-sm"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase mb-1">Estado logistico</label>
                        <select wire:model.live="estadoFiltro" class="w-full rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] bg-[var(--color-surface)] px-3 py-2 text-sm">
                            <option value="">Todos los estados</option>
                            @foreach($estadosDisponibles as $estado)
                                <option value="{{ $estado }}">{{ str_replace('_', ' ', ucfirst($estado)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase mb-1">Condicion fisica</label>
                        <select wire:model.live="condicionFiltro" class="w-full rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] bg-[var(--color-surface)] px-3 py-2 text-sm">
                            <option value="">Todas las condiciones</option>
                            @foreach($condicionesDisponibles as $condicion)
                                <option value="{{ $condicion }}">{{ str_replace('_', ' ', ucfirst($condicion)) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[1560px] text-left">
                    <thead class="bg-[var(--color-surface-alt)] dark:bg-[var(--color-surface-dark-alt)] border-b border-[var(--color-outline)] dark:border-[var(--color-outline-dark)]">
                        <tr>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider">Serie</th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider">Estado logistico</th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider">Condicion fisica</th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider">Unidad</th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider">Custodio actual</th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider">Ultima inspeccion</th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider">Ultima incidencia</th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider">Ultimo mantenimiento</th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider">Observaciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--color-outline)] dark:divide-[var(--color-outline-dark)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)]">
                        @forelse($series as $s)
                            @php
                                $ultimoMantenimiento = $s->mantenimientos->first();
                                $ultimaInspeccion = $s->inspecciones->first();
                                $ultimaIncidencia = $s->incidencias->first();
                                $custodio = $s->operacionDetalleActual?->operacion?->usuarioDestino;
                                $estadoClasses = match($s->estado) {
                                    'disponible' => 'bg-emerald-100 text-emerald-700',
                                    'asignado' => 'bg-sky-100 text-sky-700',
                                    'en_mantenimiento' => 'bg-amber-100 text-amber-700',
                                    'observado' => 'bg-orange-100 text-orange-700',
                                    'inoperativo' => 'bg-rose-100 text-rose-700',
                                    'dado_de_baja' => 'bg-zinc-200 text-zinc-700',
                                    default => 'bg-zinc-100 text-zinc-700',
                                };
                                $condicionClasses = match($s->condicion_actual) {
                                    'bueno' => 'bg-emerald-100 text-emerald-700',
                                    'con_defectos' => 'bg-amber-100 text-amber-700',
                                    'malo' => 'bg-orange-100 text-orange-700',
                                    'inoperativo' => 'bg-rose-100 text-rose-700',
                                    default => 'bg-zinc-100 text-zinc-700',
                                };
                            @endphp
                            <tr class="hover:bg-[var(--color-surface-alt)] dark:hover:bg-[var(--color-surface-dark-alt)] transition-colors align-top">
                                <td class="px-6 py-4 whitespace-nowrap font-medium">{{ $s->codigo_serie }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $estadoClasses }}">
                                        {{ str_replace('_', ' ', $s->estado) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 min-w-[220px]">
                                    <div class="flex items-center gap-2">
                                        <select
                                            wire:model.defer="condicionesActuales.{{ $s->id }}"
                                            class="min-w-[150px] rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] bg-[var(--color-surface)] px-3 py-2 text-sm"
                                        >
                                            @foreach($condicionesDisponibles as $condicion)
                                                <option value="{{ $condicion }}">{{ str_replace('_', ' ', ucfirst($condicion)) }}</option>
                                            @endforeach
                                        </select>
                                        @can('articulos.manage')
                                            <button
                                                type="button"
                                                wire:click="guardarCondicion({{ $s->id }})"
                                                class="rounded-[var(--radius-radius)] border border-[var(--color-outline)] px-3 py-2 text-xs font-medium hover:bg-[var(--color-surface-alt)]"
                                            >
                                                Guardar
                                            </button>
                                        @endcan
                                    </div>
                                    <div class="mt-2">
                                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $condicionClasses }}">
                                            {{ str_replace('_', ' ', $s->condicion_actual ?? 'bueno') }}
                                        </span>
                                    </div>
                                    @error("condicionesActuales.$s->id")
                                        <div class="mt-1 text-xs text-[var(--color-danger)]">{{ $message }}</div>
                                    @enderror
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $s->unidad?->sigla ?? $s->unidad?->nombre ?? '—' }}</td>
                                <td class="px-6 py-4">
                                    @if($custodio)
                                        <div class="text-sm font-medium">{{ $custodio->nombre_completo }}</div>
                                        <div class="text-xs opacity-70">{{ $custodio->numero_escalafon ?? 'Sin escalafon' }}</div>
                                    @else
                                        <span class="text-sm opacity-70">Sin custodio asignado</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($ultimaInspeccion)
                                        <div class="text-sm font-medium">{{ ucfirst($ultimaInspeccion->resultado) }}</div>
                                        <div class="text-xs opacity-70">{{ optional($ultimaInspeccion->realizada_en)->format('d/m/Y H:i') }}</div>
                                    @else
                                        <span class="text-sm opacity-70">Sin inspecciones</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($ultimaIncidencia)
                                        <div class="text-sm font-medium">{{ $ultimaIncidencia->tipo?->nombre ?? 'Incidencia' }}</div>
                                        <div class="text-xs opacity-70">{{ optional($ultimaIncidencia->fecha)->format('d/m/Y H:i') }}</div>
                                    @else
                                        <span class="text-sm opacity-70">Sin incidencias</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($ultimoMantenimiento)
                                        <div class="text-sm font-medium">{{ ucfirst($ultimoMantenimiento->tipo) }}</div>
                                        <div class="text-xs opacity-70">
                                            {{ optional($ultimoMantenimiento->fecha_inicio)->format('d/m/Y') }}
                                            @if($ultimoMantenimiento->fecha_fin)
                                                · cerrado
                                            @else
                                                · abierto
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-sm opacity-70">Sin mantenimientos</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 max-w-[260px]">
                                    <div class="text-sm">{{ $s->observaciones ?: '—' }}</div>
                                    @if($ultimaIncidencia?->descripcion)
                                        <div class="mt-2 text-xs opacity-70">Incidencia: {{ $ultimaIncidencia->descripcion }}</div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-12 text-center text-[var(--color-on-surface)] opacity-60">No hay unidades registradas para este articulo.</td>
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
        @else
            <div class="p-4 border-b bg-[var(--color-surface-alt)]">
                <h2 class="font-medium">Movimientos</h2>
                <p class="text-xs text-[var(--color-on-surface)] opacity-70">Listado de operaciones que afectan el stock por cantidad para este artículo.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-[var(--color-surface-alt)] dark:bg-[var(--color-surface-dark-alt)] border-b border-[var(--color-outline)] dark:border-[var(--color-outline-dark)]">
                        <tr>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider">Tipo</th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider">Fecha</th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider">Cantidad</th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider">Condicion</th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider">Observaciones</th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--color-outline)] dark:divide-[var(--color-outline-dark)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)]">
                        @forelse($detalles as $d)
                            <tr class="hover:bg-[var(--color-surface-alt)] dark:hover:bg-[var(--color-surface-dark-alt)] transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">{{ $d->operacion?->tipo ?? '—' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ optional($d->operacion?->fecha)->format('d/m/Y H:i') ?? ($d->created_at?->format('d/m/Y H:i') ?? '—') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $d->cantidad }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $d->condicion ?? '—' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $d->observaciones ?? '—' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <a href="#" class="text-[var(--color-primary)]">Ver operación</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-[var(--color-on-surface)] opacity-60">No hay movimientos registrados para este artículo.</td>
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
        @endif
    </div>
</div>
