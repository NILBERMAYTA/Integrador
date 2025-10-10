<div class="overflow-hidden w-full overflow-x-auto rounded-radius border border-outline dark:border-outline-dark">
    <table class="w-full text-left text-sm text-on-surface dark:text-on-surface-dark">
        <thead class="border-b border-outline bg-surface-alt text-sm text-on-surface-strong dark:border-outline-dark dark:bg-surface-dark-alt dark:text-on-surface-dark-strong">
            <tr>
                <th scope="col" class="p-4">Nro de Escalafon</th>
                <th scope="col" class="p-4">Apellidos</th>
                <th scope="col" class="p-4">Nombre</th>
                <th scope="col" class="p-4">Rango</th>
                <th scope="col" class="p-4">Rol</th>
                <th scope="col" class="p-4">Acciones</th>

            </tr>
        </thead>
        <tbody class="divide-y divide-outline dark:divide-outline-dark">
            @forelse ($users as $user)
                <tr>
                    <td class="p-4">{{ $user->numero_escalafon }}</td>
                    <td class="p-4">{{ $user->apellido_paterno }} {{ $user->apellido_materno }}</td>
                    <td class="p-4">{{ $user->name }}</td>
                    <td class="p-4">{{ $user->rango }}</td>
                    <td class="p-4">{{ $user->role }}</td>
                    <td class="p-4"><button type="button" class="whitespace-nowrap rounded-radius bg-transparent p-0.5 font-semibold text-primary outline-primary hover:opacity-75 focus-visible:outline-2 focus-visible:outline-offset-2 active:opacity-100 active:outline-offset-0 dark:text-primary-dark dark:outline-primary-dark">Edit</button></td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="p-4 text-center text-sm text-on-surface-muted dark:text-on-surface-muted-dark">No hay usuarios</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div class="p-4">
        {{ $users->links() }}
    </div>
</div>
