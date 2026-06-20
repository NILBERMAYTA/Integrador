@props([
    'payload',
    'title' => 'Codigo QR',
    'subtitle' => null,
    'filename' => 'codigo-qr',
])

@php
    $qrText = is_string($payload)
        ? $payload
        : json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
@endphp

<div
    class="inline-flex"
    x-data="qrModal(@js($qrText), @js($filename))"
>
    <button
        type="button"
        class="btn btn-sm border-[var(--color-outline)] bg-[var(--color-surface)] text-[var(--color-on-surface)] hover:border-[var(--color-outline-strong)] hover:bg-[var(--color-surface-alt)] dark:border-lime-800 dark:bg-[#182015] dark:text-lime-100 dark:hover:border-lime-600 dark:hover:bg-[#24301e]"
        @click="$refs.dialog.showModal(); render()"
    >
        {{ $slot->isEmpty() ? 'Ver QR' : $slot }}
    </button>

    <dialog
        x-ref="dialog"
        class="modal modal-middle bg-black/40 backdrop-blur-[2px]"
        tabindex="0"
    >
        <div class="modal-box max-h-[calc(100dvh-2rem)] max-w-md overflow-x-hidden border border-[var(--color-outline)] bg-[var(--color-surface)] text-[var(--color-on-surface)] shadow-2xl dark:border-lime-900 dark:bg-[#182015] dark:text-[#dce8d3]">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <h3 class="text-lg font-semibold text-[var(--color-on-surface-strong)] dark:text-lime-300">
                        {{ $title }}
                    </h3>
                    @if($subtitle)
                        <p class="mt-1 truncate text-sm text-[var(--color-on-surface)] opacity-75 dark:text-[#b8c9aa]">
                            {{ $subtitle }}
                        </p>
                    @endif
                </div>
                <form method="dialog">
                    <button
                        type="submit"
                        class="btn btn-sm btn-circle border-transparent bg-transparent text-[var(--color-on-surface)] hover:bg-[var(--color-surface-alt)] dark:text-lime-200 dark:hover:bg-[#2b3824]"
                        aria-label="Cerrar"
                        title="Cerrar"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </form>
            </div>

            <div class="mt-5 flex justify-center">
                <div class="w-full max-w-72 rounded-[var(--radius-radius)] border border-slate-300 bg-white p-4 shadow-sm">
                    <div x-ref="qr" class="mx-auto flex aspect-square w-full items-center justify-center">
                        <span x-show="loading" class="loading loading-spinner loading-lg"></span>
                    </div>
                </div>
            </div>

            <template x-if="error">
                <div class="alert mt-4 border border-[var(--color-danger)] bg-[var(--color-danger)]/10 text-[var(--color-danger)]">
                    <span x-text="error"></span>
                </div>
            </template>

            <div class="mt-4 min-w-0 rounded-[var(--radius-radius)] bg-[var(--color-surface-alt)] p-3 dark:border dark:border-lime-900 dark:bg-[#222d1d]">
                <p class="text-xs font-semibold uppercase text-[var(--color-on-surface-strong)] opacity-70 dark:text-lime-300">
                    Contenido del QR
                </p>
                <p
                    class="mt-1 max-h-20 overflow-y-auto break-all text-xs leading-5 text-[var(--color-on-surface)] dark:text-[#b8c9aa]"
                    x-text="value"
                ></p>
            </div>

            <div class="modal-action flex flex-wrap justify-end gap-2">
                <button
                    type="button"
                    class="btn btn-sm border-[var(--color-outline)] bg-[var(--color-surface-alt)] text-[var(--color-on-surface-strong)] hover:border-[var(--color-outline-strong)] hover:bg-[var(--color-surface)] dark:border-lime-800 dark:bg-[#263220] dark:text-lime-100 dark:hover:border-lime-600 dark:hover:bg-[#314029]"
                    @click="downloadSvg()"
                >
                    Descargar SVG
                </button>
                <button
                    type="button"
                    class="btn btn-sm border-[var(--color-outline)] bg-[var(--color-surface-alt)] text-[var(--color-on-surface-strong)] hover:border-[var(--color-outline-strong)] hover:bg-[var(--color-surface)] dark:border-lime-800 dark:bg-[#263220] dark:text-lime-100 dark:hover:border-lime-600 dark:hover:bg-[#314029]"
                    @click="downloadPng()"
                >
                    Descargar PNG
                </button>
                <button
                    type="button"
                    class="btn btn-sm border-[var(--color-primary)] bg-[var(--color-primary)] text-[var(--color-on-primary)] hover:opacity-90 dark:border-lime-500 dark:bg-lime-700 dark:text-white dark:hover:bg-lime-600"
                    @click="printQr()"
                >
                    Imprimir
                </button>
                <form method="dialog">
                    <button
                        type="submit"
                        class="btn btn-sm border-transparent bg-transparent text-[var(--color-on-surface)] hover:bg-[var(--color-surface-alt)] dark:text-[#b8c9aa] dark:hover:bg-[#2b3824] dark:hover:text-lime-100"
                    >
                        Cerrar
                    </button>
                </form>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button type="submit">cerrar</button>
        </form>
    </dialog>
</div>
