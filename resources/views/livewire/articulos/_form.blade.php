@php($modo = $modo ?? 'create')

<div class="space-y-6">
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <x-select name="categoria_id" label="Categoria" required wire:model.defer="categoria_id">
      <option value="">Seleccione una categoria</option>
      @foreach($categorias as $cat)
        <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
      @endforeach
    </x-select>

    <x-input name="nombre" label="Nombre del articulo" placeholder="Ej: Casco tactico M3" required wire:model.defer="nombre" />
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <x-select name="tipo" label="Tipo" required wire:model.defer="tipo">
      <option value="reutilizable">Reutilizable</option>
      <option value="consumible">Consumible</option>
    </x-select>
    <div></div>
  </div>

  <div>
    <x-textarea name="descripcion" label="Descripcion" rows="3" placeholder="Notas o especificaciones..." wire:model.defer="descripcion" />
  </div>

  <div class="rounded-[var(--radius-radius)] border border-outline dark:border-outline-dark p-3 text-sm bg-surface-alt/60 dark:bg-surface-dark-alt/60">
    <span class="font-medium">Reglas:</span>
    <ul class="list-disc ml-5 mt-1 space-y-1">
      <li>Si el articulo es <strong>reutilizable</strong>, el sistema lo gestionara por <strong>serie</strong>.</li>
      <li>Si el articulo es <strong>consumible</strong>, el sistema lo gestionara por <strong>cantidad</strong>.</li>
    </ul>
  </div>
</div>
