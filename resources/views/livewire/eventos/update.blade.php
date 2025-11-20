<div class="w-full max-w-4xl mx-auto p-6">
    <form
        wire:submit.prevent="actualizarevento"
        class="space-y-6 p-6 bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] rounded-[var(--radius-radius)] shadow-md border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)]"
    >
        @csrf
        <h2 class="text-2xl font-bold mb-6">Editar Evento</h2>

        @include('livewire.eventos._form')

        <div class="flex gap-3 pt-4 border-t border-[var(--color-outline)] dark:border-[var(--color-outline-dark)]">
            <button type="submit" class="btn btn-primary">Actualizar</button>
            <a href="{{ route('eventos.index') }}" class="btn">Cancelar</a>
        </div>
    </form>
</div>
