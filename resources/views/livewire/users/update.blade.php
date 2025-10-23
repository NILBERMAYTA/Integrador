<div class="w-full max-w-4xl mx-auto p-6">
  <form wire:submit.prevent="actualizaruser" class="space-y-6 p-6 bg-surface dark:bg-surface-dark rounded-[var(--radius-radius)] shadow-md border border-outline dark:border-outline-dark">
    @csrf
    <h2 class="text-2xl font-bold mb-6">Editar Usuario</h2>

    @include('livewire.users._form', ['modo' => 'edit'])

    <div class="flex gap-3 pt-4 border-t border-outline dark:border-outline-dark">
      <button type="submit" class="btn btn-primary">Actualizar Usuario</button>
      <button type="button" onclick="window.history.back()" class="btn">Cancelar</button>
    </div>
  </form>
</div>
