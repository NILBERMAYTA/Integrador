<div class="space-y-6">
    {{-- Header con título y botones --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <h1 class="text-3xl font-bold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">
            Usuarios
        </h1>
        
        <div class="flex flex-wrap items-center gap-3">
            <x-form.header_button variant="export" type="button" wire:click="exportPdf">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 16l4-4m-4 4l-4-4m4 4V4m0 12v4m-7 0h14"/>
                </svg>
                Exportar PDF
            </x-form.header_button>


            
            <x-form.header_button variant="neutral" href="{{ route('users.delete.index') }}" wire:navigate>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Ver Eliminados
            </x-form.header_button>
            
            <x-form.header_button variant="primary" href="{{ route('users.create') }}" wire:navigate>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Crear Usuario
            </x-form.header_button>
        </div>
    </div>

    {{-- Notificaciones --}}
    <x-form.toast_notification :message="session('success')" variant="success" />
    <x-form.toast_notification :message="session('error')" variant="danger" />

    @php
        $rangoOptions = collect($rangos ?? [])->map(fn($r) => ['value' => $r, 'label' => $r])->prepend(['value' => '', 'label' => 'Todos los rangos'])->values();
        $roleOptions = collect($roles ?? [])->map(fn($r) => ['value' => $r, 'label' => $r])->prepend(['value' => '', 'label' => 'Todos los roles'])->values();
    @endphp

    {{-- Barra de filtros --}}
    <x-form.filter-bar>
        <div class="flex-1 min-w-[280px]">
            <x-form.search
                name="search"
                placeholder="Buscar por nombre, apellidos, escalafón…"
                wire:model.live.debounce.300ms="search"
            />
        </div>

        <div class="min-w-[180px]">
            <x-form.combobox
                name="rango"
                label="Rango"
                placeholder="Todos los rangos"
                :options="$rangoOptions"
                :value="$rango"
                wire:model.live="rango"
            />
        </div>

        <div class="min-w-[180px]">
            <x-form.combobox
                name="rol"
                label="Rol"
                placeholder="Todos los roles"
                :options="$roleOptions"
                :value="$rol"
                wire:model.live="rol"
            />
        </div>
    </x-form.filter-bar>

    {{-- Tabla --}}
    <div class="bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] rounded-[var(--radius-radius)] shadow-sm border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-[var(--color-surface-alt)] dark:bg-[var(--color-surface-dark-alt)] border-b border-[var(--color-outline)] dark:border-[var(--color-outline-dark)]">
                    <tr>
                        <th class="px-6 py-4 text-xs font-semibold text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] uppercase tracking-wider">
                            Nro de Escalafón
                        </th>

                        <th class="px-6 py-4 text-xs font-semibold text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] uppercase tracking-wider cursor-pointer select-none hover:bg-[var(--color-surface)] dark:hover:bg-[var(--color-surface-dark)] transition-colors" wire:click="sortBy('apellidos')">
                            <div class="flex items-center gap-2">
                                <span>Apellidos</span>
                                @include('partials.sort-icon', ['field' => 'apellidos', 'sortField' => $sortField, 'sortDirection' => $sortDirection])
                            </div>
                        </th>

                        <th class="px-6 py-4 text-xs font-semibold text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] uppercase tracking-wider cursor-pointer select-none hover:bg-[var(--color-surface)] dark:hover:bg-[var(--color-surface-dark)] transition-colors" wire:click="sortBy('name')">
                            <div class="flex items-center gap-2">
                                <span>Nombre</span>
                                @include('partials.sort-icon', ['field' => 'name', 'sortField' => $sortField, 'sortDirection' => $sortDirection])
                            </div>
                        </th>

                        <th class="px-6 py-4 text-xs font-semibold text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] uppercase tracking-wider">
                            Rango
                        </th>

                        <th class="px-6 py-4 text-xs font-semibold text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] uppercase tracking-wider">
                            Rol
                        </th>

                        <th class="px-6 py-4 text-xs font-semibold text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-[var(--color-outline)] dark:divide-[var(--color-outline-dark)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)]">
                    @forelse ($users as $user)
                        <tr class="hover:bg-[var(--color-surface-alt)] dark:hover:bg-[var(--color-surface-dark-alt)] transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-medium text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">{{ $user->numero_escalafon }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">
                                {{ $user->apellido_paterno }} {{ $user->apellido_materno }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">
                                {{ $user->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-[var(--color-primary)] dark:bg-[var(--color-primary-dark)] text-[var(--color-on-primary)] dark:text-[var(--color-on-primary-dark)]">
                                    {{ $user->rango }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-[var(--color-secondary)] dark:bg-[var(--color-secondary-dark)] text-[var(--color-on-secondary)] dark:text-[var(--color-on-secondary-dark)]">
                                    {{ $user->role }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2" x-data="{ modalIsOpen: false }">
                                    <x-form.outline_button
                                        variant="edit"
                                        href="{{ route('users.update', $user) }}"
                                        wire:navigate
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Editar
                                    </x-form.outline_button>
                                    
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

                                    {{-- Modal reutilizable --}}
                                    <x-form.confirm-modal
                                        x-model="modalIsOpen"
                                        title="Confirmar Eliminación"
                                        icon="danger"
                                        confirmText="Eliminar Usuario"
                                        cancelText="Cancelar"
                                        :persistent="false"
                                        maxWidth="lg"
                                        @confirm="$wire.confirmarEliminacion({{ $user->id }}); modalIsOpen = false"
                                    >
                                        <p class="font-medium text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)] mb-2">
                                            ¿Está seguro de que desea eliminar este usuario?
                                        </p>
                                        <p class="text-sm text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] opacity-75">
                                            Esta acción moverá el usuario a la papelera. 
                                            <br>
                                            Podrá restaurarlo posteriormente desde la lista de usuarios eliminados.
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
                                <p class="mt-4 text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] font-medium">No hay usuarios registrados</p>
                                <p class="mt-1 text-sm text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] opacity-60">Intenta ajustar los filtros de búsqueda</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] bg-[var(--color-surface-alt)] dark:bg-[var(--color-surface-dark-alt)]">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
