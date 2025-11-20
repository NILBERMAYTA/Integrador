<div>
    @if ($errors->any())
        <div class="p-3 mb-3 rounded-[var(--radius-radius)] bg-[var(--color-danger)]/10 dark:bg-[var(--color-danger)]/10 border border-[var(--color-danger)] dark:border-[var(--color-danger)] text-[var(--color-danger)] dark:text-[var(--color-danger)]">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form wire:submit.prevent="saveAjuste" class="space-y-4">
        <div class="text-sm text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">
            <strong>Artículo:</strong> {{ $articulo->nombre }}
        </div>

        {{-- Tipo de ajuste --}}
        <div>
            <label class="block text-sm font-medium text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] mb-1">Tipo de ajuste</label>
            <select wire:model.defer="tipo_ajuste" class="w-full px-3 py-2 rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] placeholder:text-[var(--color-on-surface)]/60 dark:placeholder:text-[var(--color-on-surface-dark)]/60 focus:ring-2 focus:ring-[var(--color-primary)] dark:focus:ring-[var(--color-primary-dark)] focus:border-transparent transition-all">
                <option value="positivo">Agregar (positivo)</option>
                <option value="negativo">Quitar (negativo)</option>
            </select>
        </div>

        @if($articulo->seguimiento === 'cantidad')
            <div>
                <label class="block text-sm font-medium text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] mb-1">Cantidad</label>
                <input wire:model.defer="cantidad" type="number" step="0.01" min="0" class="w-full px-3 py-2 rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] placeholder:text-[var(--color-on-surface)]/60 dark:placeholder:text-[var(--color-on-surface-dark)]/60 focus:ring-2 focus:ring-[var(--color-primary)] dark:focus:ring-[var(--color-primary-dark)] focus:border-transparent transition-all" />
            </div>
        @else
            <div>
                <label class="block text-sm font-medium text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] mb-1">Código de serie</label>
                <input wire:model.defer="codigo_serie" type="text" class="w-full px-3 py-2 rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] placeholder:text-[var(--color-on-surface)]/60 dark:placeholder:text-[var(--color-on-surface-dark)]/60 focus:ring-2 focus:ring-[var(--color-primary)] dark:focus:ring-[var(--color-primary-dark)] focus:border-transparent transition-all" />
            </div>
        @endif

        <div>
            <label class="block text-sm font-medium text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] mb-1">Fecha del ajuste</label>
            <input wire:model.defer="fecha_ajuste" type="datetime-local" class="w-full px-3 py-2 rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] placeholder:text-[var(--color-on-surface)]/60 dark:placeholder:text-[var(--color-on-surface-dark)]/60 focus:ring-2 focus:ring-[var(--color-primary)] dark:focus:ring-[var(--color-primary-dark)] focus:border-transparent transition-all" />
        </div>

        <div>
            <label class="block text-sm font-medium text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] mb-1">Observaciones</label>
            <textarea wire:model.defer="observaciones" rows="3" class="w-full px-3 py-2 rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] placeholder:text-[var(--color-on-surface)]/60 dark:placeholder:text-[var(--color-on-surface-dark)]/60 focus:ring-2 focus:ring-[var(--color-primary)] dark:focus:ring-[var(--color-primary-dark)] focus:border-transparent transition-all"></textarea>
        </div>

        <div class="flex justify-end gap-2 mt-3">
            <button type="button" @click="modalAjuste = false" class="px-4 py-2 rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] text-sm font-medium text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] hover:bg-[var(--color-surface-alt)] dark:hover:bg-[var(--color-surface-dark-alt)] transition-all">Cancelar</button>
            <button type="submit" class="px-4 py-2 rounded-[var(--radius-radius)] bg-[var(--color-primary)] dark:bg-[var(--color-primary-dark)] text-[var(--color-on-primary)] dark:text-[var(--color-on-primary-dark)] text-sm font-medium hover:opacity-90 transition-all">Registrar ajuste</button>
        </div>
    </form>
</div>
