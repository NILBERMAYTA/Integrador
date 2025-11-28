@props([
    'variant' => 'neutral',
    'type' => 'button',
    'href' => null,
])

@php
    $baseClasses = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[var(--radius-radius)] text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-offset-1';

    $variants = [
        'neutral' => 'border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] hover:bg-[var(--color-surface-alt)] dark:hover:bg-[var(--color-surface-dark-alt)] hover:border-[var(--color-outline-strong)] dark:hover:border-[var(--color-outline-dark-strong)] focus:ring-[var(--color-primary)] dark:focus:ring-[var(--color-primary-dark)]',
        // Usa los colores originales de los botones de acciones en artículos
        'edit' => 'border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] hover:bg-[var(--color-surface-alt)] dark:hover:bg-[var(--color-surface-dark-alt)] hover:border-[var(--color-outline-strong)] dark:hover:border-[var(--color-outline-dark-strong)] focus:ring-[var(--color-primary)] dark:focus:ring-[var(--color-primary-dark)]',
        'details' => 'border border-[var(--color-secondary)] dark:border-[var(--color-secondary-dark)] bg-[var(--color-secondary)]/10 dark:bg-[var(--color-secondary-dark)]/10 text-[var(--color-secondary)] dark:text-[var(--color-secondary-dark)] hover:bg-[var(--color-secondary)]/20 dark:hover:bg-[var(--color-secondary-dark)]/20 focus:ring-[var(--color-secondary)] dark:focus:ring-[var(--color-secondary-dark)]',
        'adjust' => 'border border-[var(--color-warning)] bg-[var(--color-warning)]/10 text-[var(--color-warning)] hover:bg-[var(--color-warning)]/20 focus:ring-[var(--color-warning)]',
        'return' => 'border border-[var(--color-primary)] dark:border-[var(--color-primary-dark)] bg-[var(--color-primary)]/10 dark:bg-[var(--color-primary-dark)]/10 text-[var(--color-primary)] dark:text-[var(--color-primary-dark)] hover:bg-[var(--color-primary)] hover:text-[var(--color-on-primary)] dark:hover:bg-[var(--color-primary-dark)] dark:hover:text-[var(--color-on-primary-dark)] focus:ring-[var(--color-primary)] dark:focus:ring-[var(--color-primary-dark)]',
        'delete' => 'border border-[var(--color-danger)] bg-[var(--color-danger)]/10 text-[var(--color-danger)] hover:bg-[var(--color-danger)] hover:text-[var(--color-on-danger)] focus:ring-[var(--color-danger)]',
        'success' => 'border border-[var(--color-success)] bg-[var(--color-success)]/10 text-[var(--color-success)] hover:bg-[var(--color-success)] hover:text-[var(--color-on-success)] focus:ring-[var(--color-success)]',
    ];

    $classes = $baseClasses . ' ' . ($variants[$variant] ?? $variants['neutral']);
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
