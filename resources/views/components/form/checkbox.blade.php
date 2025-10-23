@props([
    'label' => null,
    'name',
    'description' => null,
    'checked' => false,
    'value' => '1',
])

<div class="flex flex-col gap-2">
    <label class="flex items-center gap-2 cursor-pointer">
        <input 
            type="checkbox" 
            name="{{ $name }}" 
            value="{{ $value }}"
            @checked(old($name, $checked))
            {{ $attributes->merge([
                'class' => 'w-4 h-4 rounded border-outline dark:border-outline-dark bg-surface dark:bg-surface-dark text-primary dark:text-primary-dark focus:ring-2 focus:ring-primary dark:focus:ring-primary-dark'
            ]) }}
        />
        @if($label)
            <span class="text-sm text-on-surface dark:text-on-surface-dark">{{ $label }}</span>
        @endif
    </label>
    
    @if($description)
        <p class="text-xs text-on-surface/60 dark:text-on-surface-dark/60 pl-6">
            {{ $description }}
        </p>
    @endif
    
    @error($name)
        <span class="text-xs text-danger">{{ $message }}</span>
    @enderror
</div>