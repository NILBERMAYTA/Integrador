@php
    $selectedTheme = auth()->user()?->selectedTheme;
    $themeSlug = $selectedTheme?->slug ?? \App\Models\Theme::DEFAULT_DARK_SLUG;
    $themeAppearance = $selectedTheme?->appearance ?? 'dark';
    $lightThemeSlug = auth()->user()?->lightTheme?->slug ?? \App\Models\Theme::DEFAULT_LIGHT_SLUG;
    $darkThemeSlug = auth()->user()?->darkTheme?->slug ?? \App\Models\Theme::DEFAULT_DARK_SLUG;
@endphp

<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    data-theme="{{ $themeSlug }}"
    data-appearance="{{ $themeAppearance }}"
    class="{{ $themeAppearance === 'dark' ? 'dark' : '' }}"
>
    <head>
        @include('partials.head')
        <script>
            document.documentElement.dataset.theme = @js($themeSlug);
            window.Flux.applyAppearance(@js($themeAppearance));
            document.documentElement.classList.toggle('dark', @js($themeAppearance) === 'dark');
            document.documentElement.dataset.appearance = @js($themeAppearance);
        </script>
    </head>
    <body class="min-h-screen bg-surface dark:bg-surface-dark text-on-surface dark:text-on-surface-dark">
        <flux:sidebar sticky stashable class="border-e border-outline dark:border-outline-dark bg-surface dark:bg-surface-dark">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <a href="{{ auth()->user()->hasRole('policia') ? route('prestamos.index') : route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
                <x-app-logo />
            </a>

            <flux:navlist variant="outline">
                <flux:navlist.group :heading="__('Plataforma')" class="grid">
                    @role('policia')
                        @can('prestamos.view')
                            <flux:navlist.item icon="arrows-right-left" :href="route('prestamos.index')" :current="request()->routeIs('prestamos.*')" wire:navigate>Mis prestamos</flux:navlist.item>
                        @endcan
                    @else
                        @can('dashboard.view')
                            <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>{{ __('Inicio') }}</flux:navlist.item>
                        @endcan
                        @can('users.manage')
                            <flux:navlist.item icon="user" :href="route('users.index')" :current="request()->routeIs('users.*')" wire:navigate>Usuarios</flux:navlist.item>
                        @endcan
                        @can('units.manage')
                            <flux:navlist.item icon="building-office-2" :href="route('unidades.index')" :current="request()->routeIs('unidades.*')" wire:navigate>Unidades</flux:navlist.item>
                        @endcan
                        @can('articulos.manage')
                            <flux:navlist.item icon="fire" :href="route('articulos.index')" :current="request()->routeIs('articulos.*')" wire:navigate>Articulos</flux:navlist.item>
                        @endcan
                        @can('categorias.manage')
                            <flux:navlist.item icon="tag" :href="route('categorias.index')" :current="request()->routeIs('categorias.*')" wire:navigate>Categorias</flux:navlist.item>
                        @endcan
                        @can('prestamos.manage')
                            <flux:navlist.item icon="arrows-right-left" :href="route('prestamos.index')" :current="request()->routeIs('prestamos.*')" wire:navigate>Prestamos y devoluciones</flux:navlist.item>
                        @endcan
                        @can('mantenimientos.manage')
                            <flux:navlist.item icon="wrench-screwdriver" :href="route('mantenimientos.index')" :current="request()->routeIs('mantenimientos.*')" wire:navigate>Mantenimientos</flux:navlist.item>
                        @endcan
                        @can('predicciones.view')
                            <flux:navlist.item icon="chart-bar" :href="route('predicciones.index')" :current="request()->routeIs('predicciones.*')" wire:navigate>Predicciones</flux:navlist.item>
                        @endcan
                        @can('eventos.manage')
                            <flux:navlist.item icon="hand-raised" :href="route('eventos.index')" :current="request()->routeIs('eventos.*')" wire:navigate>Operativos</flux:navlist.item>
                        @endcan
                        @can('activity_logs.view')
                            <flux:navlist.item icon="clipboard-document-list" :href="route('activity-logs.index')" :current="request()->routeIs('activity-logs.*')" wire:navigate>Auditoria</flux:navlist.item>
                        @endcan
                    @endrole
                </flux:navlist.group>
            </flux:navlist>

            <flux:spacer />

            <button
                type="button"
                data-appearance-toggle
                data-toggle-url="{{ route('settings.appearance.toggle') }}"
                data-light-theme="{{ $lightThemeSlug }}"
                data-dark-theme="{{ $darkThemeSlug }}"
                data-appearance="{{ $themeAppearance }}"
                class="mb-2 flex size-10 items-center justify-center self-start rounded-lg border border-outline text-on-surface transition hover:bg-surface-alt focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary dark:border-outline-dark dark:text-on-surface-dark dark:hover:bg-surface-dark-alt dark:focus-visible:outline-primary-dark"
                aria-label="{{ $themeAppearance === 'dark' ? __('Cambiar a modo claro') : __('Cambiar a modo oscuro') }}"
                title="{{ $themeAppearance === 'dark' ? __('Cambiar a modo claro') : __('Cambiar a modo oscuro') }}"
            >
                <svg data-theme-icon="sun" class="{{ $themeAppearance === 'dark' ? '' : 'hidden' }} size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <circle cx="12" cy="12" r="4"></circle>
                    <path d="M12 2v2M12 20v2M4.93 4.93l1.42 1.42M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.42-1.42M17.66 6.34l1.41-1.41"></path>
                </svg>
                <svg data-theme-icon="moon" class="{{ $themeAppearance === 'dark' ? 'hidden' : '' }} size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z"></path>
                </svg>
            </button>

            <!-- Desktop User Menu -->
            <flux:dropdown class="hidden lg:block" position="bottom" align="start">
                <flux:profile
                    :name="auth()->user()->name"
                    :initials="auth()->user()->initials()"
                    icon:trailing="chevrons-up-down"
                />

                <flux:menu class="w-[220px]">
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-primary dark:bg-primary-dark text-on-primary dark:text-on-primary-dark"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold text-on-surface-strong dark:text-on-surface-dark-strong">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs text-on-surface dark:text-on-surface-dark">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Configuracion') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Cerrar sesion') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden bg-surface dark:bg-surface-dark border-b border-outline dark:border-outline-dark">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-primary dark:bg-primary-dark text-on-primary dark:text-on-primary-dark"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold text-on-surface-strong dark:text-on-surface-dark-strong">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs text-on-surface dark:text-on-surface-dark">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Configuracion') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Cerrar sesion') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @livewire('chatbot')

        @fluxScripts
    </body>
</html>
