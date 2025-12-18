@props([
    'label' => null,
    'name',
    'placeholder' => 'Select date',
    'value' => null,
    'format' => 'YYYY-MM-DD',
    'required' => false,
    'allowEmpty' => true,
])

@php
    $inputId = $attributes->get('id', $name ? $name : 'datepicker_'.uniqid());
    $wireAttributes = $attributes->whereStartsWith('wire:');
@endphp

<div
    x-data="{
        datePickerOpen: false,
        datePickerValue: @js($value),
        datePickerFormat: @js($format),
        datePickerMonth: '',
        datePickerYear: '',
        datePickerDay: '',
        datePickerDaysInMonth: [],
        datePickerBlankDaysInMonth: [],
        datePickerMonthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
        datePickerDays: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
        allowEmpty: @js($allowEmpty),
        ensureContextDate() {
            const monthNum = Number(this.datePickerMonth);
            const yearNum = Number(this.datePickerYear);
            if (!isNaN(monthNum) && !isNaN(yearNum)) {
                return new Date(yearNum, monthNum, 1);
            }
            const today = new Date();
            this.datePickerMonth = today.getMonth();
            this.datePickerYear = today.getFullYear();
            this.datePickerDay = today.getDate();
            return today;
        },
        parseInput(val) {
            if (!val) return null;
            const normalized = val.replace(' ', 'T');
            const parsed = Date.parse(normalized);
            return isNaN(parsed) ? null : new Date(parsed);
        },
        syncHidden(newValue) {
            this.datePickerValue = newValue;
            if (this.$refs.hiddenInput) {
                this.$refs.hiddenInput.value = newValue;
                this.$refs.hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
        },
        init() {
            if (this.$refs.hiddenInput && this.$refs.hiddenInput.value && !this.datePickerValue) {
                this.datePickerValue = this.$refs.hiddenInput.value;
            }
            this.setInitialDate();
            this.datePickerCalculateDays();
        },
        datePickerSetToday() {
            const today = new Date();
            this.datePickerMonth = today.getMonth();
            this.datePickerYear = today.getFullYear();
            this.datePickerDay = today.getDate();
            this.syncHidden(this.datePickerFormatDate(today));
            this.datePickerCalculateDays();
            this.datePickerOpen = false;
        },
        setInitialDate() {
            const parsed = this.parseInput(this.datePickerValue);
            if (!parsed) {
                if (this.allowEmpty) {
                    this.datePickerValue = '';
                    this.datePickerMonth = '';
                    this.datePickerYear = '';
                    this.datePickerDay = '';
                    return;
                }
                const today = new Date();
                this.datePickerMonth = today.getMonth();
                this.datePickerYear = today.getFullYear();
                this.datePickerDay = today.getDate();
                this.syncHidden(this.datePickerFormatDate(today));
            } else {
                this.datePickerMonth = parsed.getMonth();
                this.datePickerYear = parsed.getFullYear();
                this.datePickerDay = parsed.getDate();
                this.syncHidden(this.datePickerFormatDate(parsed));
            }
        },
        datePickerDayClicked(day) {
            const base = this.parseInput(this.datePickerValue) ?? new Date();
            const selectedDate = new Date(this.datePickerYear, this.datePickerMonth, day, base.getHours(), base.getMinutes());
            this.datePickerDay = day;
            this.syncHidden(this.datePickerFormatDate(selectedDate));
            this.datePickerOpen = false;
        },
        datePickerPreviousMonth(){
            this.ensureContextDate();
            if (this.datePickerMonth === 0) {
                this.datePickerYear--;
                this.datePickerMonth = 12;
            }
            this.datePickerMonth--;
            this.datePickerCalculateDays();
        },
        datePickerNextMonth(){
            this.ensureContextDate();
            if (this.datePickerMonth === 11) {
                this.datePickerMonth = 0;
                this.datePickerYear++;
            } else {
                this.datePickerMonth++;
            }
            this.datePickerCalculateDays();
        },
        datePickerIsSelectedDate(day) {
            this.ensureContextDate();
            const d = new Date(this.datePickerYear, this.datePickerMonth, day);
            return this.datePickerValue === this.datePickerFormatDate(d);
        },
        datePickerIsToday(day) {
            const today = new Date();
            this.ensureContextDate();
            const d = new Date(this.datePickerYear, this.datePickerMonth, day);
            return today.toDateString() === d.toDateString();
        },
        datePickerCalculateDays() {
            this.ensureContextDate();
            const daysInMonth = new Date(this.datePickerYear, Number(this.datePickerMonth) + 1, 0).getDate();
            const dayOfWeek = new Date(this.datePickerYear, this.datePickerMonth).getDay();

            const blankdaysArray = [];
            for (let i = 1; i <= dayOfWeek; i++) {
                blankdaysArray.push(i);
            }

            const daysArray = [];
            for (let i = 1; i <= daysInMonth; i++) {
                daysArray.push(i);
            }

            this.datePickerBlankDaysInMonth = blankdaysArray;
            this.datePickerDaysInMonth = daysArray;
        },
        datePickerFormatDate(date) {
            const formattedDay = this.datePickerDays[date.getDay()];
            const formattedDate = ('0' + date.getDate()).slice(-2);
            const formattedMonth = this.datePickerMonthNames[date.getMonth()];
            const formattedMonthShortName = this.datePickerMonthNames[date.getMonth()].substring(0, 3);
            const formattedMonthInNumber = ('0' + (parseInt(date.getMonth()) + 1)).slice(-2);
            const formattedYear = date.getFullYear();
            const formattedHours = ('0' + date.getHours()).slice(-2);
            const formattedMinutes = ('0' + date.getMinutes()).slice(-2);

            if (this.datePickerFormat === 'M d, Y') {
                return `${formattedMonthShortName} ${formattedDate}, ${formattedYear}`;
            }
            if (this.datePickerFormat === 'MM-DD-YYYY') {
                return `${formattedMonthInNumber}-${formattedDate}-${formattedYear}`;
            }
            if (this.datePickerFormat === 'DD-MM-YYYY') {
                return `${formattedDate}-${formattedMonthInNumber}-${formattedYear}`;
            }
            if (this.datePickerFormat === 'YYYY-MM-DD') {
                return `${formattedYear}-${formattedMonthInNumber}-${formattedDate}`;
            }
            if (this.datePickerFormat === 'YYYY-MM-DD HH:mm') {
                return `${formattedYear}-${formattedMonthInNumber}-${formattedDate} ${formattedHours}:${formattedMinutes}`;
            }
            if (this.datePickerFormat === 'YYYY-MM-DDTHH:mm') {
                return `${formattedYear}-${formattedMonthInNumber}-${formattedDate}T${formattedHours}:${formattedMinutes}`;
            }
            if (this.datePickerFormat === 'D d M, Y') {
                return `${formattedDay} ${formattedDate} ${formattedMonthShortName} ${formattedYear}`;
            }

            return `${formattedMonth} ${formattedDate}, ${formattedYear}`;
        },
    }"
    x-cloak
    class="flex flex-col gap-2"
>
    @if($label)
        <label for="{{ $inputId }}" class="block text-sm font-medium text-neutral-500">
            {{ $label }}
            @if($required)
                <span class="text-danger">*</span>
            @endif
        </label>
    @endif

    <div class="relative w-full max-w-xs">
        <input
            id="{{ $inputId }}"
            type="text"
            @click="datePickerOpen=!datePickerOpen"
            x-model="datePickerValue"
            x-on:keydown.escape="datePickerOpen=false"
            class="flex px-3 py-2 w-full h-10 text-sm bg-surface rounded-md border text-on-surface border-outline ring-offset-background placeholder:text-on-surface/60 focus:border-outline focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:cursor-not-allowed disabled:opacity-50 dark:bg-surface-dark dark:text-on-surface-dark dark:border-outline-dark dark:placeholder:text-on-surface-dark/70 dark:focus:border-outline-dark dark:focus:ring-primary-dark dark:focus:ring-offset-surface-dark"
            placeholder="{{ $placeholder }}"
            readonly
            @if($required) required @endif
            x-ref="displayInput"
        />
        <input
            type="hidden"
            name="{{ $name }}"
            x-ref="hiddenInput"
            x-model="datePickerValue"
            @if($value) value="{{ $value }}" @endif
            {{ $wireAttributes }}
        >

        <div @click="datePickerOpen=!datePickerOpen; if(datePickerOpen){ $refs.displayInput.focus() }" class="absolute top-0 right-0 px-3 py-2 cursor-pointer text-on-surface/60 hover:text-on-surface dark:text-on-surface-dark/70 dark:hover:text-on-surface-dark">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
        </div>

        <div
            x-show="datePickerOpen"
            x-transition
            @click.outside="datePickerOpen = false"
            class="absolute top-0 left-0 max-w-lg p-4 mt-12 antialiased bg-surface border rounded-lg shadow w-[17rem] border-outline dark:bg-surface-dark dark:border-outline-dark dark:shadow-black/30"
        >
            <div class="flex justify-between items-center gap-2 mb-3">
                <div class="flex flex-col">
                    <span x-text="datePickerMonthNames[datePickerMonth]" class="text-lg font-bold text-on-surface dark:text-on-surface-dark"></span>
                    <div class="flex items-center gap-2 mt-1">
                        <input
                            type="number"
                            class="w-20 rounded-md border border-outline px-2 py-1 text-sm bg-surface-alt text-on-surface focus:outline-hidden focus:ring-2 focus:ring-primary dark:bg-surface-dark-alt dark:border-outline-dark dark:text-on-surface-dark dark:focus:ring-primary-dark"
                            x-model.number="datePickerYear"
                            @change="datePickerCalculateDays()"
                        />
                        <button type="button" class="text-xs text-on-surface/70 underline hover:text-on-surface dark:text-on-surface-dark/80 dark:hover:text-on-surface-dark" @click="datePickerSetToday()">Hoy</button>
                    </div>
                </div>
                <div class="flex gap-1">
                    <button @click="datePickerPreviousMonth()" type="button" class="inline-flex p-1 rounded-full transition duration-100 ease-in-out cursor-pointer focus:outline-none focus:shadow-outline hover:bg-surface-alt dark:hover:bg-surface-dark-alt">
                        <svg class="inline-flex w-6 h-6 text-on-surface/70 dark:text-on-surface-dark" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                    </button>
                    <button @click="datePickerNextMonth()" type="button" class="inline-flex p-1 rounded-full transition duration-100 ease-in-out cursor-pointer focus:outline-none focus:shadow-outline hover:bg-surface-alt dark:hover:bg-surface-dark-alt">
                        <svg class="inline-flex w-6 h-6 text-on-surface/70 dark:text-on-surface-dark" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </button>
                </div>
            </div>
            <div class="grid grid-cols-7 mb-3">
                <template x-for="(day, index) in datePickerDays" :key="index">
                    <div class="px-0.5">
                        <div x-text="day" class="text-xs font-medium text-center text-on-surface dark:text-on-surface-dark"></div>
                    </div>
                </template>
            </div>
            <div class="grid grid-cols-7">
                <template x-for="blankDay in datePickerBlankDaysInMonth">
                    <div class="p-1 text-sm text-center border border-transparent"></div>
                </template>
                <template x-for="(day, dayIndex) in datePickerDaysInMonth" :key="dayIndex">
                    <div class="px-0.5 mb-1 aspect-square">
                        <div
                            x-text="day"
                            @click="datePickerDayClicked(day)"
                            :class="{
                                'bg-surface-alt text-on-surface dark:bg-surface-dark-alt dark:text-on-surface-dark': datePickerIsToday(day),
                                'text-on-surface hover:bg-surface-alt dark:text-on-surface-dark dark:hover:bg-surface-dark-alt': !datePickerIsToday(day) && !datePickerIsSelectedDate(day),
                                'bg-primary text-on-primary hover:bg-primary/80 dark:bg-primary-dark dark:text-on-primary-dark dark:hover:bg-primary-dark/80': datePickerIsSelectedDate(day)
                            }"
                            class="flex justify-center items-center w-7 h-7 text-sm leading-none text-center rounded-full cursor-pointer"
                        ></div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    @error($name)
        <span class="text-xs text-danger">{{ $message }}</span>
    @enderror
</div>
