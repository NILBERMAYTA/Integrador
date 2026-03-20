<div class="space-y-6">
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <x-input name="nombre" label="Nombre" placeholder="Ingrese el nombre de la unidad" required wire:model.defer="nombre" />
    <x-input name="sigla" label="Sigla" placeholder="Ej: UTOP-EA" wire:model.defer="sigla" />
  </div>

  <div>
    <label class="block text-sm font-medium mb-1">Descripcion</label>
    <textarea
      wire:model.defer="descripcion"
      rows="4"
      class="w-full rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] px-3 py-2 text-sm"
      placeholder="Descripcion breve de la unidad"
    ></textarea>
    @error('descripcion')
      <p class="mt-1 text-xs text-[var(--color-danger)]">{{ $message }}</p>
    @enderror
  </div>
</div>
