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

  <div class="rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-[var(--color-surface-alt)]/70 p-4">
    <p class="text-xs uppercase tracking-wider opacity-60">Reglas de gestion</p>
    <div class="mt-3 grid gap-3 md:grid-cols-2">
      <div class="rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-[var(--color-surface)] p-4">
        <p class="text-sm font-semibold text-[var(--color-on-surface-strong)]">Reutilizable</p>
        <p class="mt-1 text-sm opacity-75">Se controla por serie y permite seguimiento individual de estado, condicion y custodio.</p>
      </div>
      <div class="rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-[var(--color-surface)] p-4">
        <p class="text-sm font-semibold text-[var(--color-on-surface-strong)]">Consumible</p>
        <p class="mt-1 text-sm opacity-75">Se consolida por cantidad y su disponibilidad se administra por unidad.</p>
      </div>
    </div>
  </div>
</div>
