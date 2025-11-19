<div class="w-full max-w-5xl mx-auto p-6 space-y-6">

  {{-- MODAL: Selección de tipo --}}
  @if ($showModal && !$mode)
    <div x-cloak x-show="true" x-transition.opacity.duration.200ms x-trap.inert.noscroll="true" 
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/20 p-4 backdrop-blur-md" 
         role="dialog" aria-modal="true">
      <div x-show="true" x-transition:enter="transition ease-out duration-200 delay-100" 
           x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100" 
           class="w-full max-w-2xl rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-[var(--color-surface)] shadow-xl dark:border-[var(--color-outline-dark)] dark:bg-[var(--color-surface-dark)]">
        
        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-[var(--color-outline)] bg-[var(--color-surface-alt)]/60 px-6 py-4 dark:border-[var(--color-outline-dark)] dark:bg-[var(--color-surface-dark-alt)]/20">
          <h2 class="text-xl font-semibold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">
            ¿Cómo deseas registrar el artículo?
          </h2>
          <a href="{{ route('articulos.index') }}" wire:navigate 
             class="rounded-full p-1 hover:bg-[var(--color-surface-alt)] dark:hover:bg-[var(--color-surface-dark-alt)] transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke="currentColor" fill="none" stroke-width="1.4" class="w-5 h-5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </a>
        </div>

        {{-- Body: Dos tarjetas --}}
        <div class="px-6 py-8 grid grid-cols-1 md:grid-cols-2 gap-6">
          
          {{-- Tarjeta 1: Por cantidad --}}
          <button type="button" wire:click="selectMode('cantidad')"
                  class="group h-full p-6 rounded-[var(--radius-radius)] border-2 border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] hover:border-[var(--color-primary)] dark:hover:border-[var(--color-primary-dark)] hover:bg-[var(--color-primary)]/5 dark:hover:bg-[var(--color-primary-dark)]/5 transition-all text-left">
            <div class="flex items-start gap-4">
              <div class="flex-shrink-0 mt-1">
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-[var(--color-primary)]/10 group-hover:bg-[var(--color-primary)]/20">
                  <svg class="w-6 h-6 text-[var(--color-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m0 0h6m-6 0h6"/>
                  </svg>
                </div>
              </div>
              <div class="flex-1">
                <h3 class="text-lg font-semibold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)] group-hover:text-[var(--color-primary)] dark:group-hover:text-[var(--color-primary-dark)] transition-colors">
                  Por cantidad
                </h3>
                <p class="mt-2 text-sm text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] opacity-75">
                  Ideal para municiones, granadas o artículos consumibles. Registra la cantidad inicial y el sistema gestiona el inventario de forma agregada.
                </p>
                <p class="mt-3 text-xs font-medium text-[var(--color-primary)] dark:text-[var(--color-primary-dark)]">
                  tipo: consumible | seguimiento: cantidad
                </p>
              </div>
            </div>
          </button>

          {{-- Tarjeta 2: Por serie --}}
          <button type="button" wire:click="selectMode('serie')"
                  class="group h-full p-6 rounded-[var(--radius-radius)] border-2 border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] hover:border-[var(--color-success)] dark:hover:border-[var(--color-success-dark)] hover:bg-[var(--color-success)]/5 dark:hover:bg-[var(--color-success-dark)]/5 transition-all text-left">
            <div class="flex items-start gap-4">
              <div class="flex-shrink-0 mt-1">
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-[var(--color-success)]/10 group-hover:bg-[var(--color-success)]/20">
                  <svg class="w-6 h-6 text-[var(--color-success)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                  </svg>
                </div>
              </div>
              <div class="flex-1">
                <h3 class="text-lg font-semibold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)] group-hover:text-[var(--color-success)] dark:group-hover:text-[var(--color-success-dark)] transition-colors">
                  Por serie (unidad)
                </h3>
                <p class="mt-2 text-sm text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] opacity-75">
                  Perfecto para cascos, escudos, chalecos o armas. Cada unidad se registra con su código de serie único para rastreo individual.
                </p>
                <p class="mt-3 text-xs font-medium text-[var(--color-success)] dark:text-[var(--color-success-dark)]">
                  tipo: reutilizable | seguimiento: serie
                </p>
              </div>
            </div>
          </button>
        </div>
      </div>
    </div>
  @endif

  {{-- WIZARD: Por cantidad --}}
  @if ($mode === 'cantidad')
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">
            Registrar artículo por cantidad
          </h1>
          <p class="mt-1 text-sm text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] opacity-70">
            Estos artículos se gestionan de forma agregada sin números de serie individuales.
          </p>
        </div>
        <a href="{{ route('articulos.index') }}" wire:navigate 
           class="inline-flex items-center gap-2 whitespace-nowrap rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] px-4 py-2 text-sm font-medium text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] hover:bg-[var(--color-surface-alt)] dark:hover:bg-[var(--color-surface-dark-alt)] transition-all">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
          Cancelar
        </a>
      </div>

      {{-- Steps --}}
      <div class="flex items-center gap-3 text-sm">
        <div class="px-3 py-1 rounded-full {{ $step >= 1 ? 'bg-[var(--color-primary)] text-[var(--color-on-primary)]' : 'bg-[var(--color-surface-alt)] dark:bg-[var(--color-surface-dark-alt)]' }}">
          1. Definición
        </div>
        <div class="h-0.5 w-8 {{ $step >= 2 ? 'bg-[var(--color-primary)]' : 'bg-[var(--color-outline)] dark:bg-[var(--color-outline-dark)]' }}"></div>
        <div class="px-3 py-1 rounded-full {{ $step >= 2 ? 'bg-[var(--color-primary)] text-[var(--color-on-primary)]' : 'bg-[var(--color-surface-alt)] dark:bg-[var(--color-surface-dark-alt)]' }}">
          2. Stock inicial
        </div>
      </div>

      @if (session()->has('success'))
        <div class="p-4 rounded-[var(--radius-radius)] bg-[var(--color-success)]/10 border border-[var(--color-success)] text-[var(--color-success)] flex items-center gap-3">
          <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <span>{{ session('success') }}</span>
        </div>
      @endif

      @if ($errors->any())
        <div class="p-4 rounded-[var(--radius-radius)] bg-[var(--color-danger)]/10 border border-[var(--color-danger)] text-[var(--color-danger)]">
          <ul class="space-y-1 list-disc list-inside">
            @foreach ($errors->all() as $error)
              <li class="text-sm">{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      {{-- PASO 1: Definición --}}
      @if ($step === 1)
        <form wire:submit.prevent="saveStep1" class="space-y-6 p-6 bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)]">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-form.select label="Categoría" wire:model.defer="categoria_id" required>
              <option value="">Seleccione una categoría</option>
              @foreach($categorias as $cat)
                <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
              @endforeach
            </x-form.select>

            <x-form.input label="Nombre del artículo" wire:model.defer="nombre" placeholder="Ej: Cartuchos 9mm" required />
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-form.input label="Unidad de medida" wire:model.defer="unidad_medida" placeholder="Ej: cartuchos, cajas" />
            <div></div>
          </div>

          <div>
            <label class="block text-sm font-medium text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] mb-2">Descripción (opcional)</label>
            <textarea wire:model.defer="descripcion" placeholder="Ej: Marca Federal, lote 2025" rows="3" class="w-full px-3 py-2 rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]"></textarea>
          </div>

          <div class="flex gap-3">
            <button type="submit" class="px-6 py-2.5 rounded-[var(--radius-radius)] bg-[var(--color-primary)] text-[var(--color-on-primary)] font-medium hover:opacity-90 transition-all">
              Siguiente
            </button>
            <button type="button" wire:click="$set('mode', null)" class="px-6 py-2.5 rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] font-medium hover:bg-[var(--color-surface-alt)] dark:hover:bg-[var(--color-surface-dark-alt)] transition-all">
              Atrás
            </button>
          </div>
        </form>
      @endif

      {{-- PASO 2: Stock inicial (cantidad) --}}
      @if ($step === 2)
        <form wire:submit.prevent="saveStep2Cantidad" class="space-y-6 p-6 bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)]">
          <div class="p-4 rounded-[var(--radius-radius)] bg-[var(--color-primary)]/10 border border-[var(--color-primary)]/30 text-[var(--color-primary)] text-sm">
            <strong>{{ $nombre }}</strong> — Categoría: <strong>{{ $articulo?->categoria?->nombre ?? '—' }}</strong>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-form.input label="Cantidad inicial" type="number" step="0.01" wire:model.defer="cantidad_inicial" placeholder="Ej: 100" required />
            <x-form.input label="Unidad de medida" wire:model.defer="unidad_medida" disabled />
          </div>

          <x-form.input label="Fecha de ingreso" type="date" wire:model.defer="fecha_ingreso" />

          <div>
            <label class="block text-sm font-medium text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] mb-2">Observaciones</label>
            <textarea wire:model.defer="obs_ingreso" placeholder="Ej: Recepción de proveedor XYZ" rows="3" class="w-full px-3 py-2 rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]"></textarea>
          </div>

          <div class="flex gap-3">
            <button type="submit" class="px-6 py-2.5 rounded-[var(--radius-radius)] bg-[var(--color-success)] text-[var(--color-on-success)] font-medium hover:opacity-90 transition-all">
              Guardar y crear artículo
            </button>
            <button type="button" wire:click="$set('step', 1)" class="px-6 py-2.5 rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] font-medium hover:bg-[var(--color-surface-alt)] dark:hover:bg-[var(--color-surface-dark-alt)] transition-all">
              Atrás
            </button>
          </div>
        </form>
      @endif
    </div>
  @endif

  {{-- WIZARD: Por serie --}}
  @if ($mode === 'serie')
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">
            Registrar artículo por serie
          </h1>
          <p class="mt-1 text-sm text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] opacity-70">
            Cada unidad se registra con su código de serie único para rastreo individual.
          </p>
        </div>
        <a href="{{ route('articulos.index') }}" wire:navigate 
           class="inline-flex items-center gap-2 whitespace-nowrap rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] px-4 py-2 text-sm font-medium text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] hover:bg-[var(--color-surface-alt)] dark:hover:bg-[var(--color-surface-dark-alt)] transition-all">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
          Cancelar
        </a>
      </div>

      {{-- Steps --}}
      <div class="flex items-center gap-3 text-sm">
        <div class="px-3 py-1 rounded-full {{ $step >= 1 ? 'bg-[var(--color-success)] text-[var(--color-on-success)]' : 'bg-[var(--color-surface-alt)] dark:bg-[var(--color-surface-dark-alt)]' }}">
          1. Definición
        </div>
        <div class="h-0.5 w-8 {{ $step >= 2 ? 'bg-[var(--color-success)]' : 'bg-[var(--color-outline)] dark:bg-[var(--color-outline-dark)]' }}"></div>
        <div class="px-3 py-1 rounded-full {{ $step >= 2 ? 'bg-[var(--color-success)] text-[var(--color-on-success)]' : 'bg-[var(--color-surface-alt)] dark:bg-[var(--color-surface-dark-alt)]' }}">
          2. Primera unidad
        </div>
      </div>

      @if (session()->has('success'))
        <div class="p-4 rounded-[var(--radius-radius)] bg-[var(--color-success)]/10 border border-[var(--color-success)] text-[var(--color-success)] flex items-center gap-3">
          <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <span>{{ session('success') }}</span>
        </div>
      @endif

      @if ($errors->any())
        <div class="p-4 rounded-[var(--radius-radius)] bg-[var(--color-danger)]/10 border border-[var(--color-danger)] text-[var(--color-danger)]">
          <ul class="space-y-1 list-disc list-inside">
            @foreach ($errors->all() as $error)
              <li class="text-sm">{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      {{-- PASO 1: Definición --}}
      @if ($step === 1)
        <form wire:submit.prevent="saveStep1" class="space-y-6 p-6 bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)]">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-form.select label="Categoría" wire:model.defer="categoria_id" required>
              <option value="">Seleccione una categoría</option>
              @foreach($categorias as $cat)
                <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
              @endforeach
            </x-form.select>

            <x-form.input label="Nombre del artículo" wire:model.defer="nombre" placeholder="Ej: Casco NIJ IIIA" required />
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-form.input label="Unidad de medida" wire:model.defer="unidad_medida" placeholder="unidad" />
            <div></div>
          </div>

          <div>
            <label class="block text-sm font-medium text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] mb-2">Descripción (opcional)</label>
            <textarea wire:model.defer="descripcion" placeholder="Ej: Marca Ballistic, color negro" rows="3" class="w-full px-3 py-2 rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]"></textarea>
          </div>

          <div class="flex gap-3">
            <button type="submit" class="px-6 py-2.5 rounded-[var(--radius-radius)] bg-[var(--color-success)] text-[var(--color-on-success)] font-medium hover:opacity-90 transition-all">
              Siguiente
            </button>
            <button type="button" wire:click="$set('mode', null)" class="px-6 py-2.5 rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] font-medium hover:bg-[var(--color-surface-alt)] dark:hover:bg-[var(--color-surface-dark-alt)] transition-all">
              Atrás
            </button>
          </div>
        </form>
      @endif

      {{-- PASO 2: Primera unidad (serie) --}}
      @if ($step === 2)
        <form wire:submit.prevent="saveStep2Serie" class="space-y-6 p-6 bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)]">
          <div class="p-4 rounded-[var(--radius-radius)] bg-[var(--color-success)]/10 border border-[var(--color-success)]/30 text-[var(--color-success)] text-sm">
            <strong>{{ $nombre }}</strong> — Categoría: <strong>{{ $articulo?->categoria?->nombre ?? '—' }}</strong>
          </div>

          <x-form.input label="Código de serie" wire:model.defer="codigo_serie" placeholder="Ej: ABC-0001" required />

          <x-form.input label="Fecha de ingreso" type="date" wire:model.defer="fecha_ingreso" />

          <div>
            <label class="block text-sm font-medium text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] mb-2">Observaciones</label>
            <textarea wire:model.defer="obs_ingreso" placeholder="Ej: Primera unidad recibida" rows="3" class="w-full px-3 py-2 rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]"></textarea>
          </div>

          <div class="flex gap-3">
            <button type="submit" class="px-6 py-2.5 rounded-[var(--radius-radius)] bg-[var(--color-success)] text-[var(--color-on-success)] font-medium hover:opacity-90 transition-all">
              Guardar y crear artículo
            </button>
            <button type="button" wire:click="$set('step', 1)" class="px-6 py-2.5 rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] font-medium hover:bg-[var(--color-surface-alt)] dark:hover:bg-[var(--color-surface-dark-alt)] transition-all">
              Atrás
            </button>
          </div>
        </form>
      @endif
    </div>
  @endif

</div>
