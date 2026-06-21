<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <h1 class="text-3xl font-bold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">
            Conflictos
        </h1>

        <div class="flex flex-wrap items-center gap-3">
            <x-form.header_button variant="neutral" href="{{ route('eventos.delete.index') }}" wire:navigate>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Ver eliminados
            </x-form.header_button>

            <x-form.header_button variant="primary" href="{{ route('eventos.create') }}" wire:navigate>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Crear conflicto
            </x-form.header_button>
        </div>
    </div>

    @if (session('success'))
        <div class="p-4 rounded-[var(--radius-radius)] bg-[var(--color-success)]/10 border border-[var(--color-success)] text-[var(--color-success)] flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="p-4 rounded-[var(--radius-radius)] bg-[var(--color-danger)]/10 border border-[var(--color-danger)] text-[var(--color-danger)] flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] rounded-[var(--radius-radius)] shadow-sm border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] p-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] opacity-70">
                Registro de conflictos y operativos con sus fechas de inicio y cierre.
            </p>
            <div class="flex w-fit rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] bg-[var(--color-surface-alt)] dark:bg-[var(--color-surface-dark-alt)] p-1">
                <button type="button" wire:click="$set('viewMode', 'table')" class="px-3 py-2 text-sm font-semibold rounded-[calc(var(--radius-radius)-2px)] {{ $viewMode === 'table' ? 'bg-[var(--color-surface)] text-[var(--color-primary)] shadow-sm' : 'text-[var(--color-on-surface)] opacity-70' }}">
                    Tabla
                </button>
                <button type="button" wire:click="$set('viewMode', 'cards')" class="px-3 py-2 text-sm font-semibold rounded-[calc(var(--radius-radius)-2px)] {{ $viewMode === 'cards' ? 'bg-[var(--color-surface)] text-[var(--color-primary)] shadow-sm' : 'text-[var(--color-on-surface)] opacity-70' }}">
                    Cards
                </button>
            </div>
        </div>
    </div>

    @if($viewMode === 'cards')
        <div>
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3 kiro-stagger">
                @forelse ($eventos as $evento)
                    @php
                        $estadoEvento = $evento->fecha_fin && $evento->fecha_fin->isPast() ? 'Cerrado' : 'Activo';
                        $estadoClasses = $estadoEvento === 'Activo' ? 'bg-emerald-100 text-emerald-700' : 'bg-zinc-100 text-zinc-700';
                    @endphp
                    <div class="rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] p-5 shadow-sm" x-data="{ modalIsOpen: false }">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-xs font-semibold uppercase tracking-wider opacity-60">Conflicto #{{ $evento->id }}</p>
                                <h2 class="mt-1 truncate text-lg font-bold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">{{ $evento->nombre }}</h2>
                            </div>
                            <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold {{ $estadoClasses }}">{{ $estadoEvento }}</span>
                        </div>

                        <div class="mt-5 grid grid-cols-2 gap-3 text-sm">
                            <div class="rounded-[var(--radius-radius)] bg-[var(--color-surface-alt)] dark:bg-[var(--color-surface-dark-alt)] p-3">
                                <p class="text-xs uppercase tracking-wider opacity-60">Fecha inicio</p>
                                <p class="mt-1 font-semibold">{{ $evento->fecha_inicio ? $evento->fecha_inicio->format('Y-m-d') : '-' }}</p>
                            </div>
                            <div class="rounded-[var(--radius-radius)] bg-[var(--color-surface-alt)] dark:bg-[var(--color-surface-dark-alt)] p-3">
                                <p class="text-xs uppercase tracking-wider opacity-60">Fecha fin</p>
                                <p class="mt-1 font-semibold">{{ $evento->fecha_fin ? $evento->fecha_fin->format('Y-m-d') : '-' }}</p>
                            </div>
                        </div>

                        <div class="mt-5 flex flex-wrap items-center gap-2">
                            <x-form.outline_button variant="edit" href="{{ route('eventos.update', $evento) }}" wire:navigate>Editar</x-form.outline_button>
                            <x-form.outline_button type="button" variant="delete" @click="modalIsOpen = true">Eliminar</x-form.outline_button>
                        </div>

                        <x-form.confirm-modal
                            x-model="modalIsOpen"
                            title="Confirmar eliminacion"
                            icon="danger"
                            confirmText="Eliminar conflicto"
                            cancelText="Cancelar"
                            :persistent="false"
                            maxWidth="lg"
                            @confirm="$wire.confirmarEliminacion({{ $evento->id }}); modalIsOpen = false"
                        >
                            <p class="font-medium mb-2">Estas seguro de que deseas eliminar este conflicto?</p>
                            <p class="text-sm opacity-75">El registro se movera a la papelera y podra restaurarse despues.</p>
                        </x-form.confirm-modal>
                    </div>
                @empty
                    <div class="md:col-span-2 xl:col-span-3 rounded-[var(--radius-radius)] border border-dashed border-[var(--color-outline)] p-12 text-center">
                        <p class="font-medium">No hay conflictos registrados</p>
                        <p class="mt-1 text-sm opacity-60">Comienza creando un nuevo conflicto</p>
                    </div>
                @endforelse
            </div>

            @if($eventos->hasPages())
                <div class="mt-4 rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-[var(--color-surface-alt)] px-6 py-4">
                    {{ $eventos->links() }}
                </div>
            @endif
        </div>
    @else
    <div class="bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] rounded-[var(--radius-radius)] shadow-sm border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-[var(--color-surface-alt)] dark:bg-[var(--color-surface-dark-alt)] border-b border-[var(--color-outline)] dark:border-[var(--color-outline-dark)]">
                    <tr>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider">ID</th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider">Nombre</th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider">Fecha inicio</th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider">Fecha fin</th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-[var(--color-outline)] dark:divide-[var(--color-outline-dark)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] kiro-stagger">
                    @forelse ($eventos as $evento)
                        <tr class="hover:bg-[var(--color-surface-alt)] dark:hover:bg-[var(--color-surface-dark-alt)] transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-medium text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">{{ $evento->id }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-semibold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">{{ $evento->nombre }}</span>
                            </td>
                            <td class="px-6 py-4 text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">
                                {{ $evento->fecha_inicio ? $evento->fecha_inicio->format('Y-m-d') : '-' }}
                            </td>
                            <td class="px-6 py-4 text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">
                                {{ $evento->fecha_fin ? $evento->fecha_fin->format('Y-m-d') : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2" x-data="{ modalIsOpen: false }">
                                    <x-form.outline_button variant="edit" href="{{ route('eventos.update', $evento) }}" wire:navigate>
                                        Editar
                                    </x-form.outline_button>

                                    <x-form.outline_button type="button" variant="delete" @click="modalIsOpen = true">
                                        Eliminar
                                    </x-form.outline_button>

                                    <x-form.confirm-modal
                                        x-model="modalIsOpen"
                                        title="Confirmar eliminacion"
                                        icon="danger"
                                        confirmText="Eliminar conflicto"
                                        cancelText="Cancelar"
                                        :persistent="false"
                                        maxWidth="lg"
                                        @confirm="$wire.confirmarEliminacion({{ $evento->id }}); modalIsOpen = false"
                                    >
                                        <p class="font-medium mb-2">Estas seguro de que deseas eliminar este conflicto?</p>
                                        <p class="text-sm opacity-75">El registro se movera a la papelera y podra restaurarse despues.</p>
                                    </x-form.confirm-modal>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <p class="mt-4 font-medium">No hay conflictos registrados</p>
                                <p class="mt-1 text-sm opacity-60">Comienza creando un nuevo conflicto</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($eventos->hasPages())
            <div class="px-6 py-4 border-t border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] bg-[var(--color-surface-alt)] dark:bg-[var(--color-surface-dark-alt)]">
                {{ $eventos->links() }}
            </div>
        @endif
    </div>
    @endif
</div>
