<div class="space-y-6">
    {{-- Header con tÃ­tulo --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <h1 class="text-3xl font-bold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">
            Auditoria del Sistema
        </h1>
        
        <div class="flex flex-wrap items-center gap-3">
            <x-form.header_button variant="export" type="button" wire:click="exportPdf">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 16l4-4m-4 4l-4-4m4 4V4m0 12v4m-7 0h14"/>
                </svg>
                Exportar PDF
            </x-form.header_button>

            @if($date_from || $date_to)
                <button 
                    wire:click="clearFilters" 
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-[var(--radius-radius)] bg-[var(--color-surface-alt)] dark:bg-[var(--color-surface-dark-alt)] text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] hover:bg-[var(--color-outline)] dark:hover:bg-[var(--color-outline-dark)] transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Limpiar Filtros
                </button>
            @endif
        </div>
    </div>

    {{-- Tabs --}}
    <div class="flex flex-wrap gap-2">
        <button
            wire:click="$set('tab','activity')"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-[var(--radius-radius)] border text-sm font-medium transition-colors {{ $tab === 'activity' ? 'bg-[var(--color-primary)] text-[var(--color-on-primary)] border-[var(--color-primary)]' : 'bg-[var(--color-surface-alt)] dark:bg-[var(--color-surface-dark-alt)] text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] hover:bg-[var(--color-outline)] dark:hover:bg-[var(--color-outline-dark)]' }}"
        >
            Actividad General
        </button>
        <button
            wire:click="$set('tab','logins')"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-[var(--radius-radius)] border text-sm font-medium transition-colors {{ $tab === 'logins' ? 'bg-[var(--color-primary)] text-[var(--color-on-primary)] border-[var(--color-primary)]' : 'bg-[var(--color-surface-alt)] dark:bg-[var(--color-surface-dark-alt)] text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] hover:bg-[var(--color-outline)] dark:hover:bg-[var(--color-outline-dark)]' }}"
        >
            Inicios de sesion
        </button>
    </div>

    {{-- Barra de filtros --}}
    <x-form.filter-bar>
        <div class="min-w-[160px]">
            <label class="block text-xs font-medium text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] mb-1.5">
                Desde
            </label>
            <input 
                type="date" 
                wire:model.live="date_from"
                class="w-full px-3 py-2 rounded-[var(--radius-radius)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)] dark:focus:ring-[var(--color-primary-dark)]"
            />
        </div>

        <div class="min-w-[160px]">
            <label class="block text-xs font-medium text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] mb-1.5">
                Hasta
            </label>
            <input 
                type="date" 
                wire:model.live="date_to"
                class="w-full px-3 py-2 rounded-[var(--radius-radius)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)] dark:focus:ring-[var(--color-primary-dark)]"
            />
        </div>
    </x-form.filter-bar>

    {{-- Tabla --}}
    <div class="bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] rounded-[var(--radius-radius)] shadow-sm border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-[var(--color-surface-alt)] dark:bg-[var(--color-surface-dark-alt)] border-b border-[var(--color-outline)] dark:border-[var(--color-outline-dark)]">
                    <tr>
                        <th class="px-6 py-4 text-xs font-semibold text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] uppercase tracking-wider cursor-pointer select-none hover:bg-[var(--color-surface)] dark:hover:bg-[var(--color-surface-dark)] transition-colors" wire:click="sortBy('created_at')">
                            <div class="flex items-center gap-2">
                                <span>Fecha/Hora</span>
                                @include('partials.sort-icon', ['field' => 'created_at', 'sortField' => $sortField, 'sortDirection' => $sortDirection])
                            </div>
                        </th>

                        <th class="px-6 py-4 text-xs font-semibold text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] uppercase tracking-wider">
                            Usuario
                        </th>

                        <th class="px-6 py-4 text-xs font-semibold text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] uppercase tracking-wider">
                            Acción
                        </th>

                        <th class="px-6 py-4 text-xs font-semibold text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] uppercase tracking-wider">
                            Modelo
                        </th>

                        @if($tab === 'logins')
                            <th class="px-6 py-4 text-xs font-semibold text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] uppercase tracking-wider">
                                Rol
                            </th>
                        @endif

                        <th class="px-6 py-4 text-xs font-semibold text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] uppercase tracking-wider">
                            Detalles
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-[var(--color-outline)] dark:divide-[var(--color-outline-dark)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)]">
                    @forelse ($activities as $activity)
                        <tr class="hover:bg-[var(--color-surface-alt)] dark:hover:bg-[var(--color-surface-dark-alt)] transition-colors" x-data="{ expanded: false }">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">
                                    {{ $activity->created_at->format('d/m/Y') }}
                                </div>
                                <div class="text-xs text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] opacity-75">
                                    {{ $activity->created_at->format('H:i:s') }}
                                </div>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($activity->causer)
                                    <div class="text-sm font-medium text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">
                                        {{ $activity->causer->nombre_completo }}
                                    </div>
                                    <div class="text-xs text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] opacity-75">
                                        {{ $activity->causer->email }}
                                    </div>
                                @else
                                    <span class="text-sm text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] opacity-50">Sistema</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col gap-1">
                                    @php
                                        $eventColors = [
                                            'created' => 'bg-green-500/20 text-green-700 dark:text-green-300',
                                            'updated' => 'bg-blue-500/20 text-blue-700 dark:text-blue-300',
                                            'deleted' => 'bg-red-500/20 text-red-700 dark:text-red-300',
                                            'login_success' => 'bg-emerald-500/20 text-emerald-700 dark:text-emerald-300',
                                            'login_failed' => 'bg-amber-500/20 text-amber-700 dark:text-amber-300',
                                            'login_locked' => 'bg-rose-500/20 text-rose-700 dark:text-rose-300',
                                        ];
                                        $eventColor = $eventColors[$activity->event] ?? 'bg-gray-500/20 text-gray-700 dark:text-gray-300';
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $eventColor }}">
                                        {{ ucfirst($activity->event) }}
                                    </span>
                                    <span class="text-xs text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] opacity-75">
                                        {{ $activity->description }}
                                    </span>
                                </div>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-[var(--color-primary)] dark:bg-[var(--color-primary-dark)] text-[var(--color-on-primary)] dark:text-[var(--color-on-primary-dark)]">
                                    {{ class_basename($activity->subject_type) }}
                                </span>
                                @if($activity->subject_id)
                                    <div class="text-xs text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] opacity-75 mt-1">
                                        ID: {{ $activity->subject_id }}
                                    </div>
                                @endif
                            </td>

                            @if($tab === 'logins')
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">
                                        {{ $activity->causer?->role ?? '—' }}
                                    </span>
                                </td>
                            @endif

                            <td class="px-6 py-4">
                                <button 
                                    @click="expanded = !expanded"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-[var(--radius-radius)] bg-[var(--color-surface-alt)] dark:bg-[var(--color-surface-dark-alt)] text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] hover:bg-[var(--color-outline)] dark:hover:bg-[var(--color-outline-dark)] transition-colors text-sm"
                                >
                                    <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': expanded }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                    <span x-text="expanded ? 'Ocultar' : 'Ver'"></span>
                                </button>

                                <div x-show="expanded" x-collapse class="mt-3 p-3 rounded-[var(--radius-radius)] bg-[var(--color-surface-alt)] dark:bg-[var(--color-surface-dark-alt)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)]">
                                    <div class="space-y-2 text-xs">
                                        @if($activity->properties->has('attributes') && count($activity->properties->get('attributes', [])) > 0)
                                            <div>
                                                <strong class="text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">Cambios:</strong>
                                                <pre class="mt-1 p-2 rounded bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] overflow-x-auto">{{ json_encode($activity->properties->get('attributes'), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                            </div>
                                        @endif

                                        @if($activity->properties->has('old') && count($activity->properties->get('old', [])) > 0)
                                            <div>
                                                <strong class="text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">Valores Anteriores:</strong>
                                                <pre class="mt-1 p-2 rounded bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] overflow-x-auto">{{ json_encode($activity->properties->get('old'), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                            </div>
                                        @endif

                                        @if($activity->properties->has('ip'))
                                            <div class="flex items-center gap-2 text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">
                                                <strong>IP:</strong>
                                                <span>{{ $activity->properties->get('ip') }}</span>
                                            </div>
                                        @endif

                                        @if($activity->properties->has('url'))
                                            <div class="flex items-center gap-2 text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">
                                                <strong>URL:</strong>
                                                <span class="truncate">{{ $activity->properties->get('url') }}</span>
                                            </div>
                                        @endif

                                        @if($activity->properties->has('user_agent'))
                                            <div class="flex items-center gap-2 text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)]">
                                                <strong>Navegador:</strong>
                                                <span class="truncate">{{ $activity->properties->get('user_agent') }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $tab === 'logins' ? 6 : 5 }}" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 01-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="mt-4 text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] font-medium">No hay registros de actividad</p>
                                <p class="mt-1 text-sm text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] opacity-60">Intenta ajustar los filtros de bÃºsqueda</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($activities->hasPages())
            <div class="px-6 py-4 border-t border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] bg-[var(--color-surface-alt)] dark:bg-[var(--color-surface-dark-alt)]">
                {{ $activities->links() }}
            </div>
        @endif
    </div>
</div>
