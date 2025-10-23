<div class="space-y-6">
    {{-- Header con título y botones --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">
                Usuarios Eliminados
            </h1>
            <p class="mt-1 text-sm text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] opacity-70">
                Gestiona los usuarios que han sido eliminados del sistema
            </p>
        </div>
        <a 
            href="{{ route('users.index') }}" 
            wire:navigate 
            class="inline-flex items-center gap-2 whitespace-nowrap rounded-[var(--radius-radius)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] px-5 py-2.5 text-sm font-medium tracking-wide text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] transition-all hover:bg-[var(--color-surface-alt)] dark:hover:bg-[var(--color-surface-dark-alt)] hover:border-[var(--color-outline-strong)] dark:hover:border-[var(--color-outline-dark-strong)] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--color-primary)] dark:focus-visible:outline-[var(--color-primary-dark)] active:opacity-100 active:outline-offset-0 disabled:cursor-not-allowed disabled:opacity-75"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Volver a Usuarios
        </a>
    </div>

    {{-- Mensajes de éxito/error --}}
    @if (session()->has('success'))
        <div class="p-4 rounded-[var(--radius-radius)] bg-[var(--color-success)]/10 border border-[var(--color-success)] text-[var(--color-success)] flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="p-4 rounded-[var(--radius-radius)] bg-[var(--color-danger)]/10 border border-[var(--color-danger)] text-[var(--color-danger)] flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('error') }}</span>
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
                        placeholder="Buscar por nombre, apellidos, escalafón…"
                        class="w-full pl-10 pr-4 py-2.5 rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] focus:ring-2 focus:ring-[var(--color-primary)] dark:focus:ring-[var(--color-primary-dark)] focus:border-transparent transition-all placeholder:text-[var(--color-on-surface)] placeholder:dark:text-[var(--color-on-surface-dark)] placeholder:opacity-50"
                        wire:model.live.debounce.300ms="search"
                    />
                </div>
            </div>

            <div class="min-w-[180px]">
                <select class="w-full rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] px-4 py-2.5 bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] focus:ring-2 focus:ring-[var(--color-primary)] dark:focus:ring-[var(--color-primary-dark)] focus:border-transparent transition-all" wire:model.live="rango">
                    <option value="">Todos los rangos</option>
                    @foreach ($rangos as $r)
                        <option value="{{ $r }}">{{ $r }}</option>
                    @endforeach
                </select>
            </div>

            <div class="min-w-[180px]">
                <select class="w-full rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] px-4 py-2.5 bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] focus:ring-2 focus:ring-[var(--color-primary)] dark:focus:ring-[var(--color-primary-dark)] focus:border-transparent transition-all" wire:model.live="rol">
                    <option value="">Todos los roles</option>
                    @foreach ($roles as $r)
                        <option value="{{ $r }}">{{ $r }}</option>
                    @endforeach
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
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">Nro de Escalafón</th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider cursor-pointer select-none hover:bg-[var(--color-surface)] dark:hover:bg-[var(--color-surface-dark)] transition-colors text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]" wire:click="sortBy('apellidos')">
                            <div class="flex items-center gap-2">
                                <span>Apellidos</span>
                                @include('partials.sort-icon', ['field' => 'apellidos', 'sortField' => $sortField, 'sortDirection' => $sortDirection])
                            </div>
                        </th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider cursor-pointer select-none hover:bg-[var(--color-surface)] dark:hover:bg-[var(--color-surface-dark)] transition-colors text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]" wire:click="sortBy('name')">
                            <div class="flex items-center gap-2">
                                <span>Nombre</span>
                                @include('partials.sort-icon', ['field' => 'name', 'sortField' => $sortField, 'sortDirection' => $sortDirection])
                            </div>
                        </th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">Rango</th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">Rol</th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider cursor-pointer select-none hover:bg-[var(--color-surface)] dark:hover:bg-[var(--color-surface-dark)] transition-colors text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]" wire:click="sortBy('deleted_at')">
                            <div class="flex items-center gap-2">
                                <span>Eliminado</span>
                                @include('partials.sort-icon', ['field' => 'deleted_at', 'sortField' => $sortField, 'sortDirection' => $sortDirection])
                            </div>
                        </th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">Acciones</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-[var(--color-outline)] dark:divide-[var(--color-outline-dark)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)]">
                    @forelse ($users as $user)
                        <tr class="hover:bg-[var(--color-surface-alt)] dark:hover:bg-[var(--color-surface-dark-alt)] transition-colors opacity-75">
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
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">
                                {{ optional($user->deleted_at)->diffForHumans() }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div x-data="{ openRestore: false, openDelete: false }" class="flex items-center gap-2">
                                    {{-- Botón: Restaurar --}}
                                    <button 
                                        type="button"
                                        @click="openRestore = true"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[var(--radius-radius)] border border-[var(--color-success)] bg-[var(--color-success)]/10 text-sm font-medium text-[var(--color-success)] hover:bg-[var(--color-success)] hover:text-[var(--color-on-success)] focus:outline-none focus:ring-2 focus:ring-[var(--color-success)] focus:ring-offset-1 transition-all"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                        </svg>
                                        Restaurar
                                    </button>

                                    {{-- Botón: Eliminar permanente --}}
                                    <button 
                                        type="button"
                                        @click="openDelete = true"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[var(--radius-radius)] border border-[var(--color-danger)] bg-[var(--color-danger)]/10 text-sm font-medium text-[var(--color-danger)] hover:bg-[var(--color-danger)] hover:text-[var(--color-on-danger)] focus:outline-none focus:ring-2 focus:ring-[var(--color-danger)] focus:ring-offset-1 transition-all"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                        Eliminar Permanentemente
                                    </button>

                                    {{-- MODAL: Confirmar Restauración (x-confirm-modal) --}}
                                    <x-form.confirm-modal
                                        x-model="openRestore"
                                        title="Confirmar Restauración"
                                        icon="info"
                                        confirmText="Restaurar Usuario"
                                        cancelText="Cancelar"
                                        :persistent="false"
                                        maxWidth="lg"
                                        @confirm="$wire.restaurar({{ $user->id }}); openRestore = false"
                                    >
                                        <p class="font-medium text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)] mb-2">
                                            ¿Desea restaurar al usuario <span class="font-semibold">{{ $user->name }}</span>?
                                        </p>
                                        <p class="text-sm opacity-75 text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">
                                            Volverá a la lista de usuarios activos con toda su información.
                                        </p>
                                    </x-form.confirm-modal>

                                    {{-- MODAL: Confirmar Eliminación Permanente (x-confirm-modal) --}}
                                    <x-form.confirm-modal
                                        x-model="openDelete"
                                        title="Confirmar Eliminación Permanente"
                                        icon="danger"
                                        confirmText="Eliminar Permanentemente"
                                        cancelText="Cancelar"
                                        :persistent="false"
                                        maxWidth="lg"
                                        @confirm="$wire.eliminarPermanentemente({{ $user->id }}); openDelete = false"
                                    >
                                        <p class="font-medium text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)] mb-2">
                                            ⚠️ Esta acción es <span class="font-semibold">irreversible</span>.
                                        </p>
                                        <p class="text-sm opacity-75 text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">
                                            ¿Está seguro de eliminar definitivamente al usuario <span class="font-semibold">{{ $user->name }}</span>?
                                        </p>
                                    </x-form.confirm-modal>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                <p class="mt-4 text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] font-medium">No hay usuarios eliminados</p>
                                <p class="mt-1 text-sm text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] opacity-60">Todos los usuarios están activos</p>
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
