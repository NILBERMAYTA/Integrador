<div class="w-full max-w-5xl mx-auto p-6 space-y-6">
  @if ($mode === 'cantidad')
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold">Registrar articulo consumible</h1>
          <p class="mt-1 text-sm opacity-70">Estos articulos se gestionan de forma agregada sin numeros de serie individuales.</p>
        </div>
        <a href="{{ route('articulos.index') }}" wire:navigate class="inline-flex items-center gap-2 whitespace-nowrap rounded-[var(--radius-radius)] border px-4 py-2 text-sm font-medium">
          Cancelar
        </a>
      </div>

      <div class="flex items-center gap-3 text-sm">
        <div class="px-3 py-1 rounded-full {{ $step >= 1 ? 'bg-[var(--color-primary)] text-[var(--color-on-primary)]' : 'bg-[var(--color-surface-alt)]' }}">1. Definicion</div>
        <div class="h-0.5 w-8 {{ $step >= 2 ? 'bg-[var(--color-primary)]' : 'bg-[var(--color-outline)]' }}"></div>
        <div class="px-3 py-1 rounded-full {{ $step >= 2 ? 'bg-[var(--color-primary)] text-[var(--color-on-primary)]' : 'bg-[var(--color-surface-alt)]' }}">2. Stock inicial</div>
      </div>

      @if ($errors->any())
        <div class="p-4 rounded-[var(--radius-radius)] bg-[var(--color-danger)]/10 border border-[var(--color-danger)] text-[var(--color-danger)]">
          <ul class="space-y-1 list-disc list-inside">
            @foreach ($errors->all() as $error)
              <li class="text-sm">{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      @if ($step === 1)
        <form wire:submit.prevent="saveStep1" class="space-y-6 p-6 bg-[var(--color-surface)] rounded-[var(--radius-radius)] border">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-form.select label="Categoria" wire:model.defer="categoria_id" required>
              <option value="">Seleccione una categoria</option>
              @foreach($categorias as $cat)
                <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
              @endforeach
            </x-form.select>

            <x-form.input label="Nombre del articulo" wire:model.defer="nombre" placeholder="Ej: Cartuchos 9mm" required />
          </div>

          <div>
            <label class="block text-sm font-medium mb-2">Descripcion (opcional)</label>
            <textarea wire:model.defer="descripcion" placeholder="Ej: Marca Federal, lote 2025" rows="3" class="w-full px-3 py-2 rounded-[var(--radius-radius)] border"></textarea>
          </div>

          <div class="space-y-2">
            <label class="block text-sm font-medium">Imagen del articulo</label>
            <label for="foto-articulo-cantidad" class="flex cursor-pointer items-center gap-4 rounded-[var(--radius-radius)] border-2 border-dashed border-[var(--color-outline)] bg-[var(--color-surface)] p-4 transition-colors hover:border-[var(--color-primary)]">
              <div class="h-24 w-24 shrink-0 overflow-hidden rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-[var(--color-surface-alt)] flex items-center justify-center">
                @if (!empty($foto))
                  <img src="{{ $foto->temporaryUrl() }}" alt="Imagen del articulo" class="h-full w-full object-cover" />
                @else
                  <span class="px-2 text-center text-xs opacity-70">Sin imagen</span>
                @endif
              </div>
              <div class="min-w-0 flex-1">
                <p class="text-sm font-medium">Seleccionar imagen</p>
                <p class="mt-1 text-xs opacity-70">JPG, PNG o WEBP. Maximo 2 MB.</p>
                @if (!empty($foto))
                  <p class="mt-2 truncate text-xs font-medium text-[var(--color-primary)]">{{ $foto->getClientOriginalName() }}</p>
                @endif
              </div>
            </label>
            <input id="foto-articulo-cantidad" type="file" accept="image/*" wire:model="foto" class="sr-only" />
            @error('foto')
              <p class="text-xs text-[var(--color-danger)]">{{ $message }}</p>
            @enderror
          </div>

          <div class="flex gap-3">
            <button type="submit" class="px-6 py-2.5 rounded-[var(--radius-radius)] bg-[var(--color-primary)] text-[var(--color-on-primary)] font-medium">Siguiente</button>
            <a href="{{ route('articulos.index') }}" wire:navigate class="px-6 py-2.5 rounded-[var(--radius-radius)] border font-medium">Atras</a>
          </div>
        </form>
      @endif

      @if ($step === 2)
        <form wire:submit.prevent="saveStep2Cantidad" class="space-y-6 p-6 bg-[var(--color-surface)] rounded-[var(--radius-radius)] border">
          <div class="p-4 rounded-[var(--radius-radius)] bg-[var(--color-primary)]/10 border border-[var(--color-primary)]/30 text-[var(--color-primary)] text-sm">
            <strong>{{ $nombre }}</strong> — Categoria: <strong>{{ $articulo?->categoria?->nombre ?? '—' }}</strong>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-form.input label="Cantidad inicial" type="number" step="0.01" wire:model.defer="cantidad_inicial" placeholder="Ej: 100" required />
            <x-form.input label="Stock minimo" type="number" step="0.01" wire:model.defer="stock_minimo" placeholder="Ej: 30" />
          </div>

          <x-form.input label="Fecha de ingreso" type="date" wire:model.defer="fecha_ingreso" />

          <div>
            <label class="block text-sm font-medium mb-2">Observaciones</label>
            <textarea wire:model.defer="obs_ingreso" placeholder="Ej: Recepcion de proveedor XYZ" rows="3" class="w-full px-3 py-2 rounded-[var(--radius-radius)] border"></textarea>
          </div>

          <div class="flex gap-3">
            <button type="submit" class="px-6 py-2.5 rounded-[var(--radius-radius)] bg-[var(--color-success)] text-[var(--color-on-success)] font-medium">Guardar y crear articulo</button>
            <button type="button" wire:click="$set('step', 1)" class="px-6 py-2.5 rounded-[var(--radius-radius)] border font-medium">Atras</button>
          </div>
        </form>
      @endif
    </div>
  @endif

  @if ($mode === 'serie')
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold">Registrar articulo reutilizable</h1>
          <p class="mt-1 text-sm opacity-70">Cada unidad se registra con su codigo de serie unico para rastreo individual.</p>
        </div>
        <a href="{{ route('articulos.index') }}" wire:navigate class="inline-flex items-center gap-2 whitespace-nowrap rounded-[var(--radius-radius)] border px-4 py-2 text-sm font-medium">Cancelar</a>
      </div>

      <div class="flex items-center gap-3 text-sm">
        <div class="px-3 py-1 rounded-full {{ $step >= 1 ? 'bg-[var(--color-success)] text-[var(--color-on-success)]' : 'bg-[var(--color-surface-alt)]' }}">1. Definicion</div>
        <div class="h-0.5 w-8 {{ $step >= 2 ? 'bg-[var(--color-success)]' : 'bg-[var(--color-outline)]' }}"></div>
        <div class="px-3 py-1 rounded-full {{ $step >= 2 ? 'bg-[var(--color-success)] text-[var(--color-on-success)]' : 'bg-[var(--color-surface-alt)]' }}">2. Unidades por serie</div>
      </div>

      @if ($errors->any())
        <div class="p-4 rounded-[var(--radius-radius)] bg-[var(--color-danger)]/10 border border-[var(--color-danger)] text-[var(--color-danger)]">
          <ul class="space-y-1 list-disc list-inside">
            @foreach ($errors->all() as $error)
              <li class="text-sm">{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      @if ($step === 1)
        <form wire:submit.prevent="saveStep1" class="space-y-6 p-6 bg-[var(--color-surface)] rounded-[var(--radius-radius)] border">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-form.select label="Categoria" wire:model.defer="categoria_id" required>
              <option value="">Seleccione una categoria</option>
              @foreach($categorias as $cat)
                <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
              @endforeach
            </x-form.select>

            <x-form.input label="Nombre del articulo" wire:model.defer="nombre" placeholder="Ej: Casco NIJ IIIA" required />
          </div>

          <div>
            <label class="block text-sm font-medium mb-2">Descripcion (opcional)</label>
            <textarea wire:model.defer="descripcion" placeholder="Ej: Marca Ballistic, color negro" rows="3" class="w-full px-3 py-2 rounded-[var(--radius-radius)] border"></textarea>
          </div>

          <div class="space-y-2">
            <label class="block text-sm font-medium">Imagen del articulo</label>
            <label for="foto-articulo-serie" class="flex cursor-pointer items-center gap-4 rounded-[var(--radius-radius)] border-2 border-dashed border-[var(--color-outline)] bg-[var(--color-surface)] p-4 transition-colors hover:border-[var(--color-success)]">
              <div class="h-24 w-24 shrink-0 overflow-hidden rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-[var(--color-surface-alt)] flex items-center justify-center">
                @if (!empty($foto))
                  <img src="{{ $foto->temporaryUrl() }}" alt="Imagen del articulo" class="h-full w-full object-cover" />
                @else
                  <span class="px-2 text-center text-xs opacity-70">Sin imagen</span>
                @endif
              </div>
              <div class="min-w-0 flex-1">
                <p class="text-sm font-medium">Seleccionar imagen</p>
                <p class="mt-1 text-xs opacity-70">JPG, PNG o WEBP. Maximo 2 MB.</p>
                @if (!empty($foto))
                  <p class="mt-2 truncate text-xs font-medium text-[var(--color-success)]">{{ $foto->getClientOriginalName() }}</p>
                @endif
              </div>
            </label>
            <input id="foto-articulo-serie" type="file" accept="image/*" wire:model="foto" class="sr-only" />
            @error('foto')
              <p class="text-xs text-[var(--color-danger)]">{{ $message }}</p>
            @enderror
          </div>

          <div class="flex gap-3">
            <button type="submit" class="px-6 py-2.5 rounded-[var(--radius-radius)] bg-[var(--color-success)] text-[var(--color-on-success)] font-medium">Siguiente</button>
            <a href="{{ route('articulos.index') }}" wire:navigate class="px-6 py-2.5 rounded-[var(--radius-radius)] border font-medium">Atras</a>
          </div>
        </form>
      @endif

      @if ($step === 2)
        <form wire:submit.prevent="saveStep2Serie" class="space-y-6 p-6 bg-[var(--color-surface)] rounded-[var(--radius-radius)] border">
          <div class="p-4 rounded-[var(--radius-radius)] bg-[var(--color-success)]/10 border border-[var(--color-success)]/30 text-[var(--color-success)] text-sm">
            <strong>{{ $nombre }}</strong> — Categoria: <strong>{{ $articulo?->categoria?->nombre ?? '—' }}</strong>
          </div>

          {{-- Generador opcional de codigos --}}
          <div class="rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-[var(--color-surface-alt)]/50 p-4">
            <div class="flex items-center justify-between gap-3">
              <div>
                <p class="text-sm font-semibold">Generador de series (opcional)</p>
                <p class="text-xs opacity-70">Crea codigos consecutivos con un prefijo y un numero inicial.</p>
              </div>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-3 md:grid-cols-4">
              <x-form.input label="Prefijo" wire:model.defer="serie_prefijo" placeholder="Ej: FAL-2024-" />
              <x-form.input label="Numero inicial" type="number" min="0" wire:model.defer="serie_inicio" placeholder="1" />
              <x-form.input label="Ceros (relleno)" type="number" min="0" max="12" wire:model.defer="serie_relleno" placeholder="4" />
              <x-form.input label="Cuantas" type="number" min="1" max="500" wire:model.defer="serie_cantidad" placeholder="Ej: 50" />
            </div>

            <div class="mt-3 flex flex-wrap gap-2">
              <button type="button" wire:click="generarSeries" class="inline-flex items-center gap-2 rounded-[var(--radius-radius)] bg-[var(--color-success)] px-4 py-2 text-sm font-medium text-[var(--color-on-success)]">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Generar y agregar
              </button>
              <button type="button" wire:click="limpiarSeries" class="inline-flex items-center gap-2 rounded-[var(--radius-radius)] border px-4 py-2 text-sm font-medium">
                Limpiar lista
              </button>
            </div>
            @error('serie_cantidad') <p class="mt-2 text-xs text-[var(--color-danger)]">{{ $message }}</p> @enderror
          </div>

          {{-- Lista de codigos --}}
          <div>
            <div class="mb-2 flex items-center justify-between">
              <label class="block text-sm font-medium">Codigos de serie <span class="text-[var(--color-danger)]">*</span></label>
              <span class="rounded-full bg-[var(--color-success)]/10 px-3 py-1 text-xs font-semibold text-[var(--color-success)]">
                {{ $seriesCount }} {{ $seriesCount === 1 ? 'unidad' : 'unidades' }}
              </span>
            </div>
            <textarea
              wire:model.live.debounce.400ms="series_input"
              rows="6"
              placeholder="Un codigo por linea, por ejemplo:&#10;FAL-2024-0001&#10;FAL-2024-0002&#10;FAL-2024-0003"
              class="w-full px-3 py-2 font-mono text-sm rounded-[var(--radius-radius)] border"
            ></textarea>
            <p class="mt-1 text-xs opacity-60">Puedes pegar una lista (un codigo por linea) o usar el generador. Cada codigo debe ser unico.</p>
            @error('series_input') <p class="mt-1 text-xs text-[var(--color-danger)]">{{ $message }}</p> @enderror
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-form.select label="Condicion inicial" wire:model.defer="condicion_inicial" required>
              <option value="bueno">Bueno</option>
              <option value="con_defectos">Con defectos</option>
              <option value="malo">Malo</option>
              <option value="inoperativo">Inoperativo</option>
            </x-form.select>
            <x-form.input label="Fecha de ingreso" type="date" wire:model.defer="fecha_ingreso" />
          </div>

          <div>
            <label class="block text-sm font-medium mb-2">Observaciones</label>
            <textarea wire:model.defer="obs_ingreso" placeholder="Ej: Lote recibido del proveedor XYZ" rows="2" class="w-full px-3 py-2 rounded-[var(--radius-radius)] border"></textarea>
          </div>

          <div class="flex gap-3">
            <button type="submit" class="px-6 py-2.5 rounded-[var(--radius-radius)] bg-[var(--color-success)] text-[var(--color-on-success)] font-medium" wire:loading.attr="disabled" wire:target="saveStep2Serie">
              <span wire:loading.remove wire:target="saveStep2Serie">Registrar {{ $seriesCount > 0 ? $seriesCount : '' }} {{ $seriesCount === 1 ? 'unidad' : 'unidades' }}</span>
              <span wire:loading wire:target="saveStep2Serie">Registrando…</span>
            </button>
            <button type="button" wire:click="$set('step', 1)" class="px-6 py-2.5 rounded-[var(--radius-radius)] border font-medium">Atras</button>
          </div>
        </form>
      @endif
    </div>
  @endif
</div>
