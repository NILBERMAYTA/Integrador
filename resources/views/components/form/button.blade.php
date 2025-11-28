@props([
    'variant' => 'primary',
    'type' => 'button',
])

@php
    $baseClasses = 'whitespace-nowrap rounded-radius px-4 py-2 text-sm font-medium tracking-wide transition hover:opacity-75 text-center focus-visible:outline-2 focus-visible:outline-offset-2 active:opacity-100 active:outline-offset-0 disabled:opacity-75 disabled:cursor-not-allowed';

    $variants = [
        'primary' => 'bg-primary border border-primary text-on-primary dark:bg-primary-dark dark:border-primary-dark dark:text-on-primary-dark dark:focus-visible:outline-primary-dark focus-visible:outline-primary',
        'secondary' => 'bg-secondary border border-secondary text-on-secondary dark:bg-secondary-dark dark:border-secondary-dark dark:text-on-secondary-dark dark:focus-visible:outline-secondary-dark focus-visible:outline-secondary',
        'alternate' => 'bg-surface-alt border border-surface-alt text-on-surface-strong dark:bg-surface-dark-alt dark:border-surface-dark-alt dark:text-on-surface-dark-strong dark:focus-visible:outline-surface-dark-alt focus-visible:outline-surface-alt',
        'inverse' => 'bg-surface-dark border border-surface-dark text-on-surface-dark dark:bg-surface dark:border-surface dark:text-on-surface dark:focus-visible:outline-surface',
        'info' => 'bg-info border border-info text-onInfo dark:bg-info dark:border-info dark:text-onInfo dark:focus-visible:outline-info focus-visible:outline-info',
        'danger' => 'bg-danger border border-danger text-onDanger dark:bg-danger dark:border-danger dark:text-onDanger dark:focus-visible:outline-danger focus-visible:outline-danger',
        'warning' => 'bg-warning border border-warning text-onWarning dark:bg-warning dark:border-warning dark:text-onWarning dark:focus-visible:outline-warning',
        'success' => 'bg-success border border-success text-onSuccess dark:bg-success dark:border-success dark:text-onSuccess dark:focus-visible:outline-success',
    ];

    $classes = $baseClasses . ' ' . ($variants[$variant] ?? $variants['primary']);
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</button>
