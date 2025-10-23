<x-layouts.app.sidebar :title="$title ?? null" class="bg-surface dark:bg-surface-dark">
    <flux:main class="bg-surface dark:bg-surface-dark">
        {{ $slot }}
    </flux:main>
</x-layouts.app.sidebar>