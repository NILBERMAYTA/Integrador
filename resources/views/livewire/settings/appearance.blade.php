<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout
        :heading="__('Apariencia')"
        :subheading="__('Configura los temas que usará el botón claro/oscuro de la barra lateral')"
        :wide="true"
    >
        @if(session('success'))
            <div class="alert mb-5 border border-[var(--color-success)] bg-[var(--color-success)]/10 text-[var(--color-success)]">
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @foreach(['light' => 'Modo claro', 'dark' => 'Modo oscuro'] as $appearance => $title)
            @php
                $preferredTheme = $appearance === 'light' ? $lightTheme : $darkTheme;
            @endphp

            <div class="{{ $loop->first ? '' : 'mt-8' }}">
                <div class="mb-4 flex items-center gap-3">
                    <div class="rounded-full bg-primary/10 p-2 text-primary dark:bg-primary-dark/10 dark:text-primary-dark">
                        @if($appearance === 'light')
                            <flux:icon.sun class="size-5" />
                        @else
                            <flux:icon.moon class="size-5" />
                        @endif
                    </div>
                    <div>
                        <h3 class="font-semibold text-on-surface-strong dark:text-on-surface-dark-strong">{{ $title }}</h3>
                        <p class="text-sm opacity-70">Elige el tema que se activará desde la barra lateral.</p>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach($themes->get($appearance, collect()) as $theme)
                        <button
                            type="button"
                            wire:key="theme-{{ $theme->id }}"
                            wire:click="selectTheme('{{ $theme->slug }}')"
                            data-theme="{{ $theme->slug }}"
                            class="group overflow-hidden rounded-box border-2 bg-base-100 text-left text-base-content shadow-sm transition hover:-translate-y-0.5 hover:shadow-md {{ $preferredTheme === $theme->slug ? 'border-primary ring-2 ring-primary/25' : 'border-base-300' }}"
                        >
                            <div class="flex items-center justify-between gap-2 border-b border-base-300 bg-base-200 px-4 py-3">
                                <div class="min-w-0">
                                    <p class="truncate font-semibold">{{ $theme->name }}</p>
                                    <p class="text-xs opacity-60">{{ $selectedTheme === $theme->slug ? 'En uso ahora' : $title }}</p>
                                </div>

                                @if($preferredTheme === $theme->slug)
                                    <span class="badge badge-primary badge-sm">Seleccionado</span>
                                @endif
                            </div>

                            <div class="space-y-3 p-4">
                                <div class="grid grid-cols-4 gap-2">
                                    <span class="h-8 rounded-field bg-primary"></span>
                                    <span class="h-8 rounded-field bg-secondary"></span>
                                    <span class="h-8 rounded-field bg-accent"></span>
                                    <span class="h-8 rounded-field bg-neutral"></span>
                                </div>

                                <div class="rounded-box border border-base-300 bg-base-200 p-3">
                                    <div class="h-2 w-3/4 rounded-full bg-base-content/70"></div>
                                    <div class="mt-2 h-2 w-1/2 rounded-full bg-base-content/30"></div>
                                    <div class="mt-3 flex gap-2">
                                        <span class="btn btn-primary btn-xs pointer-events-none">Accion</span>
                                        <span class="btn btn-ghost btn-xs pointer-events-none">Detalle</span>
                                    </div>
                                </div>
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>
        @endforeach
    </x-settings.layout>
</section>
