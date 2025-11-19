@php($modo = $modo ?? 'create')

<div class="space-y-6">
  {{-- Básicos --}}
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <x-select name="categoria_id" label="Categoría" required wire:model.defer="categoria_id">
      <option value="">Seleccione una categoría</option>
      @foreach($categorias as $cat)
        <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
      @endforeach
    </x-select>

    <x-input name="nombre" label="Nombre del artículo" placeholder="Ej: Casco táctico M3" required wire:model.defer="nombre" />
  </div>

  {{-- Seguimiento / Tipo --}}
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <x-select name="seguimiento" label="Seguimiento" required wire:model.live="seguimiento">
      <option value="serie">Por serie (unidad con código)</option>
      <option value="cantidad">Por cantidad</option>
    </x-select>

    {{-- Si seguimiento = serie, tipo queda forzado a reutilizable (disabled) --}}
    <x-select name="tipo" label="Tipo" :disabled="$seguimiento === 'serie'" wire:model.defer="tipo">
      <option value="reutilizable">Reutilizable</option>
      <option value="consumible">Consumible</option>
    </x-select>
  </div>

  {{-- Unidad y descripción --}}
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <x-input name="unidad_medida" label="Unidad de medida" placeholder="unidad, caja, cartucho…" wire:model.defer="unidad_medida" />
    <div></div>
  </div>

  <div>
    <x-textarea name="descripcion" label="Descripción" rows="3" placeholder="Notas o especificaciones…" wire:model.defer="descripcion" />
  </div>

  {{-- Hint de coherencia --}}
  <div class="rounded-[var(--radius-radius)] border border-outline dark:border-outline-dark p-3 text-sm bg-surface-alt/60 dark:bg-surface-dark-alt/60">
    <span class="font-medium">Reglas:</span>
    <ul class="list-disc ml-5 mt-1 space-y-1">
      <li>Si <strong>Seguimiento = serie</strong>, el <strong>Tipo</strong> se fuerza a <em>reutilizable</em>.</li>
      <li>Los artículos por <strong>cantidad</strong> no gestionan series.</li>
    </ul>
  </div>
</div>
