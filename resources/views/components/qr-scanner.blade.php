@props([
    'method',
    'label' => 'Escanear QR',
    'title' => 'Escanear codigo QR',
    'description' => 'Coloca el codigo dentro del recuadro.',
])

@php
    $readerId = 'qr-reader-'.\Illuminate\Support\Str::uuid();
@endphp

<div
    class="inline-flex"
    x-data="qrScanner(@js($readerId), (value) => $wire.{{ $method }}(value))"
>
    <button
        type="button"
        class="btn border-lime-700 bg-lime-800 text-white hover:border-lime-600 hover:bg-lime-700 dark:border-lime-600 dark:bg-lime-700 dark:hover:bg-lime-600"
        @click="open($refs.dialog)"
    >
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 0 1 2-2h3M16 3h3a2 2 0 0 1 2 2v3M21 16v3a2 2 0 0 1-2 2h-3M8 21H5a2 2 0 0 1-2-2v-3M7 7h3v3H7V7Zm7 0h3v3h-3V7ZM7 14h3v3H7v-3Zm7 0h1m2 0v3h-3v-1"/>
        </svg>
        {{ $label }}
    </button>

    <dialog
        x-ref="dialog"
        class="modal modal-middle bg-black/45 backdrop-blur-[2px]"
        @close="stop()"
    >
        <div class="modal-box max-w-lg overflow-hidden border border-[var(--color-outline)] bg-[var(--color-surface)] text-[var(--color-on-surface)] dark:border-lime-900 dark:bg-[#182015] dark:text-[#dce8d3]">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-[var(--color-on-surface-strong)] dark:text-lime-300">{{ $title }}</h3>
                    <p class="mt-1 text-sm opacity-75">{{ $description }}</p>
                </div>
                <button type="button" class="btn btn-sm btn-circle btn-ghost" @click="close($refs.dialog)" aria-label="Cerrar">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="mt-4 overflow-hidden rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-black dark:border-lime-900">
                <div id="{{ $readerId }}" class="aspect-square w-full"></div>
            </div>

            <div x-show="loading" class="mt-3 flex items-center gap-2 text-sm">
                <span class="loading loading-spinner loading-sm"></span>
                Iniciando camara...
            </div>

            <div x-show="error" x-cloak class="alert mt-3 border border-amber-500 bg-amber-500/10 text-amber-700 dark:text-amber-300">
                <span x-text="error"></span>
            </div>

            <div class="mt-4">
                <label class="text-xs font-semibold uppercase opacity-70">Entrada manual</label>
                <div class="mt-1 flex gap-2">
                    <input
                        type="text"
                        x-model="manualValue"
                        @keydown.enter.prevent="submit(manualValue)"
                        class="input min-w-0 flex-1 border-[var(--color-outline)] bg-[var(--color-surface)] dark:border-lime-900 dark:bg-[#222d1d]"
                        placeholder="Pega el contenido del QR"
                    >
                    <button type="button" class="btn border-lime-700 bg-lime-800 text-white hover:bg-lime-700" @click="submit(manualValue)">
                        Usar
                    </button>
                </div>
            </div>
        </div>

        <form method="dialog" class="modal-backdrop" @submit.prevent="close($refs.dialog)">
            <button type="submit">cerrar</button>
        </form>
    </dialog>
</div>
