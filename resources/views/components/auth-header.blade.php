@props([
    'title',
    'description',
])

<div class="flex w-full flex-col text-center">
    <flux:heading id="auth-title" size="xl">{{ $title }}</flux:heading>
    <flux:subheading>{{ $description }}</flux:subheading>
</div>
