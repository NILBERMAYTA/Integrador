<div class="space-y-6">
    {{-- Header con título y botones --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <h1 class="text-3xl font-bold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">
            Artículos
        </h1>
        
        <div class="flex flex-wrap items-center gap-3">
            {{-- Exportar PDF --}}
            <button     
                type="button"
                wire:click="exportPdf"
                class="inline-flex items-center gap-2 whitespace-nowrap rounded-radius bg-surface-alt border border-surface-alt 
                    px-4 py-2 text-sm font-medium tracking-wide text-on-surface-strong 
                    transition hover:opacity-75 text-center 
                    focus-visible:outline-2 focus-visible:outline-offset-2 
                    focus-visible:outline-surface-alt active:opacity-100 active:outline-offset-0 
                    disabled:opacity-75 disabled:cursor-not-allowed 
                    dark:bg-surface-dark-alt dark:border-surface-dark-alt 
                    dark:text-on-surface-dark-strong dark:focus-visible:outline-surface-dark-alt"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 16l4-4m-4 4l-4-4m4 4V4m0 12v4m-7 0h14"/>
                </svg>
                Exportar PDF
            </button>

            {{-- Ver Inventario --}}
            <a 
                href="{{ route('articulos.inventario') }}" 
                wire:navigate 
                class="inline-flex items-center gap-2 whitespace-nowrap rounded-[var(--radius-radius)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] px-5 py-2.5 text-sm font-medium tracking-wide text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] transition-all hover:bg-[var(--color-surface-alt)] dark:hover:bg-[var(--color-surface-dark-alt)] hover:border-[var(--color-outline-strong)] dark:hover:border-[var(--color-outline-dark-strong)] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--color-primary)] dark:focus-visible:outline-[var(--color-primary-dark)] active:opacity-100 active:outline-offset-0 disabled:cursor-not-allowed disabled:opacity-75"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 4v16h14V4M7 14h10M7 10h10"/>
                </svg>
                Ver Inventario
            </a>

            {{-- Ver Eliminados (ruta placeholder) --}}
            <a 
                href="{{ route('articulos.delete.index') }}" 
                wire:navigate 
                class="inline-flex items-center gap-2 whitespace-nowrap rounded-[var(--radius-radius)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] px-5 py-2.5 text-sm font-medium tracking-wide text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] transition-all hover:bg-[var(--color-surface-alt)] dark:hover:bg-[var(--color-surface-dark-alt)] hover:border-[var(--color-outline-strong)] dark:hover:border-[var(--color-outline-dark-strong)] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--color-primary)] dark:focus-visible:outline-[var(--color-primary-dark)] active:opacity-100 active:outline-offset-0 disabled:cursor-not-allowed disabled:opacity-75"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Ver Eliminados
            </a>
            
            {{-- Crear Artículo --}}
            <a 
                href="{{ route('articulos.create') }}" 
                wire:navigate 
                class="inline-flex items-center gap-2 whitespace-nowrap rounded-[var(--radius-radius)] bg-[var(--color-primary)] dark:bg-[var(--color-primary-dark)] border border-[var(--color-primary)] dark:border-[var(--color-primary-dark)] px-6 py-2.5 text-sm font-medium tracking-wide text-[var(--color-on-primary)] dark:text-[var(--color-on-primary-dark)] transition-all hover:opacity-90 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--color-primary)] dark:focus-visible:outline-[var(--color-primary-dark)] active:opacity-100 active:outline-offset-0 disabled:cursor-not-allowed disabled:opacity-75"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Crear Artículo
            </a>
        </div>
    </div>

    {{-- Mensaje de éxito --}}
    @if (session()->has('success'))
        <div class="p-4 rounded-[var(--radius-radius)] bg-[var(--color-success)]/10 border border-[var(--color-success)] text-[var(--color-success)] flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Barra de filtros --}}
    <div class="bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] rounded-[var(--radius-radius)] shadow-sm border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] p-4">
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex-1 min-w-[280px]">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input
                        type="text"
                        placeholder="Buscar por nombre, categoría…"
                        class="w-full pl-10 pr-4 py-2.5 rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] focus:ring-2 focus:ring-[var(--color-primary)] dark:focus:ring-[var(--color-primary-dark)] focus:border-transparent transition-all placeholder:text-[var(--color-on-surface)] placeholder:dark:text-[var(--color-on-surface-dark)] placeholder:opacity-50"
                        wire:model.live.debounce.300ms="search"
                    />
                </div>
            </div>

            <div class="min-w-[180px]">
                <select class="w-full rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] px-4 py-2.5 bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] focus:ring-2 focus:ring-[var(--color-primary)] dark:focus:ring-[var(--color-primary-dark)] focus:border-transparent transition-all" wire:model.live="categoria">
                    <option value="">Todas las categorías</option>
                    @foreach ($categorias as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="min-w-[160px]">
                <select class="w-full rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] px-4 py-2.5 bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] focus:ring-2 focus:ring-[var(--color-primary)] dark:focus:ring-[var(--color-primary-dark)] focus:border-transparent transition-all" wire:model.live="tipo">
                    <option value="">Todos los tipos</option>
                    <option value="reutilizable">Reutilizable</option>
                    <option value="consumible">Consumible</option>
                </select>
            </div>

            <div class="min-w-[160px]">
                <select class="w-full rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] px-4 py-2.5 bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] focus:ring-2 focus:ring-[var(--color-primary)] dark:focus:ring-[var(--color-primary-dark)] focus:border-transparent transition-all" wire:model.live="seguimiento">
                    <option value="">Todos los seguimientos</option>
                    <option value="serie">Por serie</option>
                    <option value="cantidad">Por cantidad</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] rounded-[var(--radius-radius)] shadow-sm border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-[var(--color-surface-alt)] dark:bg-[var(--color-surface-dark-alt)] border-b border-[var(--color-outline)] dark:border-[var(--color-outline-dark)]">
                    <tr>
                        <th class="px-6 py-4 text-xs font-semibold text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] uppercase tracking-wider">
                            Categoría
                        </th>
                        <th class="px-6 py-4 text-xs font-semibold text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] uppercase tracking-wider">
                            Nombre
                        </th>
                        <th class="px-6 py-4 text-xs font-semibold text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] uppercase tracking-wider">
                            Tipo
                        </th>
                        <th class="px-6 py-4 text-xs font-semibold text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] uppercase tracking-wider">
                            Seguimiento
                        </th>
                        <th class="px-6 py-4 text-xs font-semibold text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] uppercase tracking-wider">
                            Unidad
                        </th>
                        <th class="px-6 py-4 text-xs font-semibold text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-[var(--color-outline)] dark:divide-[var(--color-outline-dark)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)]">
                    @forelse ($articulos as $a)
                        <tr class="hover:bg-[var(--color-surface-alt)] dark:hover:bg-[var(--color-surface-dark-alt)] transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ $a->categoria->nombre ?? '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-medium text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">{{ $a->nombre }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-[var(--color-primary)]/10 border border-[var(--color-primary)] text-[var(--color-primary)] dark:bg-[var(--color-primary-dark)]/10 dark:border-[var(--color-primary-dark)] dark:text-[var(--color-primary-dark)]">
                                    {{ ucfirst($a->tipo) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-[var(--color-secondary)]/10 border border-[var(--color-secondary)] text-[var(--color-secondary)] dark:bg-[var(--color-secondary-dark)]/10 dark:border-[var(--color-secondary-dark)] dark:text-[var(--color-secondary-dark)]">
                                    {{ ucfirst($a->seguimiento) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">
                                {{ $a->unidad_medida ?? '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2" x-data="{ modalIsOpen: false }">
                                    {{-- Editar --}}
                                    <a 
                                        href="{{ route('articulos.update', $a) }}" 
                                        wire:navigate
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] text-sm font-medium text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] hover:bg-[var(--color-surface-alt)] dark:hover:bg-[var(--color-surface-dark-alt)] hover:border-[var(--color-outline-strong)] dark:hover:border-[var(--color-outline-dark-strong)] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)] dark:focus:ring-[var(--color-primary-dark)] focus:ring-offset-1 transition-all"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Editar
                                    </a>

                                    {{-- Eliminar (modal) --}}
                                    <button 
                                        type="button"
                                        @click="modalIsOpen = true"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[var(--radius-radius)] border border-[var(--color-danger)] bg-[var(--color-danger)]/10 text-sm font-medium text-[var(--color-danger)] hover:bg-[var(--color-danger)] hover:text-[var(--color-on-danger)] focus:outline-none focus:ring-2 focus:ring-[var(--color-danger)] focus:ring-offset-1 transition-all"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Eliminar
                                    </button>

                                    {{-- Modal reutilizable --}}
                                    <x-form.confirm-modal
                                        x-model="modalIsOpen"
                                        title="Confirmar Eliminación"
                                        icon="danger"
                                        confirmText="Eliminar Artículo"
                                        cancelText="Cancelar"
                                        :persistent="false"
                                        maxWidth="lg"
                                        @confirm="$wire.confirmarEliminacion({{ $a->id }}); modalIsOpen = false"
                                    >
                                        <p class="font-medium text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)] mb-2">
                                            ¿Está seguro de que desea eliminar este artículo?
                                        </p>
                                        <p class="text-sm text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] opacity-75">
                                            Esta acción moverá el artículo a la papelera. 
                                            <br>
                                            Podrá restaurarlo posteriormente desde la lista de artículos eliminados.
                                        </p>
                                    </x-form.confirm-modal>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                </svg>
                                <p class="mt-4 text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] font-medium">No hay artículos registrados</p>
                                <p class="mt-1 text-sm text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] opacity-60">Intenta ajustar los filtros de búsqueda</p>
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
</div>
