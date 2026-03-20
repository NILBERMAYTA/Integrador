<div class="w-full max-w-5xl mx-auto p-6 space-y-6">
  <form wire:submit.prevent="transferir" class="space-y-6 p-6 bg-surface dark:bg-surface-dark rounded-[var(--radius-radius)] shadow-md border border-outline dark:border-outline-dark">
    @csrf
    <h2 class="text-2xl font-bold">Transferir Usuario</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <x-input name="usuario" label="Usuario" :value="$user->nombre_completo" disabled />
      <x-input name="unidad_actual" label="Unidad actual" :value="$user->unidad?->nombre" disabled />
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <x-form.combobox
        name="unidad_destino_id"
        label="Nueva unidad"
        placeholder="Seleccione unidad"
        :options="collect($unidades)->map(fn($unidad) => ['value' => $unidad->id, 'label' => trim(($unidad->sigla ? $unidad->sigla.' - ' : '').$unidad->nombre)])->values()->all()"
        required
        wire:model.defer="unidad_destino_id"
      />

      <x-input name="motivo" label="Motivo" placeholder="Motivo de la transferencia" required wire:model.defer="motivo" />
    </div>

    <div class="flex gap-3 pt-4 border-t border-outline dark:border-outline-dark">
      <x-form.button type="submit">Transferir Usuario</x-form.button>
      <x-form.button type="button" variant="alternate" onclick="window.history.back()">Cancelar</x-form.button>
    </div>
  </form>

  <div class="p-6 bg-surface dark:bg-surface-dark rounded-[var(--radius-radius)] shadow-md border border-outline dark:border-outline-dark">
    <h3 class="text-xl font-semibold mb-4">Historial de transferencias</h3>

    <div class="overflow-x-auto">
      <table class="w-full text-left">
        <thead>
          <tr class="border-b border-outline dark:border-outline-dark">
            <th class="py-3">Fecha</th>
            <th class="py-3">Origen</th>
            <th class="py-3">Destino</th>
            <th class="py-3">Transferido por</th>
            <th class="py-3">Motivo</th>
          </tr>
        </thead>
        <tbody>
          @forelse($historial as $item)
            <tr class="border-b border-outline/50 dark:border-outline-dark/50">
              <td class="py-3">{{ optional($item->fecha_transferencia)->format('d/m/Y H:i') }}</td>
              <td class="py-3">{{ $item->unidadOrigen?->sigla ?? $item->unidadOrigen?->nombre ?? 'Inicial' }}</td>
              <td class="py-3">{{ $item->unidadDestino?->sigla ?? $item->unidadDestino?->nombre ?? '—' }}</td>
              <td class="py-3">{{ $item->transferidoPor?->nombre_completo ?? '—' }}</td>
              <td class="py-3">{{ $item->motivo ?? '—' }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="py-6 text-center text-sm opacity-70">No hay historial registrado.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
