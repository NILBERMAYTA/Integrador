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

    <div class="bg-[var(--color-surface)] rounded-[var(--radius-radius)] border border-[var(--color-outline)] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-[var(--color-surface-alt)] border-b border-[var(--color-outline)]">
                    <tr>
                        <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wider text-[var(--color-on-surface)]">Articulo</th>
                        <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wider text-[var(--color-on-surface)] text-center">Prestado</th>
                        <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wider text-[var(--color-on-surface)] text-center">Pendiente</th>
                        <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wider text-[var(--color-on-surface)]">Devolver</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--color-outline)] bg-[var(--color-surface)]">
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
                                    <p class="text-xs text-[var(--color-on-surface)] opacity-70 mb-2">Selecciona todas las series pendientes</p>
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
