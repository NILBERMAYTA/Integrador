<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <h1 class="text-3xl font-bold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">
            Articulos
        </h1>
        
        <div class="flex flex-wrap items-center gap-3">
            <x-form.header_button variant="export" type="button" wire:click="exportPdf">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 16l4-4m-4 4l-4-4m4 4V4m0 12v4m-7 0h14"/>
                </svg>
                Exportar PDF
            </x-form.header_button>

            <x-form.header_button variant="neutral" href="{{ route('articulos.inventario') }}" wire:navigate>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 4v16h14V4M7 14h10M7 10h10"/>
                </svg>
                Ver Inventario
            </x-form.header_button>

            <x-form.header_button variant="neutral" href="{{ route('articulos.delete.index') }}" wire:navigate>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Ver Eliminados
            </x-form.header_button>
            
            <x-form.header_button variant="primary" href="{{ route('articulos.create') }}" wire:navigate>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Crear Articulo
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
            <div class="flex-1 min-w-[280px]">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input
                        type="text"
                        placeholder="Buscar por nombre o categoria..."
                        class="w-full pl-10 pr-4 py-2.5 rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] focus:ring-2 focus:ring-[var(--color-primary)] dark:focus:ring-[var(--color-primary-dark)] focus:border-transparent transition-all placeholder:text-[var(--color-on-surface)] placeholder:dark:text-[var(--color-on-surface-dark)] placeholder:opacity-50"
                        wire:model.live.debounce.300ms="search"
                    />
                </div>
            </div>

            <div class="min-w-[180px]">
                <select class="w-full rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] px-4 py-2.5 bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]" wire:model.live="categoria">
                    <option value="">Todas las categorias</option>
                    @foreach ($categorias as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="min-w-[160px]">
                <select class="w-full rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] px-4 py-2.5 bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]" wire:model.live="tipo">
                    <option value="">Todos los tipos</option>
                    <option value="reutilizable">Reutilizable</option>
                    <option value="consumible">Consumible</option>
                </select>
            </div>
        </div>
    </div>

    <div class="bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] rounded-[var(--radius-radius)] shadow-sm border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-[var(--color-surface-alt)] dark:bg-[var(--color-surface-dark-alt)] border-b border-[var(--color-outline)] dark:border-[var(--color-outline-dark)]">
                    <tr>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider">Categoria</th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider">Nombre</th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider">Tipo</th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider">Gestion</th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-[var(--color-outline)] dark:divide-[var(--color-outline-dark)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)]">
                    @forelse ($articulos as $a)
                        <tr class="hover:bg-[var(--color-surface-alt)] dark:hover:bg-[var(--color-surface-dark-alt)] transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">{{ $a->categoria->nombre ?? '—' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-medium text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">{{ $a->nombre }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-[var(--color-primary)]/10 border border-[var(--color-primary)] text-[var(--color-primary)]">
                                    {{ ucfirst($a->tipo) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">
                                {{ $a->seguimientoLabel() }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2" x-data="{ modalIsOpen: false, modalAjuste: false }">
                                    <x-form.outline_button variant="edit" href="{{ route('articulos.update', $a) }}" wire:navigate>
                                        Editar
                                    </x-form.outline_button>

                                    <x-form.outline_button variant="details" href="{{ route('articulos.show', $a) }}" wire:navigate>
                                        Detalles
                                    </x-form.outline_button>

                                    <x-form.outline_button type="button" variant="adjust" wire:click="abrirAjuste({{ $a->id }})">
                                        Ajuste
                                    </x-form.outline_button>

                                    <x-form.outline_button type="button" variant="delete" @click="modalIsOpen = true">
                                        Eliminar
                                    </x-form.outline_button>

                                    <x-form.confirm-modal
                                        x-model="modalIsOpen"
                                        title="Confirmar Eliminacion"
                                        icon="danger"
                                        confirmText="Eliminar Articulo"
                                        cancelText="Cancelar"
                                        :persistent="false"
                                        maxWidth="lg"
                                        @confirm="$wire.confirmarEliminacion({{ $a->id }}); modalIsOpen = false"
                                    >
                                        <p class="font-medium mb-2">¿Esta seguro de que desea eliminar este articulo?</p>
                                        <p class="text-sm opacity-75">Esta accion movera el articulo a la papelera.</p>
                                    </x-form.confirm-modal>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <p class="mt-4 font-medium">No hay articulos registrados</p>
                                <p class="mt-1 text-sm opacity-60">Intenta ajustar los filtros de busqueda</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($articulos->hasPages())
            <div class="px-6 py-4 border-top border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] bg-[var(--color-surface-alt)] dark:bg-[var(--color-surface-dark-alt)]">
                {{ $articulos->links() }}
            </div>
        @endif
    </div>

    @if($ajusteArticulo)
        <div class="fixed inset-0 z-50 flex items-start justify-center p-6">
            <div class="absolute inset-0 bg-black/30 backdrop-blur-sm" wire:click="closeAjuste"></div>
            <div class="relative w-full max-w-2xl">
                <div class="bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] rounded-[var(--radius-radius)] shadow-sm border border-[var(--color-outline)] p-4">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-lg font-semibold text-[var(--color-on-surface-strong)]">Ajuste de Stock — {{ $ajusteArticulo->nombre }}</h3>
                        <button wire:click="closeAjuste" class="rounded px-2 py-1 text-sm text-[var(--color-on-surface)]">✕</button>
                    </div>
                    <livewire:articulos.ajuste-stock :articulo="$ajusteArticulo" />
                </div>
            </div>
        </div>
    @endif
</div>
