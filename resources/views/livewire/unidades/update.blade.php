<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">
                Editar Unidad
            </h1>
            <p class="mt-1 text-sm text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] opacity-70">
                Actualiza la informacion general de la unidad.
            </p>
        </div>
    </div>

    <form wire:submit="actualizarUnidad" class="space-y-6 rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] p-6">
        @include('livewire.unidades._form')

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('unidades.index') }}" wire:navigate class="inline-flex items-center rounded-[var(--radius-radius)] border border-[var(--color-outline)] px-4 py-2 text-sm">
                Cancelar
            </a>
            <button type="submit" class="inline-flex items-center rounded-[var(--radius-radius)] bg-[var(--color-primary)] px-4 py-2 text-sm font-medium text-[var(--color-on-primary)]">
                Guardar cambios
            </button>
        </div>
    </form>
</div>
