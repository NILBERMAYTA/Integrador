@php($modo = $modo ?? 'create')

<div class="space-y-6">
  <div class="user-form-section grid grid-cols-1 gap-4 md:grid-cols-3">
    <x-input name="name" label="Nombre" placeholder="Ingrese el nombre" required wire:model.defer="name" />
    <x-input name="apellido_paterno" label="Apellido Paterno" placeholder="Apellido paterno" wire:model.defer="apellido_paterno" />
    <x-input name="apellido_materno" label="Apellido Materno" placeholder="Apellido materno" wire:model.defer="apellido_materno" />
  </div>

  <div class="user-form-section grid grid-cols-1 gap-4 md:grid-cols-2">
    <x-input name="email" type="email" label="Correo Electronico" placeholder="correo@ejemplo.com" autocomplete="email" wire:model.defer="email" />
    @if($modo === 'create')
      <x-form.password name="password" label="Contrasena" placeholder="********" autocomplete="new-password" wire:model.defer="password" />
    @else
      <x-form.password name="password" label="Contrasena (opcional)" placeholder="********" autocomplete="new-password" wire:model.defer="password" />
    @endif
  </div>

  <div class="user-form-section grid grid-cols-1 gap-4 md:grid-cols-3">
    <x-input name="rango" label="Rango" placeholder="Ej: Cabo, Sargento" wire:model.defer="rango" />
    <x-input name="numero_escalafon" label="Numero de Escalafon" placeholder="Numero de escalafon" wire:model.defer="numero_escalafon" />
    <x-form.datepiker
        name="fecha_ingreso"
        label="Fecha de Ingreso"
        placeholder="Seleccione fecha"
        :value="$fecha_ingreso"
        :max-date="now()->format('Y-m-d')"
        wire:model.defer="fecha_ingreso"
    />
  </div>

  <div class="user-form-section grid grid-cols-1 gap-4 md:grid-cols-2">
    @if($modo === 'create')
      <x-form.combobox
        name="unidad_id"
        label="Unidad actual"
        placeholder="Seleccione unidad"
        :options="collect($unidades ?? [])->map(fn($unidad) => ['value' => $unidad->id, 'label' => trim(($unidad->sigla ? $unidad->sigla.' - ' : '').$unidad->nombre)])->values()->all()"
        required
        wire:model.defer="unidad_id"
      />
    @elseif(!empty($unidad_id))
      <x-input name="unidad_actual" label="Unidad actual" :value="optional(collect($unidades ?? [])->firstWhere('id', $unidad_id))->nombre" disabled />
    @else
      <div></div>
    @endif

    <x-form.combobox
      name="role"
      label="Rol"
      placeholder="Seleccione rol"
      :options="$rolesDisponibles ?? []"
      required
      wire:model.defer="role"
    />
  </div>

  <div class="user-form-section space-y-3 w-full">
    <div>
      <label class="block text-sm font-medium mb-1">Foto</label>
      <label
        for="foto-upload"
        class="group flex cursor-pointer items-center gap-4 rounded-[var(--radius-radius)] border-2 border-dashed border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] p-4 transition-colors hover:border-[var(--color-primary)]"
      >
        <div class="h-24 w-24 overflow-hidden rounded-[var(--radius-radius)] bg-[var(--color-surface-alt)] dark:bg-[var(--color-surface-dark-alt)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] flex items-center justify-center shrink-0">
          @if (!empty($foto))
            <img src="{{ $foto->temporaryUrl() }}" alt="Foto" class="h-full w-full object-cover" />
          @elseif (!empty($foto_actual) && \Illuminate\Support\Facades\Storage::disk('public')->exists($foto_actual))
            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($foto_actual) }}" alt="Foto" class="h-full w-full object-cover" />
          @else
            <div class="text-center px-2">
              <div class="text-xs font-medium text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">Sin foto</div>
            </div>
          @endif
        </div>

        <div class="flex-1">
          <p class="text-sm font-medium text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">
            Arrastra una imagen aqui o haz clic para seleccionar
          </p>
          <p class="mt-1 text-xs text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] opacity-70">
            Formatos permitidos: JPG, PNG o WEBP. Tamano maximo: 2 MB.
          </p>
          @if (!empty($foto))
            <p class="mt-2 text-xs font-medium text-[var(--color-primary)]">
              {{ $foto->getClientOriginalName() }}
            </p>
          @endif
        </div>
      </label>

      <input
        id="foto-upload"
        type="file"
        accept="image/*"
        wire:model="foto"
        class="sr-only"
      />

      @error('foto')
        <p class="mt-1 text-xs text-[var(--color-danger)]">{{ $message }}</p>
      @enderror
    </div>
  </div>
</div>
