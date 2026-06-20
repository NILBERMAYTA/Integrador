<div class="space-y-6">
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h1 class="text-3xl font-bold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">
        Editar articulo
      </h1>
      <p class="text-sm text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] opacity-70">
        Gestion separada de datos generales, {{ $articulo->isSerializado() ? 'series' : 'stock' }} y movimientos.
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

  <x-form.toast_notification :message="session('success')" variant="success" />
  <x-form.toast_notification :message="session('error')" variant="danger" />

  <div class="flex flex-wrap gap-2 border-b border-[var(--color-outline)]">
    <button type="button" wire:click="$set('tab', 'datos')" class="px-4 py-2 text-sm font-semibold border-b-2 {{ $tab === 'datos' ? 'border-[var(--color-primary)] text-[var(--color-primary)]' : 'border-transparent opacity-70' }}">
      Datos generales
    </button>
    <button type="button" wire:click="$set('tab', '{{ $articulo->isSerializado() ? 'series' : 'stock' }}')" class="px-4 py-2 text-sm font-semibold border-b-2 {{ in_array($tab, ['series', 'stock'], true) ? 'border-[var(--color-primary)] text-[var(--color-primary)]' : 'border-transparent opacity-70' }}">
      {{ $articulo->isSerializado() ? 'Series' : 'Stock' }}
    </button>
    <button type="button" wire:click="$set('tab', 'historial')" class="px-4 py-2 text-sm font-semibold border-b-2 {{ $tab === 'historial' ? 'border-[var(--color-primary)] text-[var(--color-primary)]' : 'border-transparent opacity-70' }}">
      {{ $articulo->isSerializado() ? 'Historial' : 'Movimientos' }}
    </button>
  </div>

  @if($tab === 'datos')
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
  @endif

  @if($articulo->isSerializado() && $tab === 'series')
    <div class="rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-[var(--color-surface)] overflow-hidden">
      <div class="p-4 border-b border-[var(--color-outline)] bg-[var(--color-surface-alt)]">
        <h3 class="font-medium text-[var(--color-on-surface-strong)]">Agregar serie</h3>
      </div>
      <div class="grid gap-4 p-5 md:grid-cols-5">
        <x-form.select label="Unidad" wire:model.defer="nueva_serie_unidad_id" required>
          <option value="">Seleccione unidad</option>
          @foreach($unidades as $unidad)
            <option value="{{ $unidad->id }}">{{ $unidad->sigla ?: $unidad->nombre }}</option>
          @endforeach
        </x-form.select>
        <x-form.input label="Codigo de serie" wire:model.defer="nueva_serie_codigo" placeholder="ESC-001" required />
        <x-form.select label="Estado" wire:model.defer="nueva_serie_estado" required>
          @foreach($this->estadosSerie() as $estado)
            <option value="{{ $estado }}">{{ str_replace('_', ' ', ucfirst($estado)) }}</option>
          @endforeach
        </x-form.select>
        <x-form.select label="Condicion" wire:model.defer="nueva_serie_condicion" required>
          @foreach($this->condicionesSerie() as $condicion)
            <option value="{{ $condicion }}">{{ str_replace('_', ' ', ucfirst($condicion)) }}</option>
          @endforeach
        </x-form.select>
        <div class="flex items-end">
          <x-form.header_button variant="primary" type="button" wire:click="guardarSerie">
            Agregar serie
          </x-form.header_button>
        </div>
        <div class="md:col-span-5">
          <x-textarea name="nueva_serie_observaciones" label="Observaciones" rows="2" wire:model.defer="nueva_serie_observaciones" />
        </div>
      </div>
    </div>

    <div class="rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-[var(--color-surface)] overflow-hidden">
      <div class="p-4 border-b border-[var(--color-outline)] bg-[var(--color-surface-alt)]">
        <h3 class="font-medium text-[var(--color-on-surface-strong)]">Series registradas</h3>
        <p class="text-xs opacity-70">Las series con movimientos no se eliminan; se marcan como baja, perdida, robada o inoperativa.</p>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full min-w-[980px] text-left">
          <thead class="bg-[var(--color-surface-alt)] border-b border-[var(--color-outline)]">
            <tr>
              <th class="px-4 py-3 text-xs font-semibold uppercase">Serie</th>
              <th class="px-4 py-3 text-xs font-semibold uppercase">Unidad</th>
              <th class="px-4 py-3 text-xs font-semibold uppercase">Estado</th>
              <th class="px-4 py-3 text-xs font-semibold uppercase">Condicion</th>
              <th class="px-4 py-3 text-xs font-semibold uppercase">Observaciones</th>
              <th class="px-4 py-3 text-xs font-semibold uppercase text-right">Accion</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[var(--color-outline)]">
            @forelse($series as $serie)
              <tr>
                <td class="px-4 py-3"><input wire:model.defer="seriesForm.{{ $serie->id }}.codigo_serie" class="w-full rounded-[var(--radius-radius)] border border-[var(--color-outline)] px-3 py-2 text-sm" /></td>
                <td class="px-4 py-3">
                  <select wire:model.defer="seriesForm.{{ $serie->id }}.unidad_id" class="w-full rounded-[var(--radius-radius)] border border-[var(--color-outline)] px-3 py-2 text-sm">
                    @foreach($unidades as $unidad)
                      <option value="{{ $unidad->id }}">{{ $unidad->sigla ?: $unidad->nombre }}</option>
                    @endforeach
                  </select>
                </td>
                <td class="px-4 py-3">
                  <select wire:model.defer="seriesForm.{{ $serie->id }}.estado" class="w-full rounded-[var(--radius-radius)] border border-[var(--color-outline)] px-3 py-2 text-sm">
                    @foreach($this->estadosSerie() as $estado)
                      <option value="{{ $estado }}">{{ str_replace('_', ' ', ucfirst($estado)) }}</option>
                    @endforeach
                  </select>
                </td>
                <td class="px-4 py-3">
                  <select wire:model.defer="seriesForm.{{ $serie->id }}.condicion_actual" class="w-full rounded-[var(--radius-radius)] border border-[var(--color-outline)] px-3 py-2 text-sm">
                    @foreach($this->condicionesSerie() as $condicion)
                      <option value="{{ $condicion }}">{{ str_replace('_', ' ', ucfirst($condicion)) }}</option>
                    @endforeach
                  </select>
                </td>
                <td class="px-4 py-3"><input wire:model.defer="seriesForm.{{ $serie->id }}.observaciones" class="w-full rounded-[var(--radius-radius)] border border-[var(--color-outline)] px-3 py-2 text-sm" /></td>
                <td class="px-4 py-3 text-right">
                  <x-form.outline_button type="button" variant="success" wire:click="actualizarSerie({{ $serie->id }})">Guardar</x-form.outline_button>
                </td>
              </tr>
            @empty
              <tr><td colspan="6" class="px-6 py-10 text-center opacity-60">No hay series registradas.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  @endif

  @if($articulo->isCantidad() && $tab === 'stock')
    <div class="rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-[var(--color-surface)] overflow-hidden">
      <div class="p-4 border-b border-[var(--color-outline)] bg-[var(--color-surface-alt)]">
        <h3 class="font-medium text-[var(--color-on-surface-strong)]">Stock por unidad</h3>
        <p class="text-xs opacity-70">El stock actual se modifica por movimientos. Aqui solo se administra el minimo operativo.</p>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-left">
          <thead class="bg-[var(--color-surface-alt)] border-b border-[var(--color-outline)]">
            <tr>
              <th class="px-5 py-4 text-xs font-semibold uppercase">Unidad</th>
              <th class="px-5 py-4 text-xs font-semibold uppercase text-center">Stock actual</th>
              <th class="px-5 py-4 text-xs font-semibold uppercase text-center">Asignado</th>
              <th class="px-5 py-4 text-xs font-semibold uppercase">Stock minimo</th>
              <th class="px-5 py-4 text-xs font-semibold uppercase text-right">Accion</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[var(--color-outline)]">
            @forelse($inventarios as $inventario)
              <tr>
                <td class="px-5 py-4">{{ $inventario->unidad?->sigla ?? $inventario->unidad?->nombre ?? '-' }}</td>
                <td class="px-5 py-4 text-center">{{ number_format((float) $inventario->cantidad_disponible, 2) }}</td>
                <td class="px-5 py-4 text-center">{{ number_format((float) $inventario->cantidad_asignada, 2) }}</td>
                <td class="px-5 py-4">
                  <input wire:model.defer="stockMinimos.{{ $inventario->id }}" type="number" step="0.01" min="0" class="w-36 rounded-[var(--radius-radius)] border border-[var(--color-outline)] px-3 py-2 text-sm" />
                </td>
                <td class="px-5 py-4 text-right">
                  <x-form.outline_button type="button" variant="success" wire:click="actualizarStockMinimo({{ $inventario->id }})">Guardar</x-form.outline_button>
                </td>
              </tr>
            @empty
              <tr><td colspan="5" class="px-6 py-10 text-center opacity-60">No hay stock registrado para este articulo.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  @endif

  @if($tab === 'historial')
    <div class="rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-[var(--color-surface)] overflow-hidden">
      <div class="p-4 border-b border-[var(--color-outline)] bg-[var(--color-surface-alt)]">
        <h3 class="font-medium text-[var(--color-on-surface-strong)]">{{ $articulo->isSerializado() ? 'Historial' : 'Movimientos' }}</h3>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-left">
          <thead class="bg-[var(--color-surface-alt)] border-b border-[var(--color-outline)]">
            <tr>
              <th class="px-5 py-4 text-xs font-semibold uppercase">Tipo</th>
              <th class="px-5 py-4 text-xs font-semibold uppercase">Fecha</th>
              <th class="px-5 py-4 text-xs font-semibold uppercase text-center">Cantidad</th>
              <th class="px-5 py-4 text-xs font-semibold uppercase">Observacion</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[var(--color-outline)]">
            @forelse($movimientos as $movimiento)
              <tr>
                <td class="px-5 py-4">{{ $movimiento->operacion?->tipo ?? '-' }}</td>
                <td class="px-5 py-4">{{ optional($movimiento->operacion?->fecha)->format('d/m/Y H:i') ?? '-' }}</td>
                <td class="px-5 py-4 text-center">{{ $movimiento->cantidad ?? '-' }}</td>
                <td class="px-5 py-4">{{ $movimiento->observaciones ?? $movimiento->operacion?->observaciones ?? '-' }}</td>
              </tr>
            @empty
              <tr><td colspan="4" class="px-6 py-10 text-center opacity-60">No hay movimientos registrados.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  @endif
</div>
