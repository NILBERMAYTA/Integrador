<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Restablecer contrasena')" :description="__('Ingresa tu nueva contrasena')" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form method="POST" wire:submit="resetPassword" class="flex flex-col gap-6">
        <!-- Email Address -->
        <flux:input
            wire:model="email"
            :label="__('Correo electronico')"
            type="email"
            required
            autocomplete="email"
        />

        <!-- Password -->
        <flux:input
            wire:model="password"
            :label="__('Contrasena')"
            type="password"
            required
            autocomplete="new-password"
            :placeholder="__('Contrasena')"
            viewable
        />

        <!-- Confirm Password -->
        <flux:input
            wire:model="password_confirmation"
            :label="__('Confirmar contrasena')"
            type="password"
            required
            autocomplete="new-password"
            :placeholder="__('Confirmar contrasena')"
            viewable
        />

        <div class="flex items-center justify-end">
            <flux:button type="submit" variant="primary" class="w-full">
                {{ __('Restablecer contrasena') }}
            </flux:button>
        </div>
    </form>
</div>
