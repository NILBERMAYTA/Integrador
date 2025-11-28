<div {{ $attributes->merge(['class' => 'bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] rounded-[var(--radius-radius)] shadow-sm border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] p-4']) }}>
    <div class="flex flex-wrap items-center gap-3">
        {{ $slot }}
    </div>
    @isset($footer)
        <div class="mt-3">
            {{ $footer }}
        </div>
    @endisset
</div>
