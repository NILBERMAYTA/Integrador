@php($tipos = $tipos ?? ['preventivo','correctivo'])

<div class="space-y-6" x-data="{ articuloId: $wire.entangle('articulo_id') }">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <x-form.combobox
            name="articulo_id"
            label="Articulo"
            placeholder="Seleccione articulo"
            :options="$articulos->map(fn($a) => ['value' => $a->id, 'label' => $a->nombre])"
            wire:model.live="articulo_id"
            required
        />

        <x-form.combobox
            name="serie_id"
            label="Serie"
            placeholder="Seleccione serie"
            :options="$series ?? []"
            wire:model.live="serie_id"
            required
            x-bind:disabled="!articuloId"
            wire:key="serie-{{ $articulo_id ?? 'none' }}"
        />
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <x-form.combobox
            name="tipo"
            label="Tipo de mantenimiento"
            placeholder="Seleccione tipo"
            :options="collect($tipos)->map(fn($t) => ['value' => $t, 'label' => ucfirst($t)])"
            wire:model.defer="tipo"
            required
        />

        <x-form.datepiker
            name="fecha_inicio"
            label="Fecha inicio"
            placeholder="Seleccione fecha y hora"
            format="YYYY-MM-DD HH:mm"
            :allow-empty="false"
            wire:model.defer="fecha_inicio"
        />

        <x-form.datepiker
            name="fecha_fin"
            label="Fecha fin (si concluyo)"
            placeholder="Seleccione fecha y hora"
            format="YYYY-MM-DD HH:mm"
            :allow-empty="true"
            wire:model.defer="fecha_fin"
        />
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="md:col-span-2">
            <label class="text-sm font-medium text-on-surface dark:text-on-surface-dark">Descripcion (opcional)</label>
            <textarea
                name="descripcion"
                rows="3"
                class="w-full rounded-[var(--radius-radius)] border border-outline dark:border-outline-dark bg-surface dark:bg-surface-dark px-3 py-2 text-sm text-on-surface dark:text-on-surface-dark focus:outline-hidden focus:ring-2 focus:ring-primary dark:focus:ring-primary-dark focus:ring-offset-2 focus:ring-offset-surface dark:focus:ring-offset-surface-dark"
                placeholder="Notas del mantenimiento, hallazgos, etc."
                wire:model.defer="descripcion"
            ></textarea>
            @error('descripcion') <span class="text-xs text-danger">{{ $message }}</span> @enderror
        </div>

        <x-form.input
            name="costo"
            type="number"
            step="0.01"
            min="0"
            label="Costo (opcional)"
            placeholder="0.00"
            wire:model.defer="costo"
        />
    </div>
</div>
