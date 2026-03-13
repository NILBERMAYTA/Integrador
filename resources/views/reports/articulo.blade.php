<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Reporte de ArtÃ­culo</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; margin: 30px; }
    h1 { font-size: 18px; text-align: center; margin-bottom: 6px; }
    .meta { font-size: 11px; color: #444; margin-bottom: 12px; }
    .box { border: 1px solid #ddd; padding: 8px 10px; margin-bottom: 12px; }
    table { width: 100%; border-collapse: collapse; margin-top: 8px; }
    th, td { border: 1px solid #ccc; padding: 6px; }
    th { background: #f3f3f3; text-align: left; }
    .right { text-align: right; }
    .center { text-align: center; }
    .footer { font-size: 10px; text-align: right; margin-top: 10px; color: #666; }
  </style>
</head>
<body>
  <h1>Reporte de ArtÃ­culo</h1>
  <div class="meta">Generado el {{ now()->format('d/m/Y H:i:s') }}</div>

  <div class="box">
    <strong>ArtÃ­culo:</strong> {{ $articulo->nombre }}<br>
    <strong>CategorÃ­a:</strong> {{ $articulo->categoria?->nombre ?? 'â€”' }}<br>
    <strong>Tipo:</strong> {{ ucfirst($articulo->tipo ?? '') }}<br>
    <strong>Seguimiento:</strong> {{ ucfirst($articulo->seguimiento ?? '') }}
  </div>

  @if(($articulo->seguimiento ?? '') === 'serie')
    <div class="box">
      <strong>Total series:</strong> {{ $resumen['total'] ?? 0 }} &nbsp;|&nbsp;
      <strong>Disponibles:</strong> {{ $resumen['disponibles'] ?? 0 }} &nbsp;|&nbsp;
      <strong>Asignadas:</strong> {{ $resumen['asignados'] ?? 0 }}
    </div>

    <table>
      <thead>
        <tr>
          <th>CÃ³digo de serie</th>
          <th>Estado</th>
          <th>Creado</th>
          <th>Observaciones</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($series as $s)
          <tr>
            <td>{{ $s->codigo_serie }}</td>
            <td>{{ $s->estado ?? 'â€”' }}</td>
            <td>{{ $s->created_at?->format('d/m/Y H:i') }}</td>
            <td>{{ $s->observaciones ?? 'â€”' }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  @else
    <div class="box">
      <strong>Entrada:</strong> +{{ number_format($resumen['entrada'] ?? 0, 2) }} &nbsp;|&nbsp;
      <strong>Salida:</strong> -{{ number_format($resumen['salida'] ?? 0, 2) }} &nbsp;|&nbsp;
      <strong>Total Neto:</strong> {{ number_format($resumen['total'] ?? 0, 2) }}
    </div>

    <table>
      <thead>
        <tr>
          <th>Tipo</th>
          <th>Fecha</th>
          <th class="center">Cantidad</th>
          <th>CondiciÃ³n</th>
          <th>Observaciones</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($detalles as $d)
          <tr>
            <td>{{ $d->operacion?->tipo ?? 'â€”' }}</td>
            <td>{{ optional($d->operacion?->fecha)->format('d/m/Y H:i') ?? ($d->created_at?->format('d/m/Y H:i') ?? 'â€”') }}</td>
            <td class="center">{{ $d->cantidad }}</td>
            <td>{{ $d->condicion ?? 'â€”' }}</td>
            <td>{{ $d->observaciones ?? 'â€”' }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  @endif

  <div class="footer">
    Sistema ARMUTOP â€” {{ config('app.name') }}
  </div>
</body>
</html>
