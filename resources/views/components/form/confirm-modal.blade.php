{{-- resources/views/components/confirm-modal.blade.php --}}
@props([
    'title' => 'Confirmar acción',
    'description' => null,
    'icon' => 'danger', // danger | info | warning
    'confirmText' => 'Confirmar',
    'cancelText' => 'Cancelar',
    'maxWidth' => 'lg', // sm | md | lg | xl | 2xl | 3xl | full
    'persistent' => false, // true: no cierra con ESC ni backdrop
])

@php
    $sizes = [
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-[40%]', // tu preferencia original
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        '3xl' => 'max-w-3xl',
        'full' => 'max-w-[95%]',
    ];
    $maxW = $sizes[$maxWidth] ?? $sizes['lg'];

    $iconClasses = [
        'danger' => 'text-[var(--color-danger)] bg-[var(--color-danger)]/10',
        'warning' => 'text-yellow-600 bg-yellow-500/10 dark:text-yellow-400',
        'info' => 'text-[var(--color-primary)] bg-[var(--color-primary)]/10',
    ];
    $iconClass = $iconClasses[$icon] ?? $iconClasses['danger'];
@endphp

<div
    {{ $attributes->class('relative') }}
    x-data="{ open: false }"
    x-modelable="open"
>
    {{-- Dispatcher oculto en el árbol ORIGINAL (no teletransportado) --}}
    <span x-ref="dispatcher" class="hidden" aria-hidden="true"></span>

    {{-- Overlay + Dialog teletransportados al <body> (arregla el blur/backdrop) --}}
    <template x-teleport="body">
        <div
            x-cloak
            x-show="open"
            x-transition.opacity.duration.200ms
            @keydown.escape.window="{{ $persistent ? '' : 'open = false' }}"
            @click.self="{{ $persistent ? '' : 'open = false' }}"
            class="fixed inset-0 z-50 flex items-end justify-center
                   bg-black/30 p-4 pb-8 sm:items-center lg:p-8
                   supports-[backdrop-filter]:backdrop-blur-md"
            role="dialog"
            aria-modal="true"
        >
            {{-- Dialog --}}
            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-200 delay-100 motion-reduce:transition-opacity"
                x-transition:enter-start="opacity-0 scale-90"
                x-transition:enter-end="opacity-100 scale-100"
                x-trap.inert.noscroll="open"
                class="flex w-full {{ $maxW }} flex-col gap-4 overflow-hidden rounded-[var(--radius-radius)]
                       border border-[var(--color-outline)] bg-[var(--color-surface)] text-[var(--color-on-surface)]
                       shadow-xl dark:border-[var(--color-outline-dark)] dark:bg-[var(--color-surface-dark)] dark:text-[var(--color-on-surface-dark)]"
            >
                {{-- Header --}}
                <div class="flex items-center justify-between border-b border-[var(--color-outline)] bg-[var(--color-surface-alt)]/60 px-6 py-4 dark:border-[var(--color-outline-dark)] dark:bg-[var(--color-surface-dark-alt)]/20">
                    <h3 class="text-lg font-semibold tracking-wide text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">
                        {{ $title }}
                    </h3>
                    <button
                        x-show="!@js($persistent)"
                        @click="open = false"
                        aria-label="cerrar modal"
                        class="rounded-full p-1 hover:bg-[var(--color-surface-alt)] dark:hover:bg-[var(--color-surface-dark-alt)] transition-colors"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="1.4" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Body --}}
                <div class="px-6 py-4 flex items-start gap-4">
                    <div class="flex-shrink-0">
                        <div class="rounded-full p-3 {{ $iconClass }}">
                            {{-- Ícono por variante --}}
                            @if($icon === 'warning')
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.29 3.86l-7.4 12.84A2 2 0 004.53 20h14.94a2 2 0 001.71-3.3L13.77 3.86a2 2 0 00-3.48 0z"/>
                                </svg>
                            @elseif($icon === 'info')
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z"/>
                                </svg>
                            @else
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            @endif
                        </div>
                    </div>

                    <div class="flex-1">
                        @if($slot->isNotEmpty())
                            {{ $slot }}
                        @else
                            <p class="font-medium text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)] mb-2">
                                {{ $description ?? '¿Desea continuar con esta acción?' }}
                            </p>
                            <p class="text-sm text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] opacity-75">
                                Esta acción puede ser irreversible.
                            </p>
                        @endif
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex flex-col-reverse gap-3 border-t border-[var(--color-outline)] bg-[var(--color-surface-alt)]/60 px-6 py-4 dark:border-[var(--color-outline-dark)] dark:bg-[var(--color-surface-dark-alt)]/20 sm:flex-row sm:justify-end">
                    <button
                        type="button"
                        @click="$refs.dispatcher.dispatchEvent(new CustomEvent('cancel', { bubbles: true })); open = false"
                        class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] px-5 py-2.5 text-sm font-medium tracking-wide text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] transition-all hover:bg-[var(--color-surface-alt)] dark:hover:bg-[var(--color-surface-dark-alt)] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--color-primary)] dark:focus-visible:outline-[var(--color-primary-dark)] active:opacity-100"
                    >
                        {{ $cancelText }}
                    </button>

                    <button
                        type="button"
                        @click="$refs.dispatcher.dispatchEvent(new CustomEvent('confirm', { bubbles: true })); open = false"
                        class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-[var(--radius-radius)] bg-[var(--color-danger)] border border-[var(--color-danger)] px-5 py-2.5 text-sm font-medium tracking-wide text-[var(--color-on-danger)] transition-all hover:opacity-90 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--color-danger)] active:opacity-100"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        {{ $confirmText }}
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>
