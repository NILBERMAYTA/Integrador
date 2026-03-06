<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #111; }
        h1 { font-size: 18px; margin: 0 0 6px 0; }
        .meta { margin-bottom: 12px; color: #444; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 10px; background: #eee; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px; vertical-align: top; }
        th { background: #f3f4f6; text-transform: uppercase; font-size: 10px; letter-spacing: 0.5px; }
        .muted { color: #666; font-size: 10px; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <div class="meta">
        <div>Generado: {{ now()->format('Y-m-d H:i:s') }}</div>
        <div>
            Rango: 
            {{ $date_from ?: 'Sin limite' }} 
            - 
            {{ $date_to ?: 'Sin limite' }}
            <span class="badge">{{ $tab === 'logins' ? 'Logins' : 'Actividad' }}</span>
        </div>
        <div class="muted">Total registros: {{ $activities->count() }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Fecha/Hora</th>
                <th>Usuario</th>
                <th>Evento</th>
                <th>Descripcion</th>
                <th>Modelo</th>
                @if($tab === 'logins')
                    <th>Rol</th>
                @endif
                <th>IP</th>
                <th>URL</th>
            </tr>
        </thead>
        <tbody>
            @forelse($activities as $activity)
                <tr>
                    <td>{{ $activity->created_at?->format('Y-m-d H:i:s') }}</td>
                    <td>
                        @if($activity->causer)
                            {{ $activity->causer->nombre_completo }}<br>
                            <span class="muted">{{ $activity->causer->email }}</span>
                        @else
                            Sistema
                        @endif
                    </td>
                    <td>{{ $activity->event }}</td>
                    <td>{{ $activity->description }}</td>
                    <td>
                        {{ class_basename($activity->subject_type) }}
                        @if($activity->subject_id)
                            <br><span class="muted">ID: {{ $activity->subject_id }}</span>
                        @endif
                    </td>
                    @if($tab === 'logins')
                        <td>{{ $activity->causer?->role ?? '—' }}</td>
                    @endif
                    <td>{{ $activity->properties->get('ip') }}</td>
                    <td>{{ $activity->properties->get('url') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $tab === 'logins' ? 8 : 7 }}">No hay registros.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
