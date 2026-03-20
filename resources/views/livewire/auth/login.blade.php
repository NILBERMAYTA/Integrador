<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Ingresa a tu cuenta')" :description="__('Ingresa tu correo y tu contrasena')" />

    <x-auth-session-status class="text-center" :status="session('status')" />

    <form method="POST" wire:submit="login" class="flex flex-col gap-6">
        <flux:input
            wire:model="email"
            :label="__('Correo electronico')"
            type="email"
            required
            autofocus
            autocomplete="email"
            placeholder="email@example.com"
        />

        <div class="relative">
            <flux:input
                wire:model="password"
                :label="__('Contrasena')"
                type="password"
                required
                autocomplete="current-password"
                :placeholder="__('Ingresa tu contrasena')"
                viewable
            />

            @if (Route::has('password.request'))
                <flux:link class="absolute top-0 text-sm end-0" :href="route('password.request')" wire:navigate>
                    {{ __('Olvide mi contrasena') }}
                </flux:link>
            @endif
        </div>

        <flux:checkbox wire:model="remember" :label="__('Recordarme')" />

        <div class="flex items-center justify-end">
            <flux:button variant="primary" type="submit" class="w-full" data-test="login-button">
                {{ __('Ingresar') }}
            </flux:button>
        </div>
    </form>
</div>
