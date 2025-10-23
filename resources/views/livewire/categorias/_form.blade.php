@php($modo = $modo ?? 'create')

<div class="space-y-6">
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <x-input
      name="nombre"
      label="Nombre"
      placeholder="Ej: Protección, Disuasivos, Munición"
      required
      wire:model.defer="nombre"
    />

    {{-- Si tienes un componente x-textarea úsalo; si no, deja el <textarea> con tu estilo --}}
    @isset($useTextareaComponent)
      <x-textarea
        name="descripcion"
        label="Descripción"
        rows="3"
        placeholder="Descripción breve de la categoría"
        wire:model.defer="descripcion"
      />
    @else
      <div>
        <label class="block text-sm font-medium mb-1">Descripción</label>
        <textarea
          name="descripcion"
          rows="3"
          class="w-full rounded-[var(--radius-radius)] border border-outline dark:border-outline-dark px-3 py-2 bg-surface dark:bg-surface-dark"
          placeholder="Descripción breve de la categoría"
          wire:model.defer="descripcion"
        ></textarea>
      </div>
    @endisset
  </div>
</div>
