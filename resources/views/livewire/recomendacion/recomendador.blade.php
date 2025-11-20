<div class="bg-[var(--color-surface)] rounded-[var(--radius-radius)] border border-[var(--color-outline)] shadow-sm p-5 space-y-4">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <h3 class="text-lg font-semibold text-[var(--color-on-surface-strong)]">Asistente táctico</h3>
            <span class="text-xs font-medium bg-blue-100 text-blue-800 px-2 py-0.5 rounded">Beta</span>
        </div>
        <button wire:click="resetForm" class="text-xs text-[var(--color-primary)] hover:underline">Limpiar</button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <div>
            <label class="text-xs font-medium text-[var(--color-on-surface)] opacity-70">Tipo de conflicto</label>
            <select wire:model.live="tipo_conflicto" class="mt-1 w-full rounded-[var(--radius-radius)] border border-[var(--color-outline)] px-3 py-2 text-sm bg-[var(--color-surface)]">
                <option value="">Seleccionar...</option>
                @foreach($tipos as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="text-xs font-medium text-[var(--color-on-surface)] opacity-70">Nivel de agresividad</label>
            <select wire:model.live="agresividad" class="mt-1 w-full rounded-[var(--radius-radius)] border border-[var(--color-outline)] px-3 py-2 text-sm bg-[var(--color-surface)]">
                <option value="">Seleccionar...</option>
                @foreach($agresividades as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="text-xs font-medium text-[var(--color-on-surface)] opacity-70">Aprox. personas</label>
            <select wire:model.live="cantidad_personas" class="mt-1 w-full rounded-[var(--radius-radius)] border border-[var(--color-outline)] px-3 py-2 text-sm bg-[var(--color-surface)]">
                <option value="">Seleccionar...</option>
                @foreach($rangosPersonas as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    @if($resultado)
        <div class="p-4 rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-[var(--color-surface-alt)]">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs uppercase tracking-wide opacity-60">Personal recomendado</p>
                    <p class="text-2xl font-bold text-[var(--color-primary)]">{{ $resultado['personal'] }} efectivos</p>
                </div>
                <span class="px-2 py-1 rounded text-xs font-semibold {{ $agresividad === 'alto' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                    {{ $resultado['alerta'] }}
                </span>
            </div>

            <div class="mt-3">
                <p class="text-xs uppercase tracking-wide opacity-60">Equipamiento sugerido</p>
                <div class="mt-2 grid sm:grid-cols-2 gap-2">
                    @foreach($resultado['equipo'] as $item)
                        <div class="flex items-center gap-2 rounded-[var(--radius-radius)] border border-[var(--color-outline)] px-3 py-2 bg-[var(--color-surface)]">
                            <svg class="w-4 h-4 text-[var(--color-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span class="text-sm text-[var(--color-on-surface)]">{{ $item }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @else
        <div class="text-center py-6 text-[var(--color-on-surface)] opacity-70 text-sm">
            Selecciona tipo, agresividad y personas para ver la recomendación.
        </div>
    @endif
</div>
