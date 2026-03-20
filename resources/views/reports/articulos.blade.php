<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Reporte de ArtÃ­culos</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; margin: 30px; }
    h1 { font-size: 18px; text-align: center; margin-bottom: 10px; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { border: 1px solid #ccc; padding: 6px; }
    th { background: #f3f3f3; text-align: left; }
    .footer { font-size: 10px; text-align: right; margin-top: 10px; color: #666; }
  </style>
</head>
<body>
  <h1>Reporte de ArtÃ­culos</h1>
  <p>Generado el {{ now()->format('d/m/Y H:i:s') }}</p>

  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>CategorÃ­a</th>
        <th>Tipo</th>
        <th>Gestion</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($articulos as $art)
        <tr>
          <td>{{ $art->id }}</td>
          <td>{{ $art->nombre }}</td>
          <td>{{ $art->categoria?->nombre ?? 'â€”' }}</td>
          <td>{{ $art->tipo }}</td>
          <td>{{ $art->tipo === 'reutilizable' ? 'Serie' : 'Cantidad' }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>

  <div class="footer">
    Sistema ARMUTOP â€” {{ config('app.name') }}
  </div>
</body>
</html>
