@props([
    'label' => null,
    'name',
    'placeholder' => 'Selecciona una fecha',
    'value' => null,
    'format' => 'YYYY-MM-DD',
    'required' => false,
    'allowEmpty' => true,
    'maxDate' => null,
    'placement' => 'bottom',
])

@php
    $inputId = $attributes->get('id', $name ?: 'datepicker_'.uniqid());
    $wireAttributes = $attributes->whereStartsWith('wire:');
    $panelPlacement = match ($placement) {
        'top' => 'absolute right-0 bottom-full z-[1100] mb-2 origin-bottom-right',
        'inline' => 'relative ml-auto mt-2 origin-top-right',
        default => 'absolute right-0 top-full z-[1100] mt-2 origin-top-right',
    };
@endphp

<fieldset
    class="fieldset relative gap-1.5"
    x-data="{
        open: false,
        value: @js($value),
        format: @js($format),
        maxDate: @js($maxDate),
        allowEmpty: @js($allowEmpty),
        month: null,
        year: null,
        selectedDay: null,
        days: [],
        blanks: [],
        monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
        weekDays: ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'],

        parseDate(raw) {
            if (! raw) return null;

            const match = String(raw).match(/^(\d{4})-(\d{2})-(\d{2})(?:[T ](\d{2}):(\d{2}))?$/);

            if (match) {
                return new Date(
                    Number(match[1]),
                    Number(match[2]) - 1,
                    Number(match[3]),
                    Number(match[4] ?? 0),
                    Number(match[5] ?? 0)
                );
            }

            const parsed = new Date(raw);
            return Number.isNaN(parsed.getTime()) ? null : parsed;
        },

        initializeCalendar() {
            const initial = this.parseDate(this.$refs.hidden.value || this.value);
            const context = initial ?? new Date();

            this.month = context.getMonth();
            this.year = context.getFullYear();
            this.selectedDay = initial?.getDate() ?? null;

            if (initial) this.value = this.formatDate(initial);
            this.calculateDays();
        },

        calculateDays() {
            if (this.month === null || this.month === '' || ! Number.isInteger(Number(this.month))) {
                this.month = new Date().getMonth();
            }

            if (this.year === null || this.year === '' || ! Number.isInteger(Number(this.year)) || Number(this.year) < 1900) {
                this.year = new Date().getFullYear();
            }

            this.month = Number(this.month);
            this.year = Number(this.year);

            const count = new Date(this.year, this.month + 1, 0).getDate();
            const offset = new Date(this.year, this.month, 1).getDay();

            this.days = Array.from({ length: count }, (_, index) => index + 1);
            this.blanks = Array.from({ length: offset }, (_, index) => index);
        },

        previousMonth() {
            if (this.month === 0) {
                this.month = 11;
                this.year--;
            } else {
                this.month--;
            }
            this.calculateDays();
        },

        nextMonth() {
            if (this.month === 11) {
                this.month = 0;
                this.year++;
            } else {
                this.month++;
            }
            this.calculateDays();
        },

        selectDay(day) {
            if (this.isDisabled(day)) return;

            const date = new Date(this.year, this.month, day);
            this.selectedDay = day;
            this.sync(this.formatDate(date));
            this.open = false;
        },

        selectToday() {
            const today = new Date();
            const maximum = this.parseDate(this.maxDate);
            const date = maximum && today > maximum ? maximum : today;

            this.month = date.getMonth();
            this.year = date.getFullYear();
            this.selectedDay = date.getDate();
            this.calculateDays();
            this.sync(this.formatDate(date));
            this.open = false;
        },

        clear() {
            this.selectedDay = null;
            this.sync('');
            this.open = false;
        },

        sync(nextValue) {
            this.value = nextValue;
            this.$refs.hidden.value = nextValue;
            this.$refs.hidden.dispatchEvent(new Event('input', { bubbles: true }));
            this.$refs.hidden.dispatchEvent(new Event('change', { bubbles: true }));
        },

        isSelected(day) {
            const selected = this.parseDate(this.value);
            return selected
                && selected.getFullYear() === this.year
                && selected.getMonth() === this.month
                && selected.getDate() === day;
        },

        isToday(day) {
            const today = new Date();
            return today.getFullYear() === this.year
                && today.getMonth() === this.month
                && today.getDate() === day;
        },

        isDisabled(day) {
            const maximum = this.parseDate(this.maxDate);
            if (! maximum) return false;

            maximum.setHours(23, 59, 59, 999);
            return new Date(this.year, this.month, day) > maximum;
        },

        formatDate(date) {
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');

            if (this.format === 'DD-MM-YYYY') return `${day}-${month}-${year}`;
            if (this.format === 'MM-DD-YYYY') return `${month}-${day}-${year}`;
            if (this.format === 'YYYY-MM-DD HH:mm') return `${year}-${month}-${day} ${hours}:${minutes}`;
            if (this.format === 'YYYY-MM-DDTHH:mm') return `${year}-${month}-${day}T${hours}:${minutes}`;

            return `${year}-${month}-${day}`;
        },
    }"
    x-init="initializeCalendar()"
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

    <div class="relative w-full">
        <label class="input input-bordered flex w-full items-center gap-2 bg-base-100 transition duration-200 focus-within:border-primary focus-within:outline-2 focus-within:outline-primary/20">
            <input
                id="{{ $inputId }}"
                type="text"
                class="min-w-0 grow cursor-pointer"
                x-model="value"
                x-on:click="open = ! open"
                placeholder="{{ $placeholder }}"
                readonly
                @if($required) required @endif
            >
            <button type="button" class="btn btn-ghost btn-sm btn-circle -mr-2" x-on:click="open = ! open" aria-label="Abrir calendario">
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5v14a2 2 0 002 2z"/>
                </svg>
            </button>
        </label>

        <input
            type="hidden"
            name="{{ $name }}"
            x-ref="hidden"
            value="{{ $value }}"
            {{ $wireAttributes }}
        >

        <div
            x-cloak
            x-show="open"
            x-on:click.outside="open = false"
            x-transition:enter="transition duration-200 ease-out"
            x-transition:enter-start="-translate-y-2 scale-95 opacity-0"
            x-transition:enter-end="translate-y-0 scale-100 opacity-100"
            x-transition:leave="transition duration-150 ease-in"
            x-transition:leave-start="translate-y-0 scale-100 opacity-100"
            x-transition:leave-end="-translate-y-2 scale-95 opacity-0"
            class="card card-border w-[19rem] max-w-full {{ $panelPlacement }} bg-base-100 shadow-2xl"
        >
            <div class="card-body gap-3 p-4">
                <div class="flex items-start justify-between gap-2 mb-2" x-on:click.stop>
                    <div class="flex gap-1">
                        <select class="select select-sm select-bordered w-28" x-model.number="month" x-on:change="calculateDays()" x-on:click.stop>
                            <template x-for="(name, index) in monthNames" :key="index">
                                <option :value="index" x-text="name"></option>
                            </template>
                        </select>
                        <input type="number" class="input input-sm input-bordered w-20" x-model.number="year" x-on:change="calculateDays()" x-on:click.stop>
                    </div>
                    <div class="flex gap-1">
                        <button type="button" class="btn btn-ghost btn-sm btn-circle" x-on:click="previousMonth()" aria-label="Mes anterior">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        <button type="button" class="btn btn-ghost btn-sm btn-circle" x-on:click="nextMonth()" aria-label="Mes siguiente">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-7">
                    <template x-for="dayName in weekDays" :key="dayName">
                        <span class="py-1 text-center text-xs font-semibold opacity-50" x-text="dayName"></span>
                    </template>
                </div>

                <div class="grid grid-cols-7 gap-1">
                    <template x-for="blank in blanks" :key="`blank-${blank}`">
                        <span class="size-8"></span>
                    </template>
                    <template x-for="day in days" :key="day">
                        <button
                            type="button"
                            class="btn btn-ghost btn-sm btn-circle h-8 min-h-8 w-8 transition duration-150 hover:-translate-y-0.5"
                            x-bind:class="{
                                'btn-primary': isSelected(day),
                                'btn-soft btn-primary': isToday(day) && ! isSelected(day),
                                'btn-disabled opacity-25': isDisabled(day),
                            }"
                            x-bind:disabled="isDisabled(day)"
                            x-on:click="selectDay(day)"
                            x-text="day"
                        ></button>
                    </template>
                </div>

                <div class="card-actions justify-between border-t border-base-300 pt-3">
                    @if($allowEmpty)
                        <button type="button" class="btn btn-ghost btn-xs" x-on:click="clear()">Limpiar</button>
                    @else
                        <span></span>
                    @endif
                    <button type="button" class="btn btn-soft btn-primary btn-xs" x-on:click="selectToday()">Hoy</button>
                </div>
            </div>
        </div>
    </div>

    @error($name)
        <p class="label text-error">{{ $message }}</p>
    @enderror
</fieldset>
