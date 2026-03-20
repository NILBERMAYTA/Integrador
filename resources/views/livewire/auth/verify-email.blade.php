<div class="mt-4 flex flex-col gap-6">
    <flux:text class="text-center">
        {{ __('Verifica tu correo electronico haciendo clic en el enlace que te acabamos de enviar.') }}
    </flux:text>

    @if (session('status') == 'verification-link-sent')
        <flux:text class="text-center font-medium !dark:text-green-400 !text-green-600">
            {{ __('Se envio un nuevo enlace de verificacion al correo que registraste.') }}
        </flux:text>
    @endif

    <div class="flex flex-col items-center justify-between space-y-3">
        <flux:button wire:click="sendVerification" variant="primary" class="w-full">
            {{ __('Reenviar correo de verificacion') }}
        </flux:button>

        <flux:link class="text-sm cursor-pointer" wire:click="logout">
            {{ __('Cerrar sesion') }}
        </flux:link>
    </div>
</div>
