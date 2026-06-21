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

    {{-- Contadores --}}
    <x-ui.stats-grid :cols="5">
        <x-ui.stat-card label="Total personal" :value="$stats['total']" icon="users" tone="primary" hint="Usuarios visibles en tu alcance" />
        <x-ui.stat-card label="Policias" :value="$stats['policias']" icon="shield-check" tone="info"
            :progress="$stats['total'] ? round($stats['policias'] / $stats['total'] * 100) : 0" />
        <x-ui.stat-card label="Furrieles" :value="$stats['furrieles']" icon="clipboard-document-check" tone="success"
            :progress="$stats['total'] ? round($stats['furrieles'] / $stats['total'] * 100) : 0" />
        <x-ui.stat-card label="Administradores" :value="$stats['admins']" icon="key" tone="warning"
            :progress="$stats['total'] ? round($stats['admins'] / $stats['total'] * 100) : 0" />
        <x-ui.stat-card label="Sin unidad" :value="$stats['sin_unidad']" icon="exclamation-triangle" :tone="$stats['sin_unidad'] > 0 ? 'danger' : 'neutral'" hint="Personal sin unidad asignada" />
    </x-ui.stats-grid>

    @php
        $rangoOptions = collect($rangos ?? [])->map(fn($r) => ['value' => $r, 'label' => $r])->prepend(['value' => '', 'label' => 'Todos los rangos'])->values();
        $roleOptions = collect($roles ?? [])->map(fn($r) => ['value' => $r, 'label' => $r])->prepend(['value' => '', 'label' => 'Todos los roles'])->values();
        $unidadOptions = collect($unidades ?? [])->map(fn($u) => ['value' => $u->id, 'label' => trim(($u->sigla ? $u->sigla.' - ' : '').$u->nombre)])->prepend(['value' => '', 'label' => 'Todas las unidades'])->values();
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
            <label for="rango" class="block text-xs font-semibold text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] uppercase mb-1">Rango</label>
            <select
                id="rango"
                name="rango"
                wire:model.live="rango"
                class="w-full rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] px-3 py-2 text-sm text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]"
            >
                <option value="">Todos los rangos</option>
                @foreach($rangoOptions as $opt)
                    <option value="{{ $opt['value'] }}">{{ $opt['label'] }}</option>
                @endforeach
            </select>
        </div>

        <div class="min-w-[180px]">
            <label for="rol" class="block text-xs font-semibold text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] uppercase mb-1">Rol</label>
            <select
                id="rol"
                name="rol"
                wire:model.live="rol"
                class="w-full rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] px-3 py-2 text-sm text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]"
            >
                <option value="">Todos los roles</option>
                @foreach($roleOptions as $opt)
                    <option value="{{ $opt['value'] }}">{{ $opt['label'] }}</option>
                @endforeach
            </select>
        </div>

        <div class="min-w-[220px]">
            <label for="unidad" class="block text-xs font-semibold text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] uppercase mb-1">Unidad</label>
            <select
                id="unidad"
                name="unidad"
                wire:model.live="unidad"
                class="w-full rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] px-3 py-2 text-sm text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]"
            >
                @foreach($unidadOptions as $opt)
                    <option value="{{ $opt['value'] }}">{{ $opt['label'] }}</option>
                @endforeach
            </select>
        </div>

        <div class="ml-auto flex rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] bg-[var(--color-surface-alt)] dark:bg-[var(--color-surface-dark-alt)] p-1">
            <button type="button" wire:click="$set('viewMode', 'table')" class="px-3 py-2 text-sm font-semibold rounded-[calc(var(--radius-radius)-2px)] {{ $viewMode === 'table' ? 'bg-[var(--color-surface)] text-[var(--color-primary)] shadow-sm' : 'text-[var(--color-on-surface)] opacity-70' }}">
                Tabla
            </button>
            <button type="button" wire:click="$set('viewMode', 'cards')" class="px-3 py-2 text-sm font-semibold rounded-[calc(var(--radius-radius)-2px)] {{ $viewMode === 'cards' ? 'bg-[var(--color-surface)] text-[var(--color-primary)] shadow-sm' : 'text-[var(--color-on-surface)] opacity-70' }}">
                Cards
            </button>
        </div>
    </x-form.filter-bar>

    @if($viewMode === 'cards')
        <div>
            <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 kiro-stagger">
                @forelse ($users as $user)
                    <div class="group flex h-full flex-col overflow-hidden rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-[var(--color-surface)] shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg dark:border-[var(--color-outline-dark)] dark:bg-[var(--color-surface-dark)]" x-data="{ modalIsOpen: false }">
                        <div class="relative aspect-[4/3] w-full overflow-hidden bg-[var(--color-surface-alt)] dark:bg-[var(--color-surface-dark-alt)]">
                            @if (!empty($user->foto_url))
                                <img
                                    src="{{ $user->foto_url }}"
                                    alt="Foto de {{ $user->nombre_completo }}"
                                    class="h-full w-full object-cover object-top transition duration-300 group-hover:scale-[1.025]"
                                />
                            @else
                                <div class="flex h-full w-full items-center justify-center bg-[var(--color-primary)]/10">
                                    <span class="text-6xl font-semibold text-[var(--color-primary)]">{{ $user->initials() }}</span>
                                </div>
                            @endif

                            <div class="absolute inset-x-0 bottom-0 bg-black/65 px-4 py-3 text-white backdrop-blur-[2px]">
                                <p class="text-xs font-semibold uppercase opacity-75">{{ $user->numero_escalafon ?: 'Sin escalafon' }}</p>
                                <h2 class="mt-1 line-clamp-2 text-lg font-bold leading-tight">
                                    {{ trim($user->name.' '.$user->apellido_paterno.' '.$user->apellido_materno) }}
                                </h2>
                            </div>
                        </div>

                        <div class="flex flex-1 flex-col p-4">
                            <p class="truncate text-sm opacity-70">{{ $user->email ?: 'Sin correo de acceso' }}</p>

                            <div class="mt-3 flex flex-wrap gap-2">
                                <span class="badge badge-primary badge-sm">{{ $user->rango ?: 'Sin rango' }}</span>
                                <span class="badge badge-secondary badge-sm">{{ str_replace('_', ' ', $user->role) }}</span>
                                <span class="badge badge-sm {{ $user->can_login ? 'badge-success' : 'badge-ghost' }}">
                                    {{ $user->can_login ? 'Con acceso' : 'Sin acceso' }}
                                </span>
                            </div>

                            <div class="mt-4 border-t border-[var(--color-outline)] pt-3 dark:border-[var(--color-outline-dark)]">
                                <p class="text-xs uppercase opacity-60">Unidad asignada</p>
                                <p class="mt-1 truncate font-semibold">{{ $user->unidad?->sigla ?? $user->unidad?->nombre ?? '-' }}</p>
                            </div>

                            <div class="mt-auto flex flex-wrap items-center gap-2 pt-4">
                                <x-form.outline_button variant="edit" href="{{ route('users.update', $user) }}" wire:navigate>Editar</x-form.outline_button>
                                @if(auth()->user()?->isAdministradorGeneral())
                                    <x-form.outline_button variant="neutral" href="{{ route('users.transfer', $user) }}" wire:navigate>Transferir</x-form.outline_button>
                                @endif
                                <x-qr-modal
                                    :payload="[
                                        'app' => config('app.name'),
                                        'type' => 'user',
                                        'id' => $user->id,
                                        'numero_escalafon' => $user->numero_escalafon,
                                        'nombre' => trim($user->name.' '.$user->apellido_paterno.' '.$user->apellido_materno),
                                        'rango' => $user->rango,
                                        'unidad' => $user->unidad?->sigla ?? $user->unidad?->nombre,
                                    ]"
                                    title="QR de usuario"
                                    :subtitle="trim($user->name.' '.$user->apellido_paterno.' '.$user->apellido_materno)"
                                    :filename="'qr-usuario-'.$user->id"
                                >
                                    QR
                                </x-qr-modal>
                                <x-form.outline_button type="button" variant="delete" @click="modalIsOpen = true">Eliminar</x-form.outline_button>
                            </div>
                        </div>

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
                            <p class="font-medium mb-2">¿Está seguro de que desea eliminar este usuario?</p>
                            <p class="text-sm opacity-75">Esta acción moverá el usuario a la papelera.</p>
                        </x-form.confirm-modal>
                    </div>
                @empty
                    <div class="md:col-span-2 xl:col-span-3 rounded-[var(--radius-radius)] border border-dashed border-[var(--color-outline)] p-12 text-center">
                        <p class="font-medium">No hay usuarios registrados</p>
                        <p class="mt-1 text-sm opacity-60">Intenta ajustar los filtros de búsqueda</p>
                    </div>
                @endforelse
            </div>

            @if($users->hasPages())
                <div class="mt-4 rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-[var(--color-surface-alt)] px-6 py-4">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    @else
    {{-- Tabla --}}
    <div class="bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] rounded-[var(--radius-radius)] shadow-sm border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-[var(--color-surface-alt)] dark:bg-[var(--color-surface-dark-alt)] border-b border-[var(--color-outline)] dark:border-[var(--color-outline-dark)]">
                    <tr>
                        <th class="px-6 py-4 text-xs font-semibold text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] uppercase tracking-wider">
                            Foto
                        </th>
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
                            Unidad
                        </th>
                        <th class="px-6 py-4 text-xs font-semibold text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-[var(--color-outline)] dark:divide-[var(--color-outline-dark)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] kiro-stagger">
                    @forelse ($users as $user)
                        <tr class="hover:bg-[var(--color-surface-alt)] dark:hover:bg-[var(--color-surface-dark-alt)] transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="h-10 w-10 rounded-full overflow-hidden bg-[var(--color-surface-alt)] dark:bg-[var(--color-surface-dark-alt)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] flex items-center justify-center">
                                    @if (!empty($user->foto_url))
                                        <img src="{{ $user->foto_url }}" alt="Foto de {{ $user->nombre_completo }}" class="h-full w-full object-cover" />
                                    @else
                                        <span class="text-xs text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">
                                            {{ $user->initials() }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-medium text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">{{ $user->numero_escalafon }}</span>
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
                            <td class="px-6 py-4 whitespace-nowrap text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">
                                {{ $user->unidad?->sigla ?? $user->unidad?->nombre ?? '—' }}
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

                                    @if(auth()->user()?->isAdministradorGeneral())
                                        <x-form.outline_button
                                            variant="neutral"
                                            href="{{ route('users.transfer', $user) }}"
                                            wire:navigate
                                        >
                                            Transferir
                                        </x-form.outline_button>
                                    @endif

                                    <x-qr-modal
                                        :payload="[
                                            'app' => config('app.name'),
                                            'type' => 'user',
                                            'id' => $user->id,
                                            'numero_escalafon' => $user->numero_escalafon,
                                            'nombre' => trim($user->name.' '.$user->apellido_paterno.' '.$user->apellido_materno),
                                            'rango' => $user->rango,
                                            'unidad' => $user->unidad?->sigla ?? $user->unidad?->nombre,
                                        ]"
                                        title="QR de usuario"
                                        :subtitle="trim($user->name.' '.$user->apellido_paterno.' '.$user->apellido_materno)"
                                        :filename="'qr-usuario-'.$user->id"
                                    >
                                        QR
                                    </x-qr-modal>
                                    
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
                            <td colspan="8" class="px-6 py-12 text-center">
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
    @endif
</div>

