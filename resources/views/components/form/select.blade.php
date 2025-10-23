@props([
    'label' => null,
    'name',
    'options' => [],
    'required' => false,
    'value' => null,
])

<div class="flex flex-col gap-2">
    @if($label)
        <label for="{{ $name }}" class="text-sm font-medium text-on-surface dark:text-on-surface-dark">
            {{ $label }} 
            @if($required)
                <span class="text-danger">*</span>
            @endif
        </label>
    @endif
    
    <select 
        id="{{ $name }}" 
        name="{{ $name }}"
        @if($required) required @endif
        {{ $attributes->merge([
            'class' => 'w-full rounded-[var(--radius-radius)] border border-outline dark:border-outline-dark bg-surface dark:bg-surface-dark px-3 py-2 text-sm text-on-surface dark:text-on-surface-dark focus:outline-hidden focus:ring-2 focus:ring-primary dark:focus:ring-primary-dark focus:ring-offset-2 focus:ring-offset-surface dark:focus:ring-offset-surface-dark disabled:cursor-not-allowed disabled:opacity-75'
        ]) }}
    >
        {{ $slot }}
    </select>
    
    @error($name)
        <span class="text-xs text-danger">{{ $message }}</span>
    @enderror
</div>