<div class="w-full max-w-4xl mx-auto p-6 space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">{{ $articulo->nombre }}</h1>
            <p class="text-sm text-muted">Categoría: {{ $articulo->categoria?->nombre ?? '—' }} • Tipo: {{ ucfirst($articulo->tipo) }} • Seguimiento: {{ ucfirst($articulo->seguimiento) }}</p>
        </div>
                <div class="flex items-center gap-2">
            <button type="button" wire:click="exportPdf" class="px-3 py-2 rounded border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] text-sm hover:bg-[var(--color-surface-alt)] dark:hover:bg-[var(--color-surface-dark-alt)]">
                Exportar PDF
            </button>
            <a href="{{ route('articulos.index') }}" wire:navigate class="px-3 py-2 rounded bg-[var(--color-surface)] border">Volver</a>
        </div>
    </div>

    <div class="rounded-[var(--radius-radius)] border border-[var(--color-outline)] overflow-hidden bg-[var(--color-surface)]">
        @if($articulo->seguimiento === 'serie')
            <div class="p-4 border-b bg-[var(--color-surface-alt)]">
                <h2 class="font-medium">Unidades / Series</h2>
                <p class="text-xs text-[var(--color-on-surface)] opacity-70">Listado de códigos de serie y estado para este artículo.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-[var(--color-surface-alt)] dark:bg-[var(--color-surface-dark-alt)] border-b border-[var(--color-outline)] dark:border-[var(--color-outline-dark)]">
                        <tr>
                            <th class="px-6 py-4 text-xs font-semibold text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] uppercase tracking-wider">Código de serie</th>
                            <th class="px-6 py-4 text-xs font-semibold text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] uppercase tracking-wider">Creado</th>
                            <th class="px-6 py-4 text-xs font-semibold text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] uppercase tracking-wider">Observaciones</th>
                            <th class="px-6 py-4 text-xs font-semibold text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] uppercase tracking-wider text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--color-outline)] dark:divide-[var(--color-outline-dark)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)]">
                        @forelse($series as $s)
                            <tr class="hover:bg-[var(--color-surface-alt)] dark:hover:bg-[var(--color-surface-dark-alt)] transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">{{ $s->codigo_serie }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $s->created_at?->format('d/m/Y H:i') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $s->observaciones ?? '—' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <a href="#" class="text-[var(--color-primary)]">Historial</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-[var(--color-on-surface)] opacity-60">No hay unidades registradas para este artículo.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($series->hasPages())
                <div class="p-4">
                    {{ $series->links('pagination::tailwind') }}
                </div>
            @endif

        @else
            <div class="p-4 border-b bg-[var(--color-surface-alt)]">
                <h2 class="font-medium">Movimientos</h2>
                <p class="text-xs text-[var(--color-on-surface)] opacity-70">Listado de operaciones que afectan el stock por cantidad para este artículo.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-[var(--color-surface-alt)] dark:bg-[var(--color-surface-dark-alt)] border-b border-[var(--color-outline)] dark:border-[var(--color-outline-dark)]">
                        <tr>
                            <th class="px-6 py-4 text-xs font-semibold text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] uppercase tracking-wider">Tipo</th>
                            <th class="px-6 py-4 text-xs font-semibold text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] uppercase tracking-wider">Fecha</th>
                            <th class="px-6 py-4 text-xs font-semibold text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] uppercase tracking-wider">Cantidad</th>
                            <th class="px-6 py-4 text-xs font-semibold text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] uppercase tracking-wider">Condición</th>
                            <th class="px-6 py-4 text-xs font-semibold text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] uppercase tracking-wider">Observaciones</th>
                            <th class="px-6 py-4 text-xs font-semibold text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] uppercase tracking-wider text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--color-outline)] dark:divide-[var(--color-outline-dark)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)]">
                        @forelse($detalles as $d)
                            <tr class="hover:bg-[var(--color-surface-alt)] dark:hover:bg-[var(--color-surface-dark-alt)] transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">{{ $d->operacion?->tipo ?? '—' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ optional($d->operacion?->fecha)->format('d/m/Y H:i') ?? ($d->created_at?->format('d/m/Y H:i') ?? '—') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $d->cantidad }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $d->condicion ?? '—' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $d->observaciones ?? '—' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <a href="#" class="text-[var(--color-primary)]">Ver operación</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-[var(--color-on-surface)] opacity-60">No hay movimientos registrados para este artículo.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($detalles->hasPages())
                <div class="p-4">
                    {{ $detalles->links('pagination::tailwind') }}
                </div>
            @endif

        @endif
    </div>

</div>

