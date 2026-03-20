<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Reporte de Inventario de ArtÃ­culos</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; margin: 30px; }
    h1 { font-size: 18px; text-align: center; margin-bottom: 10px; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { border: 1px solid #ccc; padding: 6px; }
    th { background: #f3f3f3; text-align: left; }
    .right { text-align: right; }
    .center { text-align: center; }
    .footer { font-size: 10px; text-align: right; margin-top: 10px; color: #666; }
  </style>
</head>
<body>
  <h1>Reporte de Inventario de ArtÃ­culos</h1>
  <p>Generado el {{ now()->format('d/m/Y H:i:s') }}</p>

  <table>
    <thead>
      <tr>
        <th>ArtÃ­culo</th>
        <th>CategorÃ­a</th>
        <th>Tipo</th>
        <th class="center">Entrada</th>
        <th class="center">Salida</th>
        <th class="center">Total Neto</th>
        <th>Ãšltimo movimiento</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($items as $item)
        @php
          $art = $item['articulo'];
          $entrada = $item['entrada'];
          $salida = $item['salida'];
          $total = $item['total'];
          $ultimo = $item['ultimo_movimiento'];
        @endphp
        <tr>
          <td>{{ $art->nombre }}</td>
          <td>{{ $art->categoria?->nombre ?? 'â€”' }}</td>
          <td>{{ ucfirst($art->tipo ?? '') }}</td>
          <td class="center">+{{ number_format($entrada, $art->isCantidad() ? 2 : 0) }}</td>
          <td class="center">-{{ number_format($salida, $art->isCantidad() ? 2 : 0) }}</td>
          <td class="center">{{ number_format($total, $art->isCantidad() ? 2 : 0) }}</td>
          <td>{{ $ultimo ?? 'â€”' }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>

  <div class="footer">
    Sistema ARMUTOP â€” {{ config('app.name') }}
  </div>
</body>
</html>
