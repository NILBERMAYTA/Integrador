<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-[var(--color-on-surface-strong)]">🔢 Paso 2: Registrar Series — Operación #{{ $operacion->id }}</h1>
            <p class="mt-1 text-sm text-[var(--color-on-surface)] opacity-70">Complete los números de serie para las unidades que requieren seguimiento por serie.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('prestamos.index') }}" wire:navigate class="inline-flex items-center gap-2 whitespace-nowrap rounded-[var(--radius-radius)] bg-[var(--color-surface)] border border-[var(--color-outline)] px-4 py-2 text-sm">Volver a Préstamos</a>
        </div>
    </div>

    <div class="space-y-4">
        @foreach($operacion->detalles as $detalle)
            <div class="bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] rounded-[var(--radius-radius)] shadow-sm border border-[var(--color-outline)] p-4">
                <h3 class="font-semibold text-[var(--color-on-surface-strong)]">{{ $detalle->nombre ?? optional($detalle->articulo)->nombre }} <span class="text-sm text-[var(--color-on-surface)] opacity-70">— Cantidad: {{ $detalle->cantidad }}</span></h3>
                <div class="mt-3 grid gap-2">
                    @for($i = 0; $i < $detalle->cantidad; $i++)
                        <div class="flex items-center gap-2">
                            <input wire:model.lazy="series.{{ $detalle->id }}.{{ $i }}" class="flex-1 rounded-[var(--radius-radius)] border border-[var(--color-outline)] px-3 py-2 bg-[var(--color-surface)]" placeholder="Escanear o ingresar serie #{{ $i + 1 }}" />
                            <button type="button" class="inline-flex items-center gap-2 rounded-[var(--radius-radius)] bg-primary text-on-primary px-3 py-2 text-sm">📷 Escanear</button>
                        </div>
                    @endfor
                </div>
            </div>
        @endforeach
    </div>

    <div class="flex justify-end">
        <button wire:click="saveSeries" class="inline-flex items-center px-4 py-2 rounded-[var(--radius-radius)] bg-primary text-on-primary">Finalizar Operación</button>
    </div>
</div>
