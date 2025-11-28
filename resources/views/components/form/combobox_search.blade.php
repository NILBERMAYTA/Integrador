@props([
    'label' => null,
    'name',
    'options' => [],
    'placeholder' => 'Please select',
    'searchPlaceholder' => 'Search',
    'noResultsText' => 'No matches found',
    'value' => null,
    'required' => false,
])

@php
    $normalizedOptions = collect($options)
        ->map(function ($option) {
            if (is_array($option)) {
                return [
                    'value' => $option['value'] ?? $option['label'] ?? null,
                    'label' => $option['label'] ?? $option['value'] ?? null,
                ];
            }

            return ['value' => $option, 'label' => $option];
        })
        ->filter(fn ($option) => $option['value'] !== null && $option['label'] !== null)
        ->values();

    $inputId = $attributes->get('id', $name ? $name : 'combobox_search_'.uniqid());
    $listboxId = $inputId.'-options';
    $wireAttributes = $attributes->whereStartsWith('wire:');
@endphp

<div
    x-data="{
        allOptions: @js($normalizedOptions),
        options: [],
        isOpen: false,
        openedWithKeyboard: false,
        selectedOption: null,
        placeholder: @js($placeholder),
        searchPlaceholder: @js($searchPlaceholder),
        showNoResults: false,
        initialValue: @js($value),
        init() {
            this.options = this.allOptions;

            if (this.initialValue) {
                const existing = this.allOptions.find((option) => option.value == this.initialValue);
                if (existing) {
                    this.setSelectedOption(existing, false);
                } else {
                    this.$refs.hiddenTextField.value = this.initialValue;
                }
            }
        },
        setSelectedOption(option, close = true) {
            this.selectedOption = option;
            this.$refs.hiddenTextField.value = option.value;

            if (close) {
                this.isOpen = false;
                this.openedWithKeyboard = false;
            }
        },
        getFilteredOptions(query) {
            this.options = this.allOptions.filter((option) =>
                option.label.toLowerCase().includes(query.toLowerCase()),
            );
            this.showNoResults = this.options.length === 0;
        },
        handleKeydownOnOptions(event) {
            if ((event.keyCode >= 65 && event.keyCode <= 90) || (event.keyCode >= 48 && event.keyCode <= 57) || event.keyCode === 8) {
                this.$refs.searchField.focus();
            }
        },
    }"
    class="flex w-full max-w-xs flex-col gap-1"
    x-on:keydown="handleKeydownOnOptions($event)"
    x-on:keydown.esc.window="isOpen = false; openedWithKeyboard = false"
>
    @if($label)
        <label for="{{ $inputId }}" class="w-fit pl-0.5 text-sm text-on-surface dark:text-on-surface-dark">
            {{ $label }}
            @if($required)
                <span class="text-danger">*</span>
            @endif
        </label>
    @endif

    <div class="relative">
        <button
            type="button"
            class="inline-flex w-full items-center justify-between gap-2 border border-outline rounded-radius bg-surface-alt px-4 py-2 text-sm font-medium tracking-wide text-on-surface transition hover:opacity-75 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary dark:border-outline-dark dark:bg-surface-dark-alt/50 dark:text-on-surface-dark dark:focus-visible:outline-primary-dark"
            role="combobox"
            aria-controls="{{ $listboxId }}"
            aria-haspopup="listbox"
            x-on:click="isOpen = ! isOpen"
            x-on:keydown.down.prevent="openedWithKeyboard = true"
            x-on:keydown.enter.prevent="openedWithKeyboard = true"
            x-on:keydown.space.prevent="openedWithKeyboard = true"
            x-bind:aria-expanded="isOpen || openedWithKeyboard"
            x-bind:aria-label="selectedOption ? selectedOption.value : placeholder"
        >
            <span class="text-sm font-normal" x-text="selectedOption ? selectedOption.value : placeholder"></span>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5" aria-hidden="true">
                <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/>
            </svg>
        </button>

        <input
            id="{{ $inputId }}"
            name="{{ $name }}"
            x-ref="hiddenTextField"
            type="hidden"
            @if($value) value="{{ $value }}" @endif
            @if($required) required @endif
            {{ $wireAttributes }}
        >

        <div
            x-show="isOpen || openedWithKeyboard"
            id="{{ $listboxId }}"
            class="absolute left-0 top-11 z-10 w-full overflow-hidden rounded-radius border border-outline bg-surface-alt dark:border-outline-dark dark:bg-surface-dark-alt"
            role="listbox"
            aria-label="{{ $label ?? $name }}"
            x-on:click.outside="isOpen = false; openedWithKeyboard = false"
            x-on:keydown.down.prevent="$focus.wrap().next()"
            x-on:keydown.up.prevent="$focus.wrap().previous()"
            x-transition
            x-trap="openedWithKeyboard"
        >
            <div class="relative">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke="currentColor" fill="none" stroke-width="1.5" class="absolute left-4 top-1/2 size-5 -translate-y-1/2 text-on-surface/50 dark:text-on-surface-dark/50" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                </svg>
                <input
                    type="text"
                    class="w-full border-b border-outline bg-surface-alt py-2.5 pl-11 pr-4 text-sm text-on-surface focus:outline-hidden focus-visible:border-primary disabled:cursor-not-allowed disabled:opacity-75 dark:border-outline-dark dark:bg-surface-dark-alt dark:text-on-surface-dark dark:focus-visible:border-primary-dark"
                    name="searchField"
                    aria-label="Search"
                    x-on:input="getFilteredOptions($el.value)"
                    x-ref="searchField"
                    x-on:keydown.stop
                    x-bind:placeholder="searchPlaceholder"
                />
            </div>

            <ul class="flex max-h-44 flex-col overflow-y-auto">
                <li class="px-4 py-2 text-sm text-on-surface dark:text-on-surface-dark" x-show="showNoResults">
                    <span x-text="noResultsText"></span>
                </li>
                <template x-for="(item, index) in options" x-bind:key="item.value">
                    <li
                        class="combobox-option inline-flex justify-between gap-6 bg-surface-alt px-4 py-2 text-sm text-on-surface hover:bg-surface-dark-alt/5 hover:text-on-surface-strong focus-visible:bg-surface-dark-alt/5 focus-visible:text-on-surface-strong focus-visible:outline-hidden dark:bg-surface-dark-alt dark:text-on-surface-dark dark:hover:bg-surface-alt/5 dark:hover:text-on-surface-dark-strong dark:focus-visible:bg-surface-alt/10 dark:focus-visible:text-on-surface-dark-strong"
                        role="option"
                        x-on:click="setSelectedOption(item)"
                        x-on:keydown.enter="setSelectedOption(item)"
                        x-bind:id="'option-' + index"
                        tabindex="0"
                    >
                        <span x-bind:class="selectedOption == item ? 'font-bold' : null" x-text="item.label"></span>
                        <span class="sr-only" x-text="selectedOption == item ? 'selected' : null"></span>
                        <svg x-cloak x-show="selectedOption == item" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke="currentColor" fill="none" stroke-width="2" class="size-4" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                        </svg>
                    </li>
                </template>
            </ul>
        </div>
    </div>

    @error($name)
        <span class="text-xs text-danger">{{ $message }}</span>
    @enderror
</div>
