@props([
    'cols' => 4,
])

@php
    // Clases literales para que Tailwind las incluya en el build (JIT no
    // genera clases construidas dinamicamente).
    $gridCols = match ((int) $cols) {
        2 => 'sm:grid-cols-2',
        3 => 'sm:grid-cols-2 lg:grid-cols-3',
        5 => 'grid-cols-2 lg:grid-cols-3 xl:grid-cols-5',
        6 => 'grid-cols-2 lg:grid-cols-3 xl:grid-cols-6',
        default => 'sm:grid-cols-2 xl:grid-cols-4',
    };
@endphp

<div {{ $attributes->merge(['class' => "grid gap-4 {$gridCols} kiro-stagger"]) }}>
    {{ $slot }}
</div>
