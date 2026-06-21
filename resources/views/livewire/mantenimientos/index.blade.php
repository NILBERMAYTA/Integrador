<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <h1 class="text-3xl font-bold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">
            Mantenimientos
        </h1>

        <div class="flex flex-wrap items-center gap-3">
            <x-form.header_button variant="neutral" href="{{ route('mantenimientos.delete.index') }}" wire:navigate>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Ver Eliminados
            </x-form.header_button>

            <x-form.header_button variant="primary" href="{{ route('mantenimientos.create') }}" wire:navigate>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Crear Mantenimiento
            </x-form.header_button>
        </div>
    </div>

    {{-- Notificaciones --}}
    <x-form.toast_notification :message="session('success')" variant="success" />
    <x-form.toast_notification :message="session('error')" variant="danger" />

    {{-- Contadores --}}
    <x-ui.stats-grid :cols="5">
        <x-ui.stat-card label="Total" :value="$stats['total']" icon="wrench-screwdriver" tone="primary" />
        <x-ui.stat-card label="Abiertos" :value="$stats['abiertos']" icon="clock" :tone="$stats['abiertos'] > 0 ? 'warning' : 'neutral'" hint="Sin fecha de cierre" />
        <x-ui.stat-card label="Cerrados" :value="$stats['cerrados']" icon="check-circle" tone="success">
            <div class="mt-4 flex items-center gap-3">
                <div class="radial-progress text-emerald-500" style="--value:{{ $stats['pct_cerrados'] }}; --size:2.75rem; --thickness:4px;" role="progressbar" aria-valuenow="{{ $stats['pct_cerrados'] }}">
                    <span class="text-[10px] font-semibold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">{{ $stats['pct_cerrados'] }}%</span>
                </div>
                <span class="text-xs text-[var(--color-on-surface)]/65 dark:text-[var(--color-on-surface-dark)]/65">tasa de cierre</span>
            </div>
        </x-ui.stat-card>
        <x-ui.stat-card label="Preventivos" :value="$stats['preventivos']" icon="shield-check" tone="info"
            :progress="$stats['total'] ? round($stats['preventivos'] / $stats['total'] * 100) : 0" />
        <x-ui.stat-card label="Correctivos" :value="$stats['correctivos']" icon="exclamation-triangle" tone="danger"
            :progress="$stats['total'] ? round($stats['correctivos'] / $stats['total'] * 100) : 0" />
    </x-ui.stats-grid>

    @php
        $tipoOptions = collect($tipos ?? [])->map(fn($t) => ['value' => $t, 'label' => ucfirst($t)]);
    @endphp

    {{-- Barra de filtros --}}
    <x-form.filter-bar>
        <div class="flex-1 min-w-[280px]">
            <x-form.search
                name="search"
                placeholder="Buscar por articulo o serie..."
                wire:model.live.debounce.300ms="search"
            />
        </div>

        <div class="min-w-[180px]">
            <x-form.combobox
                name="tipo"
                label="Tipo"
                placeholder="Todos"
                :options="$tipoOptions"
                wire:model.live="tipo"
            />
        </div>
    </x-form.filter-bar>

    {{-- Tabla --}}
    <div class="bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] rounded-[var(--radius-radius)] shadow-sm border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-[var(--color-surface-alt)] dark:bg-[var(--color-surface-dark-alt)] border-b border-[var(--color-outline)] dark:border-[var(--color-outline-dark)]">
                    <tr>
                        <th class="px-6 py-4 text-xs font-semibold text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] uppercase tracking-wider cursor-pointer select-none hover:bg-[var(--color-surface)] dark:hover:bg-[var(--color-surface-dark)] transition-colors" wire:click="sortBy('articulo')">
                            Articulo
                        </th>
                        <th class="px-6 py-4 text-xs font-semibold text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] uppercase tracking-wider">Serie</th>
                        <th class="px-6 py-4 text-xs font-semibold text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] uppercase tracking-wider cursor-pointer select-none hover:bg-[var(--color-surface)] dark:hover:bg-[var(--color-surface-dark)] transition-colors" wire:click="sortBy('tipo')">
                            Tipo
                        </th>
                        <th class="px-6 py-4 text-xs font-semibold text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] uppercase tracking-wider cursor-pointer select-none hover:bg-[var(--color-surface)] dark:hover:bg-[var(--color-surface-dark)] transition-colors" wire:click="sortBy('fecha_inicio')">
                            Inicio
                        </th>
                        <th class="px-6 py-4 text-xs font-semibold text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] uppercase tracking-wider cursor-pointer select-none hover:bg-[var(--color-surface)] dark:hover:bg-[var(--color-surface-dark)] transition-colors" wire:click="sortBy('fecha_fin')">
                            Fin
                        </th>
                        <th class="px-6 py-4 text-xs font-semibold text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] uppercase tracking-wider">
                            Estado
                        </th>
                        <th class="px-6 py-4 text-xs font-semibold text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-[var(--color-outline)] dark:divide-[var(--color-outline-dark)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] kiro-stagger">
                    @forelse ($mantenimientos as $m)
                        @php $enCurso = is_null($m->fecha_fin); @endphp
                        <tr class="hover:bg-[var(--color-surface-alt)] dark:hover:bg-[var(--color-surface-dark-alt)] transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">
                                {{ $m->articulo->nombre ?? 'Articulo' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">
                                {{ $m->serie->codigo_serie ?? '--' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-[var(--color-secondary)]/10 text-[var(--color-secondary)]">
                                    {{ ucfirst($m->tipo) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">
                                {{ optional($m->fecha_inicio)->format('Y-m-d H:i') ?? '--' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">
                                {{ optional($m->fecha_fin)->format('Y-m-d H:i') ?? '--' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($enCurso)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">En curso</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">Finalizado</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div
                                    class="flex items-center gap-2"
                                    x-data="{
                                        modalIsOpen: false,
                                        cerrarOpen: false,
                                        cierreFecha: '',
                                        cierreCosto: '',
                                        cierreDescripcion: '',
                                        init() {
                                            const d = new Date();
                                            const local = new Date(d.getTime() - d.getTimezoneOffset() * 60000).toISOString().slice(0,16);
                                            this.cierreFecha = local;
                                        }
                                    }"
                                >
                                    <x-form.outline_button
                                        variant="edit"
                                        href="{{ route('mantenimientos.update', $m) }}"
                                        wire:navigate
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Editar
                                    </x-form.outline_button>

                                    @if($enCurso)
                                        <x-form.outline_button
                                            type="button"
                                            variant="success"
                                            @click="cerrarOpen = true"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            Cerrar
                                        </x-form.outline_button>
                                    @endif
                                    
                                    <x-form.outline_button
                                        type="button"
                                        variant="delete"
                                        @click="modalIsOpen = true"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Eliminar
                                    </x-form.outline_button>

                                    <x-form.confirm-modal
                                        x-model="modalIsOpen"
                                        title="Confirmar Eliminacion"
                                        icon="danger"
                                        confirmText="Eliminar Mantenimiento"
                                        cancelText="Cancelar"
                                        :persistent="false"
                                        maxWidth="lg"
                                        @confirm="$wire.confirmarEliminacion({{ $m->id }}); modalIsOpen = false"
                                    >
                                        <p class="font-medium text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)] mb-2">
                                            Esta seguro de eliminar este mantenimiento?
                                        </p>
                                        <p class="text-sm text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] opacity-75">
                                            Esta accion movera el registro a la papelera y podra restaurarse luego.
                                        </p>
                                    </x-form.confirm-modal>

                                    @if($enCurso)
                                        <x-form.confirm-modal
                                            x-model="cerrarOpen"
                                            title="Cerrar mantenimiento"
                                            icon="info"
                                            confirmText="Cerrar mantenimiento"
                                            cancelText="Cancelar"
                                            :persistent="false"
                                            maxWidth="lg"
                                            @confirm="$wire.cerrarMantenimiento({{ $m->id }}, cierreFecha, cierreDescripcion, cierreCosto); cerrarOpen = false"
                                        >
                                            <div class="space-y-4">
                                                <p class="font-medium text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">
                                                    Define la fecha y resumen del cierre. El estado pasara a CONCLUIDO.
                                                </p>
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    <div class="flex flex-col gap-2">
                                                        <label class="text-sm font-medium text-on-surface dark:text-on-surface-dark">Fecha y hora de cierre</label>
                                                        <input
                                                            type="datetime-local"
                                                            x-model="cierreFecha"
                                                            class="w-full rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] px-3 py-2 text-sm text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] focus:outline-hidden focus:ring-2 focus:ring-[var(--color-primary)] dark:focus:ring-[var(--color-primary-dark)] focus:ring-offset-2 focus:ring-offset-[var(--color-surface)] dark:focus:ring-offset-[var(--color-surface-dark)]"
                                                        />
                                                    </div>
                                                    <div class="flex flex-col gap-2">
                                                        <label class="text-sm font-medium text-on-surface dark:text-on-surface-dark">Costo (opcional)</label>
                                                        <input
                                                            type="number"
                                                            min="0"
                                                            step="0.01"
                                                            x-model="cierreCosto"
                                                            class="w-full rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] px-3 py-2 text-sm text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] focus:outline-hidden focus:ring-2 focus:ring-[var(--color-primary)] dark:focus:ring-[var(--color-primary-dark)] focus:ring-offset-2 focus:ring-offset-[var(--color-surface)] dark:focus:ring-offset-[var(--color-surface-dark)]"
                                                        />
                                                    </div>
                                                </div>
                                                <div class="flex flex-col gap-2">
                                                    <label class="text-sm font-medium text-on-surface dark:text-on-surface-dark">Descripcion final</label>
                                                    <textarea
                                                        x-model="cierreDescripcion"
                                                        rows="3"
                                                        class="w-full rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] px-3 py-2 text-sm text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] focus:outline-hidden focus:ring-2 focus:ring-[var(--color-primary)] dark:focus:ring-[var(--color-primary-dark)] focus:ring-offset-2 focus:ring-offset-[var(--color-surface)] dark:focus:ring-offset-[var(--color-surface-dark)]"
                                                        placeholder="Notas del cierre, pruebas realizadas, etc."
                                                    ></textarea>
                                                </div>
                                            </div>
                                        </x-form.confirm-modal>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                </svg>
                                <p class="mt-4 text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] font-medium">No hay mantenimientos registrados</p>
                                <p class="mt-1 text-sm text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] opacity-60">Intenta ajustar los filtros de busqueda</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($mantenimientos->hasPages())
            <div class="px-6 py-4 border-t border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] bg-[var(--color-surface-alt)] dark:bg-[var(--color-surface-dark-alt)]">
                {{ $mantenimientos->links() }}
            </div>
        @endif
    </div>
</div>
