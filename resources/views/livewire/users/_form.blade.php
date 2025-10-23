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
      <x-input name="password" type="password" label="Contraseña" placeholder="••••••••" autocomplete="new-password" wire:model.defer="password" />
    @else
      <x-input name="password" type="password" label="Contraseña (opcional)" placeholder="••••••••" autocomplete="new-password" wire:model.defer="password" />
    @endif
  </div>

  <!-- Información Policial -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <x-input name="rango" label="Rango" placeholder="Ej: Cabo, Sargento" wire:model.defer="rango" />
    <x-input name="numero_escalafon" label="Número de Escalafón" placeholder="Número de escalafón" wire:model.defer="numero_escalafon" />
    <x-input name="fecha_ingreso" type="date" label="Fecha de Ingreso" wire:model.defer="fecha_ingreso" />
  </div>

  <!-- Rol y Permisos -->
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <x-select name="role" label="Rol" required wire:model.defer="role">
      <option value="policia">Policía</option>
      <option value="furriel">Furriel</option>
      <option value="admin">Administrador</option>
    </x-select>

    <x-checkbox name="can_login" label="Puede iniciar sesión" description="Habilitar acceso al sistema para este usuario" wire:model.defer="can_login" />
  </div>
</div>
