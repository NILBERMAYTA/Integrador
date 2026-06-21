@props([
    'label' => null,
    'name',
    'placeholder' => 'Seleccione fecha',
    'value' => null,
    'maxDate' => null,
    'required' => false,
])

@php
    $inputId = $attributes->get('id', $name);
    $wireAttributes = $attributes->whereStartsWith('wire:');
@endphp

<fieldset
    class="fieldset relative gap-1.5"
    x-data="{
        open: false,
        value: @js($value ?? ''),

        displayDate() {
            if (! this.value) return @js($placeholder);

            const [year, month, day] = this.value.split('-');
            return `${day}/${month}/${year}`;
        },

        selectDate(event) {
            this.sync(event.target.value);
            this.open = false;
        },

        clearDate() {
            this.sync('');
            this.open = false;
        },

        sync(nextValue) {
            this.value = nextValue || '';
            this.$refs.hidden.value = this.value;
            this.$refs.hidden.dispatchEvent(new Event('input', { bubbles: true }));
            this.$refs.hidden.dispatchEvent(new Event('change', { bubbles: true }));
        },
    }"
    x-on:keydown.escape.window="open = false"
>
    @if($label)
        <legend class="fieldset-legend text-sm">
            {{ $label }}
            @if($required)
                <span class="text-error">*</span>
            @endif
        </legend>
    @endif

    {{-- Botón trigger --}}
    <button
        id="{{ $inputId }}"
        type="button"
        class="input input-border flex w-full cursor-pointer items-center justify-between text-left"
        x-on:click="open = !open"
        x-bind:aria-expanded="open"
    >
        <span
            class="truncate"
            x-bind:class="value ? 'text-base-content' : 'text-base-content/50'"
            x-text="displayDate()"
        ></span>
        <svg class="size-5 shrink-0 text-base-content/60" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
    </button>

    <input
        type="hidden"
        name="{{ $name }}"
        x-ref="hidden"
        x-bind:value="value"
        {{ $wireAttributes }}
        @if($required) required @endif
    >

    {{-- Dropdown del calendario --}}
    <div
        class="absolute left-0 mt-1 z-[9999] bg-base-100 rounded-box border border-base-300 shadow-xl p-2"
        style="top: 100%; bottom: auto; min-width: 280px;"
        x-show="open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        x-on:click.outside="open = false"
        x-cloak
    >
        <calendar-date
            class="cally"
            locale="es-BO"
            first-day-of-week="1"
            x-bind:value="value"
            @if($maxDate) max="{{ $maxDate }}" @endif
            x-on:change="selectDate($event)"
        >
            <svg aria-label="Mes anterior" class="fill-current size-4" slot="previous" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path fill="currentColor" d="M15.75 19.5 8.25 12l7.5-7.5"></path>
            </svg>
            <svg aria-label="Mes siguiente" class="fill-current size-4" slot="next" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path fill="currentColor" d="m8.25 4.5 7.5 7.5-7.5 7.5"></path>
            </svg>
            <calendar-month></calendar-month>
        </calendar-date>

        <div class="mt-1 flex justify-end border-t border-base-300 pt-2">
            <button
                type="button"
                class="btn btn-ghost btn-xs"
                x-on:click="clearDate()"
                x-bind:disabled="! value"
            >
                Limpiar
            </button>
        </div>
    </div>

    @error($name)
        <p class="label text-error">{{ $message }}</p>
    @enderror
</fieldset>
