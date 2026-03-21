<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">
                Articulos
            </h1>
            <p class="text-sm text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] opacity-70">
                Vista general del material registrado, su estado actual y su ubicacion por unidad.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <x-form.header_button variant="export" type="button" wire:click="exportPdf">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 16l4-4m-4 4l-4-4m4 4V4m0 12v4m-7 0h14"/>
                </svg>
                Exportar PDF
            </x-form.header_button>

            <x-form.header_button variant="primary" href="{{ route('articulos.create') }}" wire:navigate>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Crear articulo
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

    @if (session()->has('error'))
        <div class="p-4 rounded-[var(--radius-radius)] bg-[var(--color-danger)]/10 border border-[var(--color-danger)] text-[var(--color-danger)] flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        <div class="rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-[var(--color-surface)] p-4">
            <p class="text-xs uppercase tracking-wider opacity-60">Bueno</p>
            <p class="mt-2 text-2xl font-semibold text-emerald-600">{{ $resumenCondicion['bueno'] }}</p>
        </div>
        <div class="rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-[var(--color-surface)] p-4">
            <p class="text-xs uppercase tracking-wider opacity-60">Con defectos</p>
            <p class="mt-2 text-2xl font-semibold text-amber-600">{{ $resumenCondicion['con_defectos'] }}</p>
        </div>
        <div class="rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-[var(--color-surface)] p-4">
            <p class="text-xs uppercase tracking-wider opacity-60">Malo</p>
            <p class="mt-2 text-2xl font-semibold text-orange-600">{{ $resumenCondicion['malo'] }}</p>
        </div>
        <div class="rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-[var(--color-surface)] p-4">
            <p class="text-xs uppercase tracking-wider opacity-60">Inoperativo</p>
            <p class="mt-2 text-2xl font-semibold text-rose-600">{{ $resumenCondicion['inoperativo'] }}</p>
        </div>
        <div class="rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-[var(--color-surface)] p-4">
            <p class="text-xs uppercase tracking-wider opacity-60">Total armamento</p>
            <p class="mt-2 text-2xl font-semibold text-zinc-700">{{ $resumenCondicion['total'] }}</p>
        </div>
    </div>

    <div class="bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] rounded-[var(--radius-radius)] shadow-sm border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] p-4">
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex-1 min-w-[260px]">
                <label class="block text-xs font-semibold uppercase tracking-wider mb-1.5">Buscar</label>
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input
                        type="text"
                        placeholder="Buscar por nombre, descripcion, serie o unidad..."
                        class="w-full pl-10 pr-4 py-2.5 rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] focus:ring-2 focus:ring-[var(--color-primary)] dark:focus:ring-[var(--color-primary-dark)] focus:border-transparent transition-all"
                        wire:model.live.debounce.300ms="search"
                    />
                </div>
            </div>

            <div class="min-w-[180px]">
                <label class="block text-xs font-semibold uppercase tracking-wider mb-1.5">Categoria</label>
                <select class="w-full rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] px-4 py-2.5 bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]" wire:model.live="categoria">
                    <option value="">Todas las categorias</option>
                    @foreach ($categorias as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="min-w-[160px]">
                <label class="block text-xs font-semibold uppercase tracking-wider mb-1.5">Tipo</label>
                <select class="w-full rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] px-4 py-2.5 bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]" wire:model.live="tipo">
                    <option value="">Todos los tipos</option>
                    <option value="reutilizable">Reutilizable</option>
                    <option value="consumible">Consumible</option>
                </select>
            </div>

            <div class="min-w-[180px]">
                <label class="block text-xs font-semibold uppercase tracking-wider mb-1.5">Unidad</label>
                <select class="w-full rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] px-4 py-2.5 bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]" wire:model.live="unidad">
                    <option value="">Todas las unidades</option>
                    @foreach ($unidades as $itemUnidad)
                        <option value="{{ $itemUnidad->id }}">{{ $itemUnidad->sigla ?: $itemUnidad->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="min-w-[180px]">
                <label class="block text-xs font-semibold uppercase tracking-wider mb-1.5">Estado</label>
                <select class="w-full rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] px-4 py-2.5 bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]" wire:model.live="estado">
                    <option value="">Todos los estados</option>
                    <option value="disponible">Disponible</option>
                    <option value="bajo_stock">Bajo stock</option>
                    <option value="agotado">Agotado</option>
                    <option value="asignado">Asignado</option>
                    <option value="en_mantenimiento">En mantenimiento</option>
                    <option value="observado">Observado</option>
                    <option value="inoperativo">Inoperativo</option>
                    <option value="dado_de_baja">Dado de baja</option>
                </select>
            </div>

            <div class="min-w-[150px] self-end">
                <x-form.header_button variant="neutral" type="button" wire:click="clearFilters">
                    Limpiar filtros
                </x-form.header_button>
            </div>
        </div>
    </div>

    <div class="bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] rounded-[var(--radius-radius)] shadow-sm border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1120px] text-left">
                <thead class="bg-[var(--color-surface-alt)] dark:bg-[var(--color-surface-dark-alt)] border-b border-[var(--color-outline)] dark:border-[var(--color-outline-dark)]">
                    <tr>
                        <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider">ID</th>
                        <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider">
                            <button type="button" wire:click="sortBy('operativo_nombre')" class="inline-flex items-center gap-1 hover:text-[var(--color-primary)] transition-colors">
                                Nombre
                            </button>
                        </th>
                        <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider">Estado</th>
                        <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider">Condicion</th>
                        <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider">Unidad</th>
                        <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider">Tipo</th>
                        <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider">Cantidad / Serie</th>
                        <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-right">Acciones</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-[var(--color-outline)] dark:divide-[var(--color-outline-dark)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)]">
                    @forelse ($articulos as $row)
                        @php
                            $estadoClasses = match($row['estado']) {
                                'disponible' => 'bg-emerald-100 text-emerald-700',
                                'bajo_stock' => 'bg-amber-100 text-amber-700',
                                'agotado' => 'bg-rose-100 text-rose-700',
                                'asignado' => 'bg-sky-100 text-sky-700',
                                'en_mantenimiento' => 'bg-orange-100 text-orange-700',
                                'observado' => 'bg-yellow-100 text-yellow-700',
                                'inoperativo' => 'bg-rose-100 text-rose-700',
                                'dado_de_baja' => 'bg-zinc-200 text-zinc-700',
                                default => 'bg-zinc-100 text-zinc-700',
                            };

                            $condicionClasses = match($row['condicion']) {
                                'operativo', 'bueno' => 'bg-emerald-100 text-emerald-700',
                                'con_defectos', 'regular', 'bajo_stock' => 'bg-amber-100 text-amber-700',
                                'malo', 'danado' => 'bg-orange-100 text-orange-700',
                                'inoperativo', 'sin_stock' => 'bg-rose-100 text-rose-700',
                                default => 'bg-zinc-100 text-zinc-700',
                            };
                        @endphp
                        <tr class="hover:bg-[var(--color-surface-alt)] dark:hover:bg-[var(--color-surface-dark-alt)] transition-colors">
                            <td class="px-5 py-4 whitespace-nowrap text-sm font-medium">
                                {{ $row['id'] }}
                            </td>
                            <td class="px-5 py-4 min-w-[260px]">
                                <div class="font-medium text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">
                                    {{ $row['nombre'] }}
                                </div>
                                <div class="mt-1 text-xs text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] opacity-70">
                                    {{ $row['categoria'] }}
                                </div>
                                <div class="mt-1 text-xs text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] opacity-60">
                                    {{ $row['detalle_principal'] }}
                                </div>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $estadoClasses }}">
                                    {{ str_replace('_', ' ', ucfirst($row['estado'])) }}
                                </span>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $condicionClasses }}">
                                    {{ str_replace('_', ' ', ucfirst($row['condicion'])) }}
                                </span>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-sm">
                                {{ $row['unidad'] }}
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-[var(--color-primary)]/10 border border-[var(--color-primary)] text-[var(--color-primary)]">
                                    {{ ucfirst($row['tipo']) }}
                                </span>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-sm font-medium">
                                {{ $row['cantidad_serie'] }}
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end gap-2" x-data="{ openMenu: false, modalIsOpen: false }">
                                    <x-form.outline_button variant="details" href="{{ route('articulos.show', $row['articulo_id']) }}" wire:navigate>
                                        Ver
                                    </x-form.outline_button>

                                    <div class="relative">
                                        <button
                                            type="button"
                                            @click="openMenu = !openMenu"
                                            class="inline-flex items-center gap-1 rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] px-3 py-2 text-sm font-medium hover:bg-[var(--color-surface-alt)] dark:hover:bg-[var(--color-surface-dark-alt)]"
                                        >
                                            Mas
                                        </button>

                                        <div
                                            x-show="openMenu"
                                            x-transition
                                            @click.outside="openMenu = false"
                                            class="absolute right-0 z-20 mt-2 w-44 rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] shadow-lg text-left"
                                        >
                                            <a href="{{ route('articulos.update', $row['articulo_id']) }}" wire:navigate class="block w-full px-4 py-2 text-left text-sm hover:bg-[var(--color-surface-alt)] dark:hover:bg-[var(--color-surface-dark-alt)]">
                                                Editar
                                            </a>
                                            <button type="button" wire:click="abrirAjuste({{ $row['articulo_id'] }})" @click="openMenu = false" class="block w-full px-4 py-2 text-left text-sm hover:bg-[var(--color-surface-alt)] dark:hover:bg-[var(--color-surface-dark-alt)]">
                                                Ajustar
                                            </button>
                                            <button type="button" @click="modalIsOpen = true; openMenu = false" class="block w-full px-4 py-2 text-left text-sm text-[var(--color-danger)] hover:bg-[var(--color-danger)]/10">
                                                Eliminar
                                            </button>
                                        </div>
                                    </div>

                                    <x-form.confirm-modal
                                        x-model="modalIsOpen"
                                        title="Confirmar eliminacion"
                                        icon="danger"
                                        confirmText="Eliminar articulo"
                                        cancelText="Cancelar"
                                        :persistent="false"
                                        maxWidth="lg"
                                        @confirm="$wire.confirmarEliminacion({{ $row['articulo_id'] }}); modalIsOpen = false"
                                    >
                                        <p class="font-medium mb-2">Estas seguro de que deseas eliminar este articulo?</p>
                                        <p class="text-sm opacity-75">Esta accion movera el articulo a la papelera.</p>
                                    </x-form.confirm-modal>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <p class="mt-4 font-medium">No hay material registrado</p>
                                <p class="mt-1 text-sm opacity-60">Intenta ajustar los filtros de busqueda</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($articulos->hasPages())
            <div class="px-6 py-4 border-t border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] bg-[var(--color-surface-alt)] dark:bg-[var(--color-surface-dark-alt)]">
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
                        <h3 class="text-lg font-semibold text-[var(--color-on-surface-strong)]">Ajuste de stock - {{ $ajusteArticulo->nombre }}</h3>
                        <button wire:click="closeAjuste" class="rounded px-2 py-1 text-sm text-[var(--color-on-surface)]">X</button>
                    </div>
                    <livewire:articulos.ajuste-stock :articulo="$ajusteArticulo" />
                </div>
            </div>
        </div>
    @endif
</div>
