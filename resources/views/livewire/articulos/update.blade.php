<div class="space-y-6">
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h1 class="text-3xl font-bold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">
        Editar articulo
      </h1>
      <p class="text-sm text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] opacity-70">
        Actualiza la informacion del material registrado con la misma estructura visual del modulo principal.
      </p>
    </div>

    <div class="flex flex-wrap items-center gap-3">
      <x-form.header_button variant="neutral" href="{{ route('articulos.show', $articulo) }}" wire:navigate>
        Ver detalle
      </x-form.header_button>
      <x-form.header_button variant="neutral" href="{{ route('articulos.index') }}" wire:navigate>
        Volver
      </x-form.header_button>
    </div>
  </div>

  <form wire:submit.prevent="actualizararticulo" class="space-y-6">
    @csrf

    <div class="rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-[var(--color-surface)] p-6">
      <div class="mb-5 flex items-start justify-between gap-4 border-b border-[var(--color-outline)] pb-4">
        <div>
          <p class="text-xs uppercase tracking-wider opacity-60">Informacion general</p>
          <h2 class="mt-2 text-xl font-semibold text-[var(--color-on-surface-strong)]">
            Datos del articulo
          </h2>
        </div>

        <span class="inline-flex items-center rounded-full bg-[var(--color-primary)]/10 border border-[var(--color-primary)] px-3 py-1 text-xs font-medium text-[var(--color-primary)]">
          {{ ucfirst($articulo->tipo) }}
        </span>
      </div>

      @include('livewire.articulos._form', ['modo' => 'edit'])
    </div>

    <div class="flex flex-wrap items-center gap-3">
      <x-form.header_button variant="primary" type="submit">
        Actualizar articulo
      </x-form.header_button>
      <x-form.header_button variant="neutral" href="{{ route('articulos.index') }}" wire:navigate>
        Cancelar
      </x-form.header_button>
    </div>
  </form>

  @if($articulo && $articulo->isSerializado())
    <div class="rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-[var(--color-surface)] overflow-hidden">
      <div class="p-4 border-b border-[var(--color-outline)] bg-[var(--color-surface-alt)]">
        <h3 class="font-medium text-[var(--color-on-surface-strong)]">Series del articulo</h3>
        <p class="text-xs opacity-70">Cada unidad reutilizable debe mantener un codigo de serie unico para control institucional.</p>
      </div>

      <div class="p-5 text-sm text-[var(--color-on-surface)] opacity-80">
        Aqui puedes integrar el componente de gestion de series asociado a este articulo.
      </div>
    </div>
  @endif
</div>
