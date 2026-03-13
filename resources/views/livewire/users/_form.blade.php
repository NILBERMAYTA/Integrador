@php($modo = $modo ?? 'create')

<div class="space-y-6">
  <!-- Información Personal -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <x-input name="name" label="Nombre" placeholder="Ingrese el nombre" required wire:model.defer="name" />
    <x-input name="apellido_paterno" label="Apellido Paterno" placeholder="Apellido paterno" wire:model.defer="apellido_paterno" />
    <x-input name="apellido_materno" label="Apellido Materno" placeholder="Apellido materno" wire:model.defer="apellido_materno" />
  </div>

  <!-- Credenciales -->
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <x-input name="email" type="email" label="Correo Electrónico" placeholder="correo@ejemplo.com" autocomplete="email" wire:model.defer="email" />
    @if($modo === 'create')
      <x-form.password name="password" label="Contraseña" placeholder="********" autocomplete="new-password" wire:model.defer="password" />
    @else
      <x-form.password name="password" label="Contraseña (opcional)" placeholder="********" autocomplete="new-password" wire:model.defer="password" />
    @endif
  </div>

  <!-- Información Policial -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <x-input name="rango" label="Rango" placeholder="Ej: Cabo, Sargento" wire:model.defer="rango" />
    <x-input name="numero_escalafon" label="Número de Escalafón" placeholder="Número de escalafón" wire:model.defer="numero_escalafon" />
    <x-form.datepiker name="fecha_ingreso" label="Fecha de Ingreso" placeholder="Seleccione fecha" format="YYYY-MM-DD" wire:model.defer="fecha_ingreso" />
  </div>

  <!-- Foto -->
  <div class="flex flex-col md:flex-row md:items-center gap-4">
    <div class="h-16 w-16 rounded-full overflow-hidden bg-[var(--color-surface-alt)] dark:bg-[var(--color-surface-dark-alt)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] flex items-center justify-center">
      @if (!empty($foto))
        <img src="{{ $foto->temporaryUrl() }}" alt="Foto" class="h-full w-full object-cover" />
      @elseif (!empty($foto_actual))
        <img src="{{ asset('storage/'.$foto_actual) }}" alt="Foto" class="h-full w-full object-cover" />
      @else
        <span class="text-xs text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">Sin foto</span>
      @endif
    </div>
    <div class="flex-1">
      <label class="block text-sm font-medium mb-1">Foto</label>
      <input
        type="file"
        accept="image/*"
        wire:model="foto"
        class="block w-full rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] px-3 py-2 text-sm"
      />
      @error('foto')
        <p class="mt-1 text-xs text-[var(--color-danger)]">{{ $message }}</p>
      @enderror
    </div>
  </div>

  <!-- Rol y Permisos -->
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <x-form.combobox
      name="role"
      label="Rol"
      placeholder="Seleccione rol"
      :options="[
        ['value' => 'policia', 'label' => 'Policía'],
        ['value' => 'furriel', 'label' => 'Furriel'],
        ['value' => 'admin', 'label' => 'Administrador'],
      ]"
      required
      wire:model.defer="role"
    />

    <x-checkbox name="can_login" label="Puede iniciar sesión" description="Habilitar acceso al sistema para este usuario" wire:model.defer="can_login" />
  </div>
</div>
