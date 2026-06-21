@props([
    'label' => '',
    'value' => '0',
    'hint' => null,
    'icon' => null,
    'tone' => 'neutral',
    'progress' => null,
])

@php
    $tones = [
        'primary' => [
            'text' => 'text-[var(--color-primary)] dark:text-[var(--color-primary-dark)]',
            'bg' => 'bg-[var(--color-primary)]/10',
            'bar' => 'bg-[var(--color-primary)] dark:bg-[var(--color-primary-dark)]',
        ],
        'success' => [
            'text' => 'text-emerald-600 dark:text-emerald-400',
            'bg' => 'bg-emerald-500/10',
            'bar' => 'bg-emerald-500',
        ],
        'warning' => [
            'text' => 'text-amber-600 dark:text-amber-400',
            'bg' => 'bg-amber-500/10',
            'bar' => 'bg-amber-500',
        ],
        'danger' => [
            'text' => 'text-rose-600 dark:text-rose-400',
            'bg' => 'bg-rose-500/10',
            'bar' => 'bg-rose-500',
        ],
        'info' => [
            'text' => 'text-sky-600 dark:text-sky-400',
            'bg' => 'bg-sky-500/10',
            'bar' => 'bg-sky-500',
        ],
        'neutral' => [
            'text' => 'text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]',
            'bg' => 'bg-[var(--color-surface-alt)] dark:bg-[var(--color-surface-dark-alt)]',
            'bar' => 'bg-[var(--color-on-surface)]/40 dark:bg-[var(--color-on-surface-dark)]/40',
        ],
    ];
    $t = $tones[$tone] ?? $tones['neutral'];
@endphp

<div class="group relative overflow-hidden rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-[var(--color-surface)] p-4 shadow-sm transition hover:shadow-md sm:p-5 dark:border-[var(--color-outline-dark)] dark:bg-[var(--color-surface-dark)]">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-[var(--color-on-surface)]/60 sm:text-xs sm:tracking-[0.16em] dark:text-[var(--color-on-surface-dark)]/60">{{ $label }}</p>
            <p class="mt-1.5 text-2xl font-bold tracking-tight sm:mt-2 sm:text-3xl {{ $t['text'] }}">{{ $value }}</p>
            @if($hint)
                <p class="mt-1 text-xs text-[var(--color-on-surface)]/65 dark:text-[var(--color-on-surface-dark)]/65">{{ $hint }}</p>
            @endif
        </div>
        @if($icon)
            <div class="hidden size-10 shrink-0 items-center justify-center rounded-2xl sm:flex sm:size-11 {{ $t['bg'] }} {{ $t['text'] }}">
                <flux:icon :name="$icon" class="size-5" />
            </div>
        @endif
    </div>

    @if(! is_null($progress))
        <div class="mt-4 h-1.5 overflow-hidden rounded-full bg-[var(--color-surface-alt)] dark:bg-[var(--color-surface-dark-alt)]">
            <div class="h-full rounded-full {{ $t['bar'] }}" style="width: {{ max(0, min(100, (float) $progress)) }}%"></div>
        </div>
    @endif

    {{ $slot }}
</div>
