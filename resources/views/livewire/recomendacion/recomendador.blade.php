<div class="bg-[var(--color-surface)] rounded-lg border border-[var(--color-outline)] shadow-md p-6">
    <h3 class="text-lg font-bold text-[var(--color-on-surface-strong)] mb-4 flex items-center gap-2">
        🤖 Asistente Táctico
        <span class="text-xs font-normal bg-blue-100 text-blue-800 px-2 py-1 rounded">Beta</span>
    </h3>

    <div class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div>
                <label class="text-xs font-medium">Tipo de Conflicto</label>
                <select wire:model.live="tipo_conflicto" class="w-full rounded border p-2 text-sm">
                    <option value="">Seleccionar...</option>
                    @foreach($tipos as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-xs font-medium">Nivel Agresividad</label>
                <select wire:model.live="agresividad" class="w-full rounded border p-2 text-sm">
                    <option value="">Seleccionar...</option>
                    <option value="bajo">🟢 Bajo (Pacífico)</option>
                    <option value="medio">🟡 Medio (Gritos/Insultos)</option>
                    <option value="alto">🔴 Alto (Armas/Piedras)</option>
                </select>
            </div>

            <div>
                <label class="text-xs font-medium">Aprox. Personas</label>
                <select wire:model.live="cantidad_personas" class="w-full rounded border p-2 text-sm">
                    <option value="">Seleccionar...</option>
                    <option value="50">1 - 50</option>
                    <option value="200">50 - 200</option>
                    <option value="500">200 - 500</option>
                    <option value="1000">Masivo (+1000)</option>
                </select>
            </div>
        </div>

        @if($resultado)
            <div class="mt-4 p-4 bg-[var(--color-surface-alt)] rounded border border-l-4 {{ $agresividad == 'alto' ? 'border-l-red-500' : 'border-l-green-500' }} animate-pulse-once">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider opacity-60">Personal Recomendado</span>
                        <div class="text-2xl font-bold text-[var(--color-primary)]">
                            {{ $resultado['personal'] }} Efectivos
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="px-2 py-1 rounded text-xs font-bold {{ $agresividad == 'alto' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                            {{ $resultado['alerta'] }}
                        </span>
                    </div>
                </div>

                <div>
                    <span class="text-xs font-bold uppercase tracking-wider opacity-60">Equipamiento Sugerido</span>
                    <ul class="mt-2 space-y-1">
                        @foreach($resultado['equipo'] as $item)
                            <li class="text-sm flex items-center gap-2">
                                <svg class="w-4 h-4 text-[var(--color-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                {{ $item }}
                            </li>
                        @endforeach
                    </ul>
                </div>
                
                <div class="mt-4 pt-3 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ route('prestamos.create') }}" class="text-sm text-blue-600 hover:underline">
                        Ir a Crear Asignación con estos datos &rarr;
                    </a>
                </div>
            </div>
        @else
            <div class="text-center py-6 text-gray-400 text-sm">
                Selecciona los parámetros para generar una estrategia.
            </div>
        @endif
    </div>
</div>