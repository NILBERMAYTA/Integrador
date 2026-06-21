<div class="w-full max-w-7xl mx-auto p-6 space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">
                Inventario de Artículos
            </h1>
            <p class="mt-1 text-sm text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] opacity-70">
                Vista consolidada del stock actual de todos los artículos.
            </p>
        </div>
                <div class="flex items-center gap-3">
            <button type="button" wire:click="exportPdf"
                class="inline-flex items-center gap-2 whitespace-nowrap rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] px-4 py-2 text-sm font-medium text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] hover:bg-[var(--color-surface-alt)] dark:hover:bg-[var(--color-surface-dark-alt)] transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 16l4-4m-4 4l-4-4m4 4V4m0 12v4m-7 0h14"/>
                </svg>
                Exportar PDF
            </button>
            <a href="{{ route('articulos.index') }}" wire:navigate
                class="inline-flex items-center gap-2 whitespace-nowrap rounded-[var(--radius-radius)] bg-[var(--color-primary)] text-[var(--color-on-primary)] px-4 py-2 text-sm font-medium hover:opacity-90 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nuevo artículo
            </a>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 p-4 bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)]">
    
        <div>
            <label class="block text-sm font-medium text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] mb-2">
                Buscar
            </label>
            <input type="text" wire:model.live="search" placeholder="Nombre o descripción..." 
                         class="w-full px-3 py-2 rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)]" />
        </div>

        <div>
            <label class="block text-sm font-medium text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] mb-2">
                Categoría
            </label>
            <select wire:model.live="categoria_id" 
                            class="w-full px-3 py-2 rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)]">
                <option value="">Todas las categorías</option>
                @foreach($categorias as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] mb-2">
                Unidad
            </label>
            <select wire:model.live="unidad_id" 
                            class="w-full px-3 py-2 rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)]">
                <option value="">Todas las unidades</option>
                @foreach($unidades as $unidad)
                    <option value="{{ $unidad->id }}">{{ ($unidad->sigla ? $unidad->sigla.' - ' : '').$unidad->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex items-end">
            <button type="button" wire:click="$set('search', ''); $set('categoria_id', null); $set('unidad_id', null);"
                            class="w-full px-4 py-2 rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] font-medium hover:bg-[var(--color-surface-alt)] dark:hover:bg-[var(--color-surface-dark-alt)] transition-all">
                Limpiar filtros
            </button>
        </div>
    </div>

    {{-- Tabla de inventario --}}
    <div class="rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] overflow-hidden bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)]">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] bg-[var(--color-surface-alt)]/60 dark:bg-[var(--color-surface-dark-alt)]/20">
                        <th class="px-4 py-3 text-left font-semibold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">
                            <button type="button" wire:click="sortBy('articulos.nombre')" class="inline-flex items-center gap-1 hover:text-[var(--color-primary)] transition-colors">
                                Artículo
                                @if($sortField === 'articulos.nombre')
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        @if($sortDirection === 'asc')
                                            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                                        @else
                                            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                                        @endif
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th class="px-4 py-3 text-left font-semibold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">Categoría</th>
                        <th class="px-4 py-3 text-left font-semibold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">Tipo</th>
                        <th class="px-4 py-3 text-center font-semibold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">Entrada</th>
                        <th class="px-4 py-3 text-center font-semibold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">Salida</th>
                        <th class="px-4 py-3 text-center font-semibold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">
                            <button type="button" wire:click="sortBy('total')" class="inline-flex items-center gap-1 hover:text-[var(--color-primary)] transition-colors justify-center">
                                Total Neto
                                @if($sortField === 'total')
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        @if($sortDirection === 'asc')
                                            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                                        @else
                                            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                                        @endif
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th class="px-4 py-3 text-left font-semibold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">Último movimiento</th>
                    </tr>
                </thead>
                <tbody class="kiro-stagger">
                    @forelse($articulos as $item)
                        @php
                            $art = $item['articulo'];
                            $entrada = $item['entrada'];
                            $salida = $item['salida'];
                            $total = $item['total'];
                            $ultimo = $item['ultimo_movimiento'];
                            $min = (float) ($art->stock_minimo ?? 0);
                            $isNegative = $total < 0;
                            $isZero = $total == 0;
                            $isLow = $min > 0 && $total > 0 && $total <= $min;
                            $estadoStock = ($isNegative || $isZero) ? 'Agotado' : ($isLow ? 'Bajo' : 'OK');
                            $estadoBadge = ($isNegative || $isZero) ? 'badge-error' : ($isLow ? 'badge-warning' : 'badge-success');
                        @endphp
                        <tr class="border-b border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] hover:bg-[var(--color-surface-alt)]/30 dark:hover:bg-[var(--color-surface-dark-alt)]/10 transition-colors">
                            <td class="px-4 py-3 font-medium text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">
                                {{ $art->nombre }}
                            </td>
                            <td class="px-4 py-3 text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">
                                <span class="inline-flex px-2 py-1 rounded-full text-xs font-medium bg-[var(--color-outline)]/20 dark:bg-[var(--color-outline-dark)]/20 text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">
                                    {{ $art->categoria?->nombre ?? '—' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">
                                <span class="inline-flex px-2 py-1 rounded-full text-xs font-medium {{ $art->tipo === 'consumible' ? 'bg-[var(--color-danger)]/10 text-[var(--color-danger)]' : 'bg-[var(--color-success)]/10 text-[var(--color-success)]' }}">
                                    {{ ucfirst($art->tipo) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex px-3 py-1 rounded-full text-sm font-semibold bg-[var(--color-success)]/10 text-[var(--color-success)]">
                                    +{{ number_format($entrada, $art->isCantidad() ? 2 : 0) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex px-3 py-1 rounded-full text-sm font-semibold bg-[var(--color-danger)]/10 text-[var(--color-danger)]">
                                    -{{ number_format($salida, $art->isCantidad() ? 2 : 0) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex flex-col items-center gap-1">
                                    <span class="inline-flex px-3 py-1 rounded-full text-sm font-bold {{ $isNegative ? 'bg-[var(--color-danger)]/10 text-[var(--color-danger)]' : ($isZero ? 'bg-[var(--color-warning)]/10 text-[var(--color-warning)]' : ($isLow ? 'bg-[var(--color-warning)]/10 text-[var(--color-warning)]' : 'bg-[var(--color-success)]/10 text-[var(--color-success)]')) }}">
                                        {{ number_format($total, $art->isCantidad() ? 2 : 0) }}
                                        @if($art->unidad_medida) <span class="opacity-60 font-normal">{{ $art->unidad_medida }}</span> @endif
                                    </span>
                                    <span class="badge badge-xs {{ $estadoBadge }} badge-soft" @if($min > 0) title="Minimo: {{ number_format($min, $art->isCantidad() ? 2 : 0) }}" @endif>{{ $estadoStock }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-left text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] text-xs opacity-75">
                                {{ $ultimo ?? '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] opacity-60">
                                No hay artículos que coincidan con los filtros.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Paginación --}}
    @if($articulos->hasPages())
        <div class="flex items-center justify-between px-4 py-3 bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)]">
            <div class="text-sm text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] opacity-70">
                Mostrando <span class="font-medium">{{ $articulos->firstItem() }}</span> a <span class="font-medium">{{ $articulos->lastItem() }}</span> de <span class="font-medium">{{ $articulos->total() }}</span> artículos
            </div>
            <div class="flex gap-2">
                {{ $articulos->links('pagination::tailwind') }}
            </div>
        </div>
    @endif

</div>

