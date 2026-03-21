<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
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
                        @can('eventos.manage')
                            <flux:navlist.item icon="hand-raised" :href="route('eventos.index')" :current="request()->routeIs('eventos.*')" wire:navigate>Conflictos e incidencias</flux:navlist.item>
                        @endcan
                        @can('activity_logs.view')
                            <flux:navlist.item icon="clipboard-document-list" :href="route('activity-logs.index')" :current="request()->routeIs('activity-logs.*')" wire:navigate>Auditoria</flux:navlist.item>
                        @endcan
                    @endrole
                </flux:navlist.group>
            </flux:navlist>

            <flux:spacer />

            {{-- <flux:navlist variant="outline">
                <flux:navlist.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit" target="_blank">
                {{ __('Repository') }}
                </flux:navlist.item>

                <flux:navlist.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire" target="_blank">
                {{ __('Documentation') }}
                </flux:navlist.item>
            </flux:navlist> --}}

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

        @fluxScripts
    </body>
</html>
