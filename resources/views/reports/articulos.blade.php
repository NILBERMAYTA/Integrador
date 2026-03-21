<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Reporte de Articulos</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; margin: 28px; color: #1f2937; }
    h1 { font-size: 18px; margin: 0 0 8px 0; }
    p { margin: 0 0 12px 0; color: #4b5563; }
    table { width: 100%; border-collapse: collapse; margin-top: 12px; }
    th, td { border: 1px solid #d1d5db; padding: 7px; vertical-align: top; }
    th { background: #e5e7eb; text-align: left; font-size: 10px; text-transform: uppercase; }
    .footer { font-size: 10px; text-align: right; margin-top: 10px; color: #6b7280; }
  </style>
</head>
<body>
  <h1>Reporte operativo de articulos</h1>
  <p>Generado el {{ now()->format('d/m/Y H:i:s') }}</p>

  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Categoria</th>
        <th>Estado</th>
        <th>Condicion</th>
        <th>Unidad</th>
        <th>Tipo</th>
        <th>Cantidad / Serie</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($articulos as $art)
        <tr>
          <td>{{ data_get($art, 'id') }}</td>
          <td>{{ data_get($art, 'nombre') }}</td>
          <td>{{ data_get($art, 'categoria', '-') }}</td>
          <td>{{ data_get($art, 'estado', '-') }}</td>
          <td>{{ data_get($art, 'condicion', '-') }}</td>
          <td>{{ data_get($art, 'unidad', '-') }}</td>
          <td>{{ ucfirst((string) data_get($art, 'tipo')) }}</td>
          <td>{{ data_get($art, 'cantidad_serie', '-') }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>

  <div class="footer">
    Sistema ARMUTOP - {{ config('app.name') }}
  </div>
</body>
</html>
