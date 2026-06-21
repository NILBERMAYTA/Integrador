<x-layouts.app.sidebar :title="$title ?? null" class="bg-surface dark:bg-surface-dark">
    <flux:main class="bg-surface dark:bg-surface-dark">
        <div wire:key="page-{{ request()->path() }}" class="kiro-page-enter">
            {{ $slot }}
        </div>
    </flux:main>
</x-layouts.app.sidebar>