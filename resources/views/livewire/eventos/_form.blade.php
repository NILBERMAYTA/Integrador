<div class="space-y-6">
	<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
		<x-input
			name="nombre"
			label="Nombre"
			placeholder="Ej: Ejercicio de tiro, Inspección general"
			required
			wire:model.defer="nombre"
		/>

		<div>
			<label class="block text-sm font-medium mb-1">Fecha inicio</label>
			<input
				type="date"
				name="fecha_inicio"
				wire:model.defer="fecha_inicio"
				class="w-full rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] px-3 py-2 bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] focus:ring-2 focus:ring-[var(--color-primary)] dark:focus:ring-[var(--color-primary-dark)] focus:border-transparent"
			/>
		</div>

		<div>
			<label class="block text-sm font-medium mb-1">Fecha fin</label>
			<input
				type="date"
				name="fecha_fin"
				wire:model.defer="fecha_fin"
				class="w-full rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] px-3 py-2 bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] focus:ring-2 focus:ring-[var(--color-primary)] dark:focus:ring-[var(--color-primary-dark)] focus:border-transparent"
			/>
		</div>

		<div>
			<label class="block text-sm font-medium mb-1">Nivel de severidad</label>
			<select
				name="nivel"
				wire:model.defer="nivel"
				class="w-full rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] px-3 py-2 bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] focus:ring-2 focus:ring-[var(--color-primary)] dark:focus:ring-[var(--color-primary-dark)] focus:border-transparent"
			>
				<option value="bajo">Bajo</option>
				<option value="medio">Medio</option>
				<option value="alto">Alto</option>
			</select>
			@error('nivel') <p class="mt-1 text-xs text-[var(--color-danger)]">{{ $message }}</p> @enderror
		</div>

		<div>
			<label class="block text-sm font-medium mb-1">Estado</label>
			<select
				name="estado"
				wire:model.defer="estado"
				class="w-full rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] px-3 py-2 bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] focus:ring-2 focus:ring-[var(--color-primary)] dark:focus:ring-[var(--color-primary-dark)] focus:border-transparent"
			>
				<option value="planificado">Planificado</option>
				<option value="activo">Activo</option>
				<option value="cerrado">Cerrado</option>
			</select>
			@error('estado') <p class="mt-1 text-xs text-[var(--color-danger)]">{{ $message }}</p> @enderror
		</div>

		{{-- Descripción --}}
		<div class="md:col-span-2">
			<label class="block text-sm font-medium mb-1">Descripción</label>
			<textarea
				name="descripcion"
				rows="3"
				wire:model.defer="descripcion"
				class="w-full rounded-[var(--radius-radius)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] px-3 py-2 bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] focus:ring-2 focus:ring-[var(--color-primary)] dark:focus:ring-[var(--color-primary-dark)] focus:border-transparent"
				placeholder="Descripción breve del evento"
			></textarea>
		</div>
	</div>
</div>
