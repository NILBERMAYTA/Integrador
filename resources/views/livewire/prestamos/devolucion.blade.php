<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-[var(--color-on-surface-strong)]">Devolucion de prestamo</h1>
            <p class="text-sm text-[var(--color-on-surface)] opacity-70">
                Prestamo #{{ $operacion->id }} · {{ $operacion->policia->name ?? 'Policia' }} · {{ $operacion->evento->nombre ?? 'Evento' }}
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('prestamos.index') }}" wire:navigate class="inline-flex items-center gap-2 rounded-[var(--radius-radius)] border border-[var(--color-outline)] px-4 py-2 text-sm">
                Volver
            </a>
            <button type="button" wire:click="save" class="inline-flex items-center gap-2 rounded-[var(--radius-radius)] bg-[var(--color-primary)] border border-[var(--color-primary)] px-5 py-2 text-sm font-medium text-[var(--color-on-primary)] hover:opacity-90 transition">
                Registrar devolucion
            </button>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="p-4 rounded-[var(--radius-radius)] bg-[var(--color-success)]/10 border border-[var(--color-success)] text-[var(--color-success)] flex items-center gap-3">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-3 rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-[var(--color-surface)] p-4 dark:border-lime-900 dark:bg-[#182015]">
        <div>
            <h2 class="font-semibold text-[var(--color-on-surface-strong)] dark:text-lime-300">Recepcion por QR</h2>
            <p class="text-sm opacity-70">Cada serie escaneada se agrega a esta devolucion.</p>
        </div>
        <x-qr-scanner
            method="procesarQr"
            label="Escanear serie"
            title="QR de devolucion"
            description="Escanea una serie pendiente de este prestamo."
        />
    </div>

    @if($qrMensaje)
        <div class="alert border border-[var(--color-success)] bg-[var(--color-success)]/10 text-[var(--color-success)]">
            <span>{{ $qrMensaje }}</span>
        </div>
    @endif

    @if($qrError)
        <div class="alert border border-[var(--color-danger)] bg-[var(--color-danger)]/10 text-[var(--color-danger)]">
            <span>{{ $qrError }}</span>
        </div>
    @endif

    @error('items')
        <div class="alert border border-[var(--color-danger)] bg-[var(--color-danger)]/10 text-[var(--color-danger)]">
            <span>{{ $message }}</span>
        </div>
    @enderror

    <div class="bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-[var(--color-surface-alt)] dark:bg-[var(--color-surface-dark-alt)] border-b border-[var(--color-outline)] dark:border-[var(--color-outline-dark)]">
                    <tr>
                        <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wider text-[var(--color-on-surface)]">Articulo</th>
                        <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wider text-[var(--color-on-surface)] text-center">Prestado</th>
                        <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wider text-[var(--color-on-surface)] text-center">Pendiente</th>
                        <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wider text-[var(--color-on-surface)]">Devolver</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--color-outline)] dark:divide-[var(--color-outline-dark)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)]">
                    @forelse($items as $idx => $item)
                        <tr class="hover:bg-[var(--color-surface-alt)] transition-colors">
                            <td class="px-6 py-3">
                                <div class="font-medium text-[var(--color-on-surface-strong)]">{{ $item['articulo'] }}</div>
                                <div class="text-xs text-[var(--color-on-surface)] opacity-70">
                                    Gestion: {{ $item['seguimiento'] === 'serie' ? 'Por serie' : 'Por cantidad' }}
                                </div>
                            </td>
                            <td class="px-6 py-3 text-center">{{ $item['cantidad_prestada'] }}</td>
                            <td class="px-6 py-3 text-center">{{ $item['cantidad_pendiente'] }}</td>
                            <td class="px-6 py-3">
                                @if($item['seguimiento'] === 'serie')
                                    <p class="text-xs text-[var(--color-on-surface)] opacity-70 mb-2">Series seleccionadas para esta devolucion</p>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($item['series_pendientes'] as $serie)
                                            <label class="inline-flex items-center gap-2 px-3 py-1 rounded-[var(--radius-radius)] border border-[var(--color-outline)]">
                                                <input type="checkbox" wire:model="items.{{ $idx }}.series_devolver" value="{{ $serie['id'] }}" class="rounded">
                                                <span class="text-sm text-[var(--color-on-surface)]">{{ $serie['codigo'] }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    @error("items.{$idx}.series_devolver") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                @else
                                    <div class="flex items-center gap-2">
                                        <input type="number" min="0" max="{{ $item['cantidad_pendiente'] }}" wire:model.lazy="items.{{ $idx }}.cantidad_devolver" class="w-24 rounded-[var(--radius-radius)] border border-[var(--color-outline)] px-2 py-1 text-center">
                                        <span class="text-xs text-[var(--color-on-surface)] opacity-70">Max: {{ $item['cantidad_pendiente'] }}</span>
                                    </div>
                                    @error("items.{$idx}.cantidad_devolver") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-[var(--color-on-surface)] opacity-70">
                                No hay detalles para devolver.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
