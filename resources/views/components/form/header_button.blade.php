@props([
    'variant' => 'export', 
    'type' => 'button',
    'href' => null,
])

@php
    $base = 'inline-flex items-center gap-2 whitespace-nowrap rounded-radius px-4 py-2 text-sm font-medium tracking-wide transition text-center focus-visible:outline-2 focus-visible:outline-offset-2 active:opacity-100 active:outline-offset-0 disabled:opacity-75 disabled:cursor-not-allowed';

    $variants = [
        'export' => 'bg-surface-alt border border-surface-alt text-on-surface-strong hover:opacity-75 focus-visible:outline-surface-alt dark:bg-surface-dark-alt dark:border-surface-dark-alt dark:text-on-surface-dark-strong dark:focus-visible:outline-surface-dark-alt',
        'neutral' => 'bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] px-5 py-2.5 text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] hover:bg-[var(--color-surface-alt)] dark:hover:bg-[var(--color-surface-dark-alt)] hover:border-[var(--color-outline-strong)] dark:hover:border-[var(--color-outline-dark-strong)] focus-visible:outline-[var(--color-primary)] dark:focus-visible:outline-[var(--color-primary-dark)]',
        'primary' => 'bg-[var(--color-primary)] dark:bg-[var(--color-primary-dark)] border border-[var(--color-primary)] dark:border-[var(--color-primary-dark)] px-6 py-2.5 text-[var(--color-on-primary)] dark:text-[var(--color-on-primary-dark)] hover:opacity-90 focus-visible:outline-[var(--color-primary)] dark:focus-visible:outline-[var(--color-primary-dark)]',
    ];

    $classes = $base . ' ' . ($variants[$variant] ?? $variants['neutral']);
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
