<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen antialiased auth-bg">
        <div class="auth-bg-content flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
            <main class="auth-panel kiro-scale-in flex w-full max-w-sm flex-col gap-2" aria-labelledby="auth-title">
                <a href="{{ route('home') }}" class="flex flex-col items-center gap-2 font-medium" wire:navigate>
                    <span class="flex h-9 w-9 mb-1 items-center justify-center rounded-md">
                        <x-app-logo-icon class="size-9 fill-current text-black dark:text-white" />
                    </span>
                    <span class="sr-only">{{ config('app.name', 'Laravel') }}</span>
                </a>
                <div class="flex flex-col gap-6">
                    {{ $slot }}
                </div>
            </main>
        </div>
        @fluxScripts
    </body>
</html>
