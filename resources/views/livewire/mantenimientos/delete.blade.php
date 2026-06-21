<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">
                Mantenimientos Eliminados
            </h1>
            <p class="mt-1 text-sm text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] opacity-70">
                Registros enviados a la papelera.
            </p>
        </div>
        <x-form.header_button variant="neutral" href="{{ route('mantenimientos.index') }}" wire:navigate>
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Volver
        </x-form.header_button>
    </div>

    <x-form.toast_notification :message="session('success')" variant="success" />
    <x-form.toast_notification :message="session('error')" variant="danger" />

    <div class="bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] rounded-[var(--radius-radius)] shadow-sm border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-[var(--color-surface-alt)] dark:bg-[var(--color-surface-dark-alt)] border-b border-[var(--color-outline)] dark:border-[var(--color-outline-dark)]">
                    <tr>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">Artículo</th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">Tipo</th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">Inicio</th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">Eliminado</th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--color-outline)] dark:divide-[var(--color-outline-dark)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] kiro-stagger">
                    @forelse($mantenimientos as $m)
                        <tr class="hover:bg-[var(--color-surface-alt)] dark:hover:bg-[var(--color-surface-dark-alt)] transition-colors">
                            <td class="px-6 py-4 text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">
                                {{ $m->articulo->nombre ?? 'Artículo' }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-[var(--color-secondary)]/10 text-[var(--color-secondary)]">
                                    {{ ucfirst($m->tipo) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">
                                {{ optional($m->fecha_inicio)->format('Y-m-d H:i') ?? '—' }}
                            </td>
                            <td class="px-6 py-4 text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">
                                {{ optional($m->deleted_at)->diffForHumans() }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <x-form.outline_button type="button" variant="success" wire:click="restaurar({{ $m->id }})">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                        </svg>
                                        Restaurar
                                    </x-form.outline_button>

                                    <x-form.outline_button type="button" variant="delete" wire:click="eliminarPermanentemente({{ $m->id }})">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                        Eliminar permanente
                                    </x-form.outline_button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <p class="text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">No hay mantenimientos eliminados.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($mantenimientos->hasPages())
            <div class="px-6 py-4 border-t border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] bg-[var(--color-surface-alt)] dark:bg-[var(--color-surface-dark-alt)]">
                {{ $mantenimientos->links() }}
            </div>
        @endif
    </div>
</div>
