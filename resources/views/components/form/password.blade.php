@props([
    'label' => 'Password',
    'name' => 'password',
    'placeholder' => 'Ingrese la contraseña',
    'autocomplete' => 'current-password',
    'required' => false,
    'value' => null,
])

@php
    $inputId = $attributes->get('id', $name ? $name : 'password_'.uniqid());
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
    <div x-data="{ showPassword: false }" class="relative">
        <input
            x-bind:type="showPassword ? 'text' : 'password'"
            id="{{ $inputId }}"
            name="{{ $name }}"
            placeholder="{{ $placeholder }}"
            autocomplete="{{ $autocomplete }}"
            @if($required) required @endif
            @if($value) value="{{ $value }}" @endif
            {{ $attributes->merge([
                'class' => 'w-full rounded-[var(--radius-radius)] border border-outline bg-surface px-3 py-2 text-sm text-on-surface placeholder:text-on-surface/60 focus:outline-hidden focus:ring-2 focus:ring-primary focus:ring-offset-2 focus:ring-offset-surface disabled:cursor-not-allowed disabled:opacity-75 dark:border-outline-dark dark:bg-surface-dark dark:text-on-surface-dark dark:placeholder:text-on-surface-dark/70 dark:focus:ring-primary-dark dark:focus:ring-offset-surface-dark'
            ]) }}
        />
        <button
            type="button"
            x-on:click="showPassword = !showPassword"
            class="absolute right-2.5 top-1/2 -translate-y-1/2 text-on-surface/70 hover:text-on-surface dark:text-on-surface-dark/70 dark:hover:text-on-surface-dark"
            aria-label="Mostrar/Ocultar password"
        >
            <svg x-show="!showPassword" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" class="size-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
            </svg>
            <svg x-show="showPassword" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" class="size-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
            </svg>
        </button>
    </div>

    @error($name)
        <span class="text-xs text-danger">{{ $message }}</span>
    @enderror
</div>
