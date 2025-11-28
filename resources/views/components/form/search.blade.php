@props([
    'label' => null,
    'name' => 'search',
    'placeholder' => 'Buscar',
    'required' => false,
])

@php
    $inputId = $attributes->get('id', $name ? $name : 'search_'.uniqid());
@endphp

<div class="flex w-full flex-col gap-2 text-on-surface dark:text-on-surface-dark">
    @if($label)
        <label for="{{ $inputId }}" class="w-fit pl-0.5 text-sm font-medium">
            {{ $label }}
            @if($required)
                <span class="text-danger">*</span>
            @endif
        </label>
    @endif
    <div class="relative">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true" class="absolute left-2.5 top-1/2 size-5 -translate-y-1/2 text-on-surface/50 dark:text-on-surface-dark/50">
            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
        </svg>
        <input
            id="{{ $inputId }}"
            type="search"
            name="{{ $name }}"
            placeholder="{{ $placeholder }}"
            aria-label="{{ $label ?? $placeholder }}"
            @if($required) required @endif
            {{ $attributes->merge([
                'class' => 'w-full rounded-[var(--radius-radius)] border border-outline bg-surface px-3 py-2 pl-10 text-sm text-on-surface placeholder:text-on-surface/60 focus:outline-hidden focus:ring-2 focus:ring-primary focus:ring-offset-2 focus:ring-offset-surface disabled:cursor-not-allowed disabled:opacity-75 dark:border-outline-dark dark:bg-surface-dark dark:text-on-surface-dark dark:placeholder:text-on-surface-dark/70 dark:focus:ring-primary-dark dark:focus:ring-offset-surface-dark'
            ]) }}
        />
    </div>

    @error($name)
        <span class="text-xs text-danger">{{ $message }}</span>
    @enderror
</div>
