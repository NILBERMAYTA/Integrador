<div class="space-y-6" x-data="{ showCreate: false }">
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

            <x-form.header_button variant="primary" type="button" @click="showCreate = true">
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

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5 kiro-stagger">
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
            <p class="text-xs uppercase tracking-wider opacity-60">Total registros</p>
            <p class="mt-2 text-2xl font-semibold text-zinc-700">{{ $resumenCondicion['total'] }}</p>
        </div>
    </div>

    <div class="bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] rounded-[var(--radius-radius)] shadow-sm border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] p-4">
        <div class="flex flex-wrap items-end gap-3">
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
                    <option value="perdido">Perdido</option>
                    <option value="robado">Robado</option>
                </select>
            </div>

            <div class="min-w-[150px] self-end">
                <x-form.header_button variant="neutral" type="button" wire:click="clearFilters">
                    Limpiar filtros
                </x-form.header_button>
            </div>

            <div class="ml-auto flex rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] bg-[var(--color-surface-alt)] dark:bg-[var(--color-surface-dark-alt)] p-1">
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
            <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 kiro-stagger">
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
                            'perdido' => 'bg-zinc-200 text-zinc-700',
                            'robado' => 'bg-rose-100 text-rose-700',
                            'activo' => 'bg-emerald-100 text-emerald-700',
                            'sin_series' => 'bg-zinc-100 text-zinc-700',
                            default => 'bg-zinc-100 text-zinc-700',
                        };

                        $condicionClasses = match($row['condicion']) {
                            'operativo', 'bueno' => 'bg-emerald-100 text-emerald-700',
                            'con_defectos', 'regular', 'bajo_stock' => 'bg-amber-100 text-amber-700',
                            'malo', 'danado' => 'bg-orange-100 text-orange-700',
                            'inoperativo', 'sin_stock' => 'bg-rose-100 text-rose-700',
                            'con_observaciones' => 'bg-amber-100 text-amber-700',
                            default => 'bg-zinc-100 text-zinc-700',
                        };
                    @endphp
                    <div class="group flex h-full flex-col overflow-hidden rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-[var(--color-surface)] shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg dark:border-[var(--color-outline-dark)] dark:bg-[var(--color-surface-dark)]" x-data="{ modalIsOpen: false }">
                        <div class="relative aspect-[4/3] w-full overflow-hidden bg-[var(--color-surface-alt)] dark:bg-[var(--color-surface-dark-alt)]">
                            @if(!empty($row['foto_url']))
                                <img
                                    src="{{ $row['foto_url'] }}"
                                    alt="Imagen de {{ $row['nombre'] }}"
                                    class="h-full w-full object-contain p-3 transition duration-300 group-hover:scale-[1.025]"
                                />
                            @else
                                <div class="flex h-full w-full items-center justify-center bg-[var(--color-primary)]/10">
                                    <span class="text-7xl font-semibold text-[var(--color-primary)]">{{ mb_strtoupper(mb_substr($row['nombre'], 0, 1)) }}</span>
                                </div>
                            @endif

                            <div class="absolute left-3 top-3">
                                <span class="badge border-0 bg-black/65 text-white backdrop-blur-sm">{{ $row['id'] }}</span>
                            </div>

                            <div class="absolute right-3 top-3">
                                <span class="badge badge-primary">{{ ucfirst($row['tipo']) }}</span>
                            </div>
                        </div>

                        <div class="flex flex-1 flex-col p-4">
                            <h2 class="line-clamp-2 text-lg font-bold leading-tight text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">{{ $row['nombre'] }}</h2>
                            <p class="mt-1 truncate text-sm opacity-70">{{ $row['categoria'] }}</p>

                            <div class="mt-3 flex flex-wrap gap-2">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $estadoClasses }}">{{ str_replace('_', ' ', ucfirst($row['estado'])) }}</span>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $condicionClasses }}">{{ str_replace('_', ' ', ucfirst($row['condicion'])) }}</span>
                            </div>

                            <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                                <div class="min-w-0 border-r border-[var(--color-outline)] pr-3 dark:border-[var(--color-outline-dark)]">
                                    <p class="text-xs uppercase opacity-60">Unidad</p>
                                    <p class="mt-1 truncate font-semibold">{{ $row['unidad'] }}</p>
                                </div>
                                <div class="min-w-0 pl-1">
                                    <p class="text-xs uppercase opacity-60">Cantidad / Serie</p>
                                    <p class="mt-1 truncate font-semibold" title="{{ $row['cantidad_serie'] }}">{{ $row['cantidad_serie'] }}</p>
                                </div>
                            </div>

                            <p class="mt-4 line-clamp-2 text-sm opacity-70">{{ $row['detalle_principal'] }}</p>
                            <p class="mt-1 line-clamp-2 text-xs opacity-60">{{ $row['detalle_secundario'] }}</p>

                            <div class="mt-auto flex flex-wrap items-center gap-2 border-t border-[var(--color-outline)] pt-4 dark:border-[var(--color-outline-dark)]">
                                @if($row['row_type'] === 'reutilizable')
                                    <x-form.outline_button variant="details" href="{{ route('articulos.show', $row['articulo_id']) }}" wire:navigate>Ver serie</x-form.outline_button>
                                @else
                                    <x-form.outline_button variant="details" href="{{ route('articulos.show', $row['articulo_id']) }}" wire:navigate>Ver stock</x-form.outline_button>
                                    <x-form.outline_button variant="success" type="button" wire:click="abrirAjuste({{ $row['articulo_id'] }})">Ingreso</x-form.outline_button>
                                @endif
                                <x-qr-modal
                                :payload="[
                                    'app' => config('app.name'),
                                    'type' => $row['row_type'] === 'reutilizable' ? 'serie' : 'articulo',
                                    'id' => $row['serie_id'] ?? $row['articulo_id'],
                                    'articulo_id' => $row['articulo_id'],
                                    'codigo' => $row['codigo_serie'] ?? $row['id'],
                                    'nombre' => $row['nombre'],
                                    'categoria' => $row['categoria'],
                                    'tipo' => $row['tipo'],
                                    'estado' => $row['estado'],
                                    'unidad' => $row['unidad'],
                                    'url' => route('articulos.show', $row['articulo_id']),
                                ]"
                                :title="$row['row_type'] === 'reutilizable' ? 'QR de serie' : 'QR de articulo'"
                                :subtitle="$row['row_type'] === 'reutilizable' ? $row['nombre'].' - '.$row['codigo_serie'] : $row['nombre']"
                                :filename="$row['row_type'] === 'reutilizable' ? 'qr-serie-'.$row['serie_id'] : 'qr-articulo-'.$row['articulo_id']"
                                >
                                    QR
                                </x-qr-modal>
                                <x-form.outline_button variant="edit" href="{{ route('articulos.update', $row['articulo_id']) }}" wire:navigate>Editar</x-form.outline_button>
                                <x-form.outline_button type="button" variant="delete" @click="modalIsOpen = true">Eliminar</x-form.outline_button>
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
                @empty
                    <div class="md:col-span-2 xl:col-span-3 rounded-[var(--radius-radius)] border border-dashed border-[var(--color-outline)] p-12 text-center">
                        <p class="font-medium">No hay material registrado</p>
                        <p class="mt-1 text-sm opacity-60">Intenta ajustar los filtros de busqueda</p>
                    </div>
                @endforelse
            </div>

            @if($articulos->hasPages())
                <div class="mt-4 rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-[var(--color-surface-alt)] px-6 py-4">
                    {{ $articulos->links() }}
                </div>
            @endif
        </div>
    @else
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

                <tbody class="divide-y divide-[var(--color-outline)] dark:divide-[var(--color-outline-dark)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] kiro-stagger">
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
                                'perdido' => 'bg-zinc-200 text-zinc-700',
                                'robado' => 'bg-rose-100 text-rose-700',
                                'activo' => 'bg-emerald-100 text-emerald-700',
                                'sin_series' => 'bg-zinc-100 text-zinc-700',
                                default => 'bg-zinc-100 text-zinc-700',
                            };

                            $condicionClasses = match($row['condicion']) {
                                'operativo', 'bueno' => 'bg-emerald-100 text-emerald-700',
                                'con_defectos', 'regular', 'bajo_stock' => 'bg-amber-100 text-amber-700',
                                'malo', 'danado' => 'bg-orange-100 text-orange-700',
                                'inoperativo', 'sin_stock' => 'bg-rose-100 text-rose-700',
                                'con_observaciones' => 'bg-amber-100 text-amber-700',
                                default => 'bg-zinc-100 text-zinc-700',
                            };
                        @endphp
                        <tr class="hover:bg-[var(--color-surface-alt)] dark:hover:bg-[var(--color-surface-dark-alt)] transition-colors">
                            <td class="px-5 py-4 whitespace-nowrap text-sm font-medium">
                                {{ $row['id'] }}
                            </td>
                            <td class="px-5 py-4 min-w-[260px]">
                                <div class="flex items-center gap-3">
                                    <div class="h-11 w-11 shrink-0 overflow-hidden rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-[var(--color-surface-alt)] flex items-center justify-center">
                                        @if(!empty($row['foto_url']))
                                            <img src="{{ $row['foto_url'] }}" alt="Imagen de {{ $row['nombre'] }}" class="h-full w-full object-cover" />
                                        @else
                                            <span class="text-xs font-semibold opacity-60">{{ mb_strtoupper(mb_substr($row['nombre'], 0, 1)) }}</span>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-medium text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">
                                            {{ $row['nombre'] }}
                                        </div>
                                        <div class="mt-1 text-xs text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] opacity-70">
                                            {{ $row['categoria'] }}
                                        </div>
                                        <div class="mt-1 text-xs text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] opacity-60">
                                            {{ $row['detalle_principal'] }}
                                        </div>
                                    </div>
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
                                        {{ $row['row_type'] === 'reutilizable' ? 'Ver series' : 'Ver stock' }}
                                    </x-form.outline_button>

                                    <x-qr-modal
                                        :payload="[
                                            'app' => config('app.name'),
                                            'type' => $row['row_type'] === 'reutilizable' ? 'serie' : 'articulo',
                                            'id' => $row['serie_id'] ?? $row['articulo_id'],
                                            'articulo_id' => $row['articulo_id'],
                                            'codigo' => $row['codigo_serie'] ?? $row['id'],
                                            'nombre' => $row['nombre'],
                                            'categoria' => $row['categoria'],
                                            'tipo' => $row['tipo'],
                                            'estado' => $row['estado'],
                                            'unidad' => $row['unidad'],
                                            'url' => route('articulos.show', $row['articulo_id']),
                                        ]"
                                        :title="$row['row_type'] === 'reutilizable' ? 'QR de serie' : 'QR de articulo'"
                                        :subtitle="$row['row_type'] === 'reutilizable' ? $row['nombre'].' - '.$row['codigo_serie'] : $row['nombre']"
                                        :filename="$row['row_type'] === 'reutilizable' ? 'qr-serie-'.$row['serie_id'] : 'qr-articulo-'.$row['articulo_id']"
                                    >
                                        QR
                                    </x-qr-modal>

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
                                            @if($row['row_type'] === 'reutilizable')
                                                <a href="{{ route('articulos.update', $row['articulo_id']) }}?tab=series" wire:navigate class="block w-full px-4 py-2 text-left text-sm hover:bg-[var(--color-surface-alt)] dark:hover:bg-[var(--color-surface-dark-alt)]">
                                                    Agregar serie
                                                </a>
                                                <a href="{{ route('articulos.show', $row['articulo_id']) }}" wire:navigate class="block w-full px-4 py-2 text-left text-sm hover:bg-[var(--color-surface-alt)] dark:hover:bg-[var(--color-surface-dark-alt)]">
                                                    Historial
                                                </a>
                                            @else
                                                <button type="button" wire:click="abrirAjuste({{ $row['articulo_id'] }})" @click="openMenu = false" class="block w-full px-4 py-2 text-left text-sm hover:bg-[var(--color-surface-alt)] dark:hover:bg-[var(--color-surface-dark-alt)]">
                                                    Agregar ingreso
                                                </button>
                                                <button type="button" wire:click="abrirAjuste({{ $row['articulo_id'] }})" @click="openMenu = false" class="block w-full px-4 py-2 text-left text-sm hover:bg-[var(--color-surface-alt)] dark:hover:bg-[var(--color-surface-dark-alt)]">
                                                    Registrar salida
                                                </button>
                                            @endif
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
    @endif

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

    {{-- Modal: seleccion del tipo de registro (la lista queda de fondo) --}}
    <div
        x-cloak
        x-show="showCreate"
        class="fixed inset-0 z-[80] flex items-center justify-center p-4"
        role="dialog" aria-modal="true" aria-labelledby="crear-articulo-titulo"
        x-on:keydown.escape.window="showCreate = false"
    >
        <div
            x-show="showCreate"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="absolute inset-0 bg-black/40 backdrop-blur-sm"
            x-on:click="showCreate = false"
        ></div>

        <div
            x-show="showCreate"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-3 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="relative w-full max-w-2xl overflow-hidden rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-[var(--color-surface)] shadow-2xl dark:border-[var(--color-outline-dark)] dark:bg-[var(--color-surface-dark)]"
        >
            <div class="flex items-center justify-between border-b border-[var(--color-outline)] bg-[var(--color-surface-alt)]/60 px-6 py-4 dark:border-[var(--color-outline-dark)] dark:bg-[var(--color-surface-dark-alt)]/20">
                <h2 id="crear-articulo-titulo" class="text-xl font-semibold">¿Como deseas registrar el articulo?</h2>
                <button type="button" @click="showCreate = false" class="rounded-full p-1 transition-colors hover:bg-[var(--color-surface-alt)] dark:hover:bg-[var(--color-surface-dark-alt)]" aria-label="Cerrar">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke="currentColor" fill="none" stroke-width="1.4" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="grid grid-cols-1 gap-6 px-6 py-8 md:grid-cols-2">
                <a href="{{ route('articulos.create', ['mode' => 'cantidad']) }}" wire:navigate class="group h-full rounded-[var(--radius-radius)] border-2 border-[var(--color-outline)] p-6 text-left transition-all hover:border-[var(--color-primary)] hover:bg-[var(--color-primary)]/5">
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-[var(--color-primary)]/10 group-hover:bg-[var(--color-primary)]/20">
                            <svg class="h-6 w-6 text-[var(--color-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m0 0h6m-6 0h6"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold transition-colors group-hover:text-[var(--color-primary)]">Consumible</h3>
                            <p class="mt-2 text-sm opacity-75">Ideal para municiones, granadas o articulos consumibles. El sistema gestiona cantidades agregadas.</p>
                            <p class="mt-3 text-xs font-medium text-[var(--color-primary)]">tipo: consumible | gestion automatica por cantidad</p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('articulos.create', ['mode' => 'serie']) }}" wire:navigate class="group h-full rounded-[var(--radius-radius)] border-2 border-[var(--color-outline)] p-6 text-left transition-all hover:border-[var(--color-success)] hover:bg-[var(--color-success)]/5">
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-[var(--color-success)]/10 group-hover:bg-[var(--color-success)]/20">
                            <svg class="h-6 w-6 text-[var(--color-success)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold transition-colors group-hover:text-[var(--color-success)]">Reutilizable</h3>
                            <p class="mt-2 text-sm opacity-75">Perfecto para cascos, escudos, chalecos o armas. Cada unidad se controla por serie.</p>
                            <p class="mt-3 text-xs font-medium text-[var(--color-success)]">tipo: reutilizable | gestion automatica por serie</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
