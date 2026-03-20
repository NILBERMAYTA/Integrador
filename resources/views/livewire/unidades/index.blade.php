<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">
                Unidades
            </h1>
            <p class="mt-1 text-sm text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] opacity-70">
                Administra las unidades operativas del sistema.
            </p>
        </div>

        <a href="{{ route('unidades.create') }}" wire:navigate class="inline-flex items-center gap-2 whitespace-nowrap rounded-[var(--radius-radius)] bg-[var(--color-primary)] px-6 py-2.5 text-sm font-medium text-[var(--color-on-primary)]">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nueva Unidad
        </a>
    </div>

    <x-form.toast_notification :message="session('success')" variant="success" />
    <x-form.toast_notification :message="session('error')" variant="danger" />

    <div class="rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-[var(--color-surface)] p-4">
        <x-form.search
            name="search"
            placeholder="Buscar por nombre, sigla o descripcion..."
            wire:model.live.debounce.300ms="search"
        />
    </div>

    <div class="rounded-[var(--radius-radius)] border border-[var(--color-outline)] bg-[var(--color-surface)] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-[var(--color-surface-alt)] border-b border-[var(--color-outline)]">
                    <tr>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider">Sigla</th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider">Nombre</th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider">Descripcion</th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-center">Personal activo</th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-center">Series activas</th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--color-outline)]">
                    @forelse ($unidades as $unidad)
                        <tr class="hover:bg-[var(--color-surface-alt)] transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap font-medium">{{ $unidad->sigla ?? '—' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $unidad->nombre }}</td>
                            <td class="px-6 py-4">{{ $unidad->descripcion ?? '—' }}</td>
                            <td class="px-6 py-4 text-center">{{ $unidad->personal_activo_count }}</td>
                            <td class="px-6 py-4 text-center">{{ $unidad->series_activas_count }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2" x-data="{ modalIsOpen: false }">
                                    <a href="{{ route('unidades.update', $unidad) }}" wire:navigate class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[var(--radius-radius)] border border-[var(--color-outline)] text-sm">
                                        Editar
                                    </a>
                                    <button type="button" @click="modalIsOpen = true" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[var(--radius-radius)] border border-[var(--color-danger)] bg-[var(--color-danger)]/10 text-sm text-[var(--color-danger)]">
                                        Eliminar
                                    </button>

                                    <x-form.confirm-modal
                                        x-model="modalIsOpen"
                                        title="Confirmar Eliminacion"
                                        icon="danger"
                                        confirmText="Eliminar unidad"
                                        cancelText="Cancelar"
                                        :persistent="false"
                                        maxWidth="lg"
                                        @confirm="$wire.confirmarEliminacion({{ $unidad->id }}); modalIsOpen = false"
                                    >
                                        <p class="font-medium mb-2">Se eliminara la unidad {{ $unidad->nombre }}.</p>
                                        <p class="text-sm opacity-75">Si tiene informacion asociada, el sistema puede impedir la eliminacion.</p>
                                    </x-form.confirm-modal>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center opacity-70">
                                No hay unidades registradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($unidades->hasPages())
            <div class="px-6 py-4 border-t border-[var(--color-outline)] bg-[var(--color-surface-alt)]">
                {{ $unidades->links() }}
            </div>
        @endif
    </div>
</div>
