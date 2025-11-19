<div class="w-full max-w-4xl mx-auto p-6">
  <form wire:submit.prevent="actualizararticulo" class="space-y-6 p-6 bg-surface dark:bg-surface-dark rounded-[var(--radius-radius)] shadow-md border border-outline dark:border-outline-dark">
    @csrf
    <h2 class="text-2xl font-bold mb-6">Editar Artículo</h2>

    {{-- $categorias requerido igual que en create --}}
    @include('livewire.articulos._form', ['modo' => 'edit'])

    <div class="flex gap-3 pt-4 border-t border-outline dark:border-outline-dark">
      <button type="submit" class="btn btn-primary">Actualizar Artículo</button>
      <button type="button" onclick="window.history.back()" class="btn">Cancelar</button>
    </div>
  </form>

  {{-- Sub-CRUD de series (solo si seguimiento = serie y el artículo ya existe) --}}
  @if($articulo && ($seguimiento === 'serie'))
    <div class="mt-8 p-6 bg-surface dark:bg-surface-dark rounded-[var(--radius-radius)] shadow-md border border-outline dark:border-outline-dark">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-xl font-semibold">Series del artículo</h3>
        <div class="text-xs opacity-70">Código único por unidad</div>
      </div>

      {{-- Este es un placeholder: invoca tu propio componente Livewire de series --}}
      {{-- Ejemplo: @livewire('articulos.series', ['articulo' => $articulo], key('series-'.$articulo->id)) --}}
      <div class="text-sm text-on-surface dark:text-on-surface-dark opacity-80">
        (Aquí renderiza tu componente Livewire para gestionar series)
      </div>
    </div>
  @endif
</div>
