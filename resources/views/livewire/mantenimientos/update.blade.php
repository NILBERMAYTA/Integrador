<div class="w-full max-w-4xl mx-auto p-6">
  <form wire:submit.prevent="actualizar" class="space-y-6 p-6 bg-surface dark:bg-surface-dark rounded-[var(--radius-radius)] shadow-md border border-outline dark:border-outline-dark">
    @csrf
    <h2 class="text-2xl font-bold mb-6">Editar Mantenimiento</h2>

    @include('livewire.mantenimientos._form', ['tipos' => ['preventivo','correctivo']])

    <div class="flex gap-3 pt-4 border-top border-outline dark:border-outline-dark">
      <x-form.button type="submit">Actualizar</x-form.button>
      <x-form.button type="button" variant="alternate" onclick="window.history.back()">Cancelar</x-form.button>
    </div>
  </form>
</div>
