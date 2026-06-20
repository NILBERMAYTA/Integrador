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

  <div class="space-y-3">
    <label class="block text-sm font-medium">Imagen del articulo</label>
    <label
      for="foto-articulo-upload"
      class="flex cursor-pointer items-center gap-4 rounded-[var(--radius-radius)] border-2 border-dashed border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] p-4 transition-colors hover:border-[var(--color-primary)]"
    >
      <div class="h-24 w-24 shrink-0 overflow-hidden rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] bg-[var(--color-surface-alt)] dark:bg-[var(--color-surface-dark-alt)] flex items-center justify-center">
        @if (!empty($foto))
          <img src="{{ $foto->temporaryUrl() }}" alt="Imagen del articulo" class="h-full w-full object-cover" />
        @elseif (!empty($foto_actual) && \Illuminate\Support\Facades\Storage::disk('public')->exists($foto_actual))
          <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($foto_actual) }}" alt="Imagen del articulo" class="h-full w-full object-cover" />
        @else
          <span class="px-2 text-center text-xs opacity-70">Sin imagen</span>
        @endif
      </div>

      <div class="min-w-0 flex-1">
        <p class="text-sm font-medium text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">Seleccionar imagen</p>
        <p class="mt-1 text-xs opacity-70">Formatos permitidos: JPG, PNG o WEBP. Tamano maximo: 2 MB.</p>
        @if (!empty($foto))
          <p class="mt-2 truncate text-xs font-medium text-[var(--color-primary)]">{{ $foto->getClientOriginalName() }}</p>
        @endif
      </div>
    </label>

    <input id="foto-articulo-upload" type="file" accept="image/*" wire:model="foto" class="sr-only" />

    @error('foto')
      <p class="text-xs text-[var(--color-danger)]">{{ $message }}</p>
    @enderror
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
