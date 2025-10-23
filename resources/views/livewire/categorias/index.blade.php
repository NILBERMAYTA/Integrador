<div class="space-y-6">
    {{-- Header con título y botón --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <h1 class="text-3xl font-bold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">
            Categorías
        </h1>
        
        <div class="flex flex-wrap items-center gap-3">
            <a 
                href="{{ route('categorias.delete.index') }}" 
                wire:navigate 
                class="inline-flex items-center gap-2 whitespace-nowrap rounded-[var(--radius-radius)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] px-5 py-2.5 text-sm font-medium tracking-wide text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] transition-all hover:bg-[var(--color-surface-alt)] dark:hover:bg-[var(--color-surface-dark-alt)] hover:border-[var(--color-outline-strong)] dark:hover:border-[var(--color-outline-dark-strong)] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--color-primary)] dark:focus-visible:outline-[var(--color-primary-dark)] active:opacity-100 active:outline-offset-0 disabled:cursor-not-allowed disabled:opacity-75"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Ver Eliminados
            </a>

            <a 
                href="{{ route('categorias.create') }}" 
                wire:navigate 
                class="inline-flex items-center gap-2 whitespace-nowrap rounded-[var(--radius-radius)] bg-[var(--color-primary)] dark:bg-[var(--color-primary-dark)] border border-[var(--color-primary)] dark:border-[var(--color-primary-dark)] px-6 py-2.5 text-sm font-medium tracking-wide text-[var(--color-on-primary)] dark:text-[var(--color-on-primary-dark)] transition-all hover:opacity-90 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--color-primary)] dark:focus-visible:outline-[var(--color-primary-dark)] active:opacity-100 active:outline-offset-0 disabled:cursor-not-allowed disabled:opacity-75"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nueva Categoría
            </a>
        </div>
    </div>

    {{-- Mensajes de sesión --}}
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

    {{-- Tabla --}}
    <div class="bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] rounded-[var(--radius-radius)] shadow-sm border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-[var(--color-surface-alt)] dark:bg-[var(--color-surface-dark-alt)] border-b border-[var(--color-outline)] dark:border-[var(--color-outline-dark)]">
                    <tr>
                        <th class="px-6 py-4 text-xs font-semibold text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] uppercase tracking-wider">
                            ID
                        </th>
                        <th class="px-6 py-4 text-xs font-semibold text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] uppercase tracking-wider">
                            Nombre
                        </th>
                        <th class="px-6 py-4 text-xs font-semibold text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] uppercase tracking-wider">
                            Descripción
                        </th>
                        <th class="px-6 py-4 text-xs font-semibold text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-[var(--color-outline)] dark:divide-[var(--color-outline-dark)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)]">
                    @forelse ($categorias as $categoria)
                        <tr class="hover:bg-[var(--color-surface-alt)] dark:hover:bg-[var(--color-surface-dark-alt)] transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-medium text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">{{ $categoria->id }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-semibold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">{{ $categoria->nombre }}</span>
                            </td>
                            <td class="px-6 py-4 text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">
                                {{ $categoria->descripcion ?? '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2" x-data="{ modalIsOpen: false }">
                                    <a 
                                        href="{{ route('categorias.update', $categoria) }}" 
                                        wire:navigate
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] text-sm font-medium text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] hover:bg-[var(--color-surface-alt)] dark:hover:bg-[var(--color-surface-dark-alt)] hover:border-[var(--color-outline-strong)] dark:hover:border-[var(--color-outline-dark-strong)] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)] dark:focus:ring-[var(--color-primary-dark)] focus:ring-offset-1 transition-all"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Editar
                                    </a>
                                    
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

                                    {{-- Modal de confirmación - EXACTAMENTE IGUAL QUE USUARIOS --}}
                                    <x-form.confirm-modal
                                        x-model="modalIsOpen"
                                        title="Confirmar Eliminación"
                                        icon="danger"
                                        confirmText="Eliminar Categoría"
                                        cancelText="Cancelar"
                                        :persistent="false"
                                        maxWidth="lg"
                                        @confirm="$wire.confirmarEliminacion({{ $categoria->id }}); modalIsOpen = false"
                                    >
                                        <p class="font-medium text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)] mb-2">
                                            ¿Está seguro de que desea eliminar esta categoría?
                                        </p>
                                        <p class="text-sm text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] opacity-75">
                                            <strong>{{ $categoria->nombre }}</strong>
                                            <br>
                                            Se moverá a la papelera.
                                            <br>
                                            Podrá restaurarla posteriormente desde la lista de categorías eliminadas.
                                        </p>
                                    </x-form.confirm-modal>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                                <p class="mt-4 text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] font-medium">No hay categorías registradas</p>
                                <p class="mt-1 text-sm text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] opacity-60">Comienza creando una nueva categoría</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($categorias->hasPages())
            <div class="px-6 py-4 border-t border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] bg-[var(--color-surface-alt)] dark:bg-[var(--color-surface-dark-alt)]">
                {{ $categorias->links() }}
            </div>
        @endif
    </div>
</div>