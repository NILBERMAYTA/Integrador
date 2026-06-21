<div class="space-y-3">

    <div class="grid gap-2.5">
        <label class="grid gap-1.5">
            <span class="text-xs font-semibold text-[var(--color-on-surface)]/70 dark:text-[var(--color-on-surface-dark)]/70">Situación observada</span>
            <select wire:model.live="situacion" class="w-full rounded-xl border border-[var(--color-outline)] bg-[var(--color-surface)] px-3 py-1.5 text-sm dark:border-[var(--color-outline-dark)] dark:bg-[var(--color-surface-dark)]">
                <option value="">Seleccionar...</option>
                @foreach($situaciones as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </label>

        <div class="grid gap-2.5 sm:grid-cols-2">
            <label class="grid gap-1.5">
                <span class="text-xs font-semibold text-[var(--color-on-surface)]/70 dark:text-[var(--color-on-surface-dark)]/70">Carácter de la reunión</span>
                <select wire:model.live="legalidad" class="w-full rounded-xl border border-[var(--color-outline)] bg-[var(--color-surface)] px-3 py-1.5 text-sm dark:border-[var(--color-outline-dark)] dark:bg-[var(--color-surface-dark)]">
                    <option value="">Seleccionar...</option>
                    @foreach($legalidades as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </label>

            <label class="grid gap-1.5">
                <span class="text-xs font-semibold text-[var(--color-on-surface)]/70 dark:text-[var(--color-on-surface-dark)]/70">Magnitud estimada</span>
                <select wire:model.live="magnitud" class="w-full rounded-xl border border-[var(--color-outline)] bg-[var(--color-surface)] px-3 py-1.5 text-sm dark:border-[var(--color-outline-dark)] dark:bg-[var(--color-surface-dark)]">
                    <option value="">Seleccionar...</option>
                    @foreach($magnitudes as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </label>
        </div>

        <label class="grid gap-1.5">
            <span class="text-xs font-semibold text-[var(--color-on-surface)]/70 dark:text-[var(--color-on-surface-dark)]/70">Conducta actual o amenaza inminente</span>
            <select wire:model.live="conducta" class="w-full rounded-xl border border-[var(--color-outline)] bg-[var(--color-surface)] px-3 py-1.5 text-sm dark:border-[var(--color-outline-dark)] dark:bg-[var(--color-surface-dark)]">
                <option value="">Seleccionar...</option>
                @foreach($conductas as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </label>

        <div class="grid gap-2 lg:grid-cols-3 xl:grid-cols-1 2xl:grid-cols-3">
            <label class="flex cursor-pointer items-start gap-2.5 rounded-xl border border-[var(--color-outline)]/70 p-2.5 dark:border-[var(--color-outline-dark)]/70">
                <input type="checkbox" wire:model.live="planificado" class="checkbox checkbox-sm mt-0.5">
                <span>
                    <span class="block text-sm font-medium">Evento conocido previamente</span>
                    <span class="block text-xs opacity-60">Permite activar planificación, coordinación y contingencias.</span>
                </span>
            </label>
            <label class="flex cursor-pointer items-start gap-2.5 rounded-xl border border-[var(--color-outline)]/70 p-2.5 dark:border-[var(--color-outline-dark)]/70">
                <input type="checkbox" wire:model.live="vulnerables" class="checkbox checkbox-sm mt-0.5">
                <span>
                    <span class="block text-sm font-medium">Hay grupos vulnerables</span>
                    <span class="block text-xs opacity-60">Niñez, personas mayores, discapacidad u otras condiciones.</span>
                </span>
            </label>
            <label class="flex cursor-pointer items-start gap-2.5 rounded-xl border border-[var(--color-outline)]/70 p-2.5 dark:border-[var(--color-outline-dark)]/70">
                <input type="checkbox" wire:model.live="zona_sensible" class="checkbox checkbox-sm mt-0.5">
                <span>
                    <span class="block text-sm font-medium">Zona sensible o espacio cerrado</span>
                    <span class="block text-xs opacity-60">Hospital, colegio, asilo u otro lugar de especial protección.</span>
                </span>
            </label>
        </div>
    </div>

    @if($resultado)
        @php
            $riskClasses = [
                'emerald' => 'border-emerald-500/30 bg-emerald-500/8 text-emerald-700 dark:text-emerald-300',
                'amber' => 'border-amber-500/30 bg-amber-500/8 text-amber-700 dark:text-amber-300',
                'orange' => 'border-orange-500/30 bg-orange-500/8 text-orange-700 dark:text-orange-300',
                'rose' => 'border-rose-500/30 bg-rose-500/8 text-rose-700 dark:text-rose-300',
                'red' => 'border-red-500/30 bg-red-500/8 text-red-700 dark:text-red-300',
            ];
        @endphp

        <div class="space-y-4 border-t border-[var(--color-outline)] pt-4 dark:border-[var(--color-outline-dark)] kiro-fade-up">
            <div class="rounded-2xl border p-4 {{ $riskClasses[$resultado['color']] }}">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-[0.18em] opacity-70">{{ $resultado['nivel'] }}</p>
                        <h4 class="mt-1 text-base font-bold">{{ $resultado['respuesta'] }}</h4>
                    </div>
                    <span class="rounded-full bg-black/5 px-2.5 py-1 text-[10px] font-bold uppercase dark:bg-white/10">{{ $resultado['riesgo'] }}</span>
                </div>
                <p class="mt-3 text-xs leading-5 opacity-80">{{ $resultado['objetivo'] }}</p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.15em] opacity-60">Prioridades recomendadas</p>
                <ol class="mt-2 space-y-2">
                    @foreach($resultado['acciones'] as $action)
                        <li class="flex gap-2 text-xs leading-5">
                            <span class="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full bg-[var(--color-primary)]/10 text-[10px] font-bold text-[var(--color-primary)] dark:text-[var(--color-primary-dark)]">{{ $loop->iteration }}</span>
                            <span>{{ $action }}</span>
                        </li>
                    @endforeach
                </ol>
            </div>

            <details class="rounded-xl border border-[var(--color-outline)]/70 p-3 dark:border-[var(--color-outline-dark)]/70">
                <summary class="cursor-pointer text-xs font-semibold">Salvaguardas obligatorias</summary>
                <ul class="mt-3 space-y-2">
                    @foreach($resultado['salvaguardas'] as $item)
                        <li class="flex gap-2 text-xs leading-5">
                            <flux:icon.shield-check class="mt-0.5 size-4 shrink-0 text-[var(--color-success)]" />
                            <span>{{ $item }}</span>
                        </li>
                    @endforeach
                </ul>
            </details>

            <div class="flex flex-wrap gap-1.5">
                @foreach($resultado['referencias'] as $reference)
                    <span class="rounded-full bg-[var(--color-surface-alt)] px-2.5 py-1 text-[10px] font-medium dark:bg-[var(--color-surface-dark-alt)]">{{ $reference }}</span>
                @endforeach
            </div>

            <button type="button" wire:click="resetForm" class="text-xs font-semibold text-[var(--color-primary)] hover:underline dark:text-[var(--color-primary-dark)]">
                Nueva evaluación
            </button>
        </div>
    @else
        <div class="rounded-2xl border border-dashed border-[var(--color-outline)] px-4 py-4 text-center text-xs opacity-60 dark:border-[var(--color-outline-dark)]">
            Completa los cuatro criterios principales para generar una orientación.
        </div>
    @endif
</div>
