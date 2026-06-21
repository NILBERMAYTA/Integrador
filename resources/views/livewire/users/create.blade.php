<div class="mx-auto w-full max-w-4xl p-4 sm:p-6">
  <form wire:submit.prevent="guardaruser" class="card card-border user-form-enter bg-base-100 shadow-xl">
    <div class="card-body gap-6 p-5 sm:p-7">
    @csrf
    <div>
      <p class="text-xs font-semibold uppercase tracking-[0.18em] opacity-55">Gestión de personal</p>
      <h2 class="card-title mt-1 text-2xl">Registro de nuevo usuario</h2>
    </div>

    @include('livewire.users._form', ['modo' => 'create'])

    <div class="card-actions user-form-section justify-end gap-3 border-t border-base-300 pt-5">
      <button type="button" class="btn btn-ghost" onclick="window.history.back()">Cancelar</button>
      <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
        <span wire:loading.remove wire:target="guardaruser">Registrar usuario</span>
        <span wire:loading.flex wire:target="guardaruser" class="items-center gap-2">
          <span class="loading loading-spinner loading-sm"></span>
          Registrando
        </span>
      </button>
    </div>
    </div>
  </form>
</div>
