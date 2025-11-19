<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Reporte de Usuarios</title>
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
  <h1>Reporte de Usuarios</h1>
  <p>Generado el {{ now()->format('d/m/Y H:i:s') }}</p>

  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Escalafón</th>
        <th>Apellidos</th>
        <th>Nombre</th>
        <th>Rango</th>
        <th>Rol</th>
        <th>Ingreso</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($users as $u)
        <tr>
          <td>{{ $u->id }}</td>
          <td>{{ $u->numero_escalafon }}</td>
          <td>{{ trim($u->apellido_paterno.' '.$u->apellido_materno) }}</td>
          <td>{{ $u->name }}</td>
          <td>{{ $u->rango }}</td>
          <td>{{ $u->role }}</td>
          <td>{{ optional($u->fecha_ingreso)->format('Y-m-d') }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>

  <div class="footer">
    Sistema ARMUTOP — {{ config('app.name') }}
  </div>
</body>
</html>
