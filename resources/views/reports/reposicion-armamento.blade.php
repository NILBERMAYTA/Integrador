<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de reposicion de armamento</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #172033; margin: 24px; }
        h1, h2, h3, p { margin: 0; }
        h1 { font-size: 22px; margin-bottom: 4px; }
        h2 { font-size: 14px; margin-bottom: 10px; color: #0f172a; }
        .subtitle { color: #475569; margin-bottom: 16px; }
        .meta { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        .meta td { padding: 8px 10px; border: 1px solid #dbe2ea; background: #f8fafc; }
        .section { margin-bottom: 20px; }
        .grid-4 { width: 100%; border-collapse: separate; border-spacing: 10px 0; margin: 0 -10px 14px; }
        .grid-4 td { width: 25%; vertical-align: top; }
        .split { width: 100%; border-collapse: separate; border-spacing: 14px 0; margin: 0 -14px; }
        .split td { width: 50%; vertical-align: top; }
        .card, .panel { border: 1px solid #dbe2ea; border-radius: 10px; background: #fff; }
        .card { padding: 12px; min-height: 72px; }
        .panel { padding: 14px; }
        .label { font-size: 9px; text-transform: uppercase; letter-spacing: 1px; color: #64748b; margin-bottom: 8px; }
        .value { font-size: 24px; font-weight: 700; color: #0f172a; }
        .hint { font-size: 10px; color: #475569; margin-top: 4px; }
        .stats-table, .data-table { width: 100%; border-collapse: collapse; }
        .stats-table td { padding: 7px 0; border-bottom: 1px solid #eef2f7; }
        .stats-table tr:last-child td { border-bottom: none; }
        .data-table th, .data-table td { border: 1px solid #dbe2ea; padding: 8px; vertical-align: top; }
        .data-table th { background: #e2e8f0; text-transform: uppercase; font-size: 9px; letter-spacing: .8px; color: #334155; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 999px; font-size: 9px; font-weight: 700; }
        .badge-now { background: #ffe4e6; color: #be123c; }
        .badge-soon { background: #fef3c7; color: #b45309; }
        .badge-plan { background: #e0f2fe; color: #0369a1; }
        .badge-stable { background: #dcfce7; color: #166534; }
        .muted { color: #64748b; }
        .small { font-size: 10px; }
        .footer { margin-top: 14px; font-size: 10px; color: #64748b; }
    </style>
</head>
<body>
    @php
        $generatedByName = data_get($generatedBy, 'nombre_completo')
            ?: trim((data_get($generatedBy, 'name', '').' '.data_get($generatedBy, 'apellido_paterno', '')))
            ?: data_get($generatedBy, 'email', 'Sistema');
        $totalSugerido = (int) ($resumen['cantidad_sugerida_total'] ?? 0);
        $articulosEvaluados = (int) ($resumen['articulos_evaluados'] ?? 0);
        $reposicionInmediata = (int) ($resumen['reposicion_inmediata'] ?? 0);
        $reposicionProxima = (int) ($resumen['reposicion_proxima'] ?? 0);
        $itemsParaPedido = collect($recomendaciones)
            ->filter(fn (array $item) => (int) ($item['cantidad_sugerida'] ?? 0) > 0)
            ->values();
        $pedidoRapido = $itemsParaPedido
            ->map(function (array $item) {
                $urgenciaTexto = match ($item['urgencia'] ?? '') {
                    'inmediata' => 'de forma inmediata',
                    'proxima' => 'en la siguiente ventana',
                    'planificada' => 'de forma planificada',
                    default => 'sin urgencia inmediata',
                };

                return sprintf(
                    '%s (%d unidades, %s)',
                    $item['articulo'] ?? 'Articulo sin nombre',
                    (int) ($item['cantidad_sugerida'] ?? 0),
                    $urgenciaTexto
                );
            })
            ->implode('; ');
        $resumenNatural = match (true) {
            $totalSugerido <= 0 => 'No se identifican necesidades inmediatas de reposicion. Se recomienda mantener el monitoreo preventivo del armamento evaluado.',
            $reposicionInmediata > 0 && $reposicionProxima > 0 => "Se recomienda gestionar un pedido total de {$totalSugerido} unidades distribuidas en {$articulosEvaluados} articulos evaluados. La prioridad actual combina requerimientos inmediatos y compras para la siguiente ventana operativa.",
            $reposicionInmediata > 0 => "Se recomienda gestionar con prioridad un pedido de {$totalSugerido} unidades. La necesidad principal se concentra en articulos con reposicion inmediata para sostener la disponibilidad operativa.",
            default => "Se recomienda programar un pedido de {$totalSugerido} unidades de manera preventiva. La demanda identificada se puede atender en la siguiente ventana de adquisicion sin caracter critico inmediato.",
        };
    @endphp

    <div class="section">
        <h1>Reporte de reposicion de armamento</h1>
        <p class="subtitle">Estimación generada exclusivamente desde la predicción futura del modelo.</p>
    </div>

    <table class="meta">
        <tr>
            <td><strong>Generado:</strong> {{ $generatedAt->format('d/m/Y H:i') }}</td>
            <td><strong>Usuario:</strong> {{ $generatedByName }}</td>
            <td><strong>Unidad:</strong> {{ $unidadNombre ?? 'Todas las unidades' }}</td>
            <td><strong>Horizonte:</strong> {{ $horizonteDias ?? 0 }} días</td>
        </tr>
    </table>

    <table class="grid-4">
        <tr>
            <td>
                <div class="card">
                    <div class="label">Reposicion inmediata</div>
                    <div class="value" style="color:#be123c;">{{ $resumen['reposicion_inmediata'] ?? 0 }}</div>
                    <div class="hint">Articulos que conviene pedir ahora.</div>
                </div>
            </td>
            <td>
                <div class="card">
                    <div class="label">Reposicion proxima</div>
                    <div class="value" style="color:#b45309;">{{ $resumen['reposicion_proxima'] ?? 0 }}</div>
                    <div class="hint">Material para la siguiente ventana de compra.</div>
                </div>
            </td>
            <td>
                <div class="card">
                    <div class="label">Planificadas o estables</div>
                    <div class="value" style="color:#0369a1;">{{ ($distribucion['planificada'] ?? 0) + ($distribucion['estable'] ?? 0) }}</div>
                    <div class="hint">Seguimiento preventivo sin urgencia inmediata.</div>
                </div>
            </td>
            <td>
                <div class="card">
                    <div class="label">Cantidad sugerida total</div>
                    <div class="value">{{ $resumen['cantidad_sugerida_total'] ?? 0 }}</div>
                    <div class="hint">Suma de cantidades recomendadas por articulo.</div>
                </div>
            </td>
        </tr>
    </table>

    <table class="split section">
        <tr>
            <td>
                <div class="panel">
                    <h2>Distribucion por urgencia</h2>
                    <table class="stats-table">
                        <tr>
                            <td>Inmediata</td>
                            <td style="text-align:right;">{{ $distribucion['inmediata'] ?? 0 }}</td>
                        </tr>
                        <tr>
                            <td>Proxima</td>
                            <td style="text-align:right;">{{ $distribucion['proxima'] ?? 0 }}</td>
                        </tr>
                        <tr>
                            <td>Planificada</td>
                            <td style="text-align:right;">{{ $distribucion['planificada'] ?? 0 }}</td>
                        </tr>
                        <tr>
                            <td>Estable</td>
                            <td style="text-align:right;">{{ $distribucion['estable'] ?? 0 }}</td>
                        </tr>
                    </table>
                </div>
            </td>
            <td>
                <div class="panel">
                    <h2>Ventana recomendada de pedido</h2>
                    <table class="stats-table">
                        <tr>
                            <td>Ahora</td>
                            <td style="text-align:right;">{{ $distribucion['ahora'] ?? 0 }}</td>
                        </tr>
                        <tr>
                            <td>Pronto</td>
                            <td style="text-align:right;">{{ $distribucion['pronto'] ?? 0 }}</td>
                        </tr>
                        <tr>
                            <td>Luego</td>
                            <td style="text-align:right;">{{ $distribucion['luego'] ?? 0 }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <div class="section">
        <div class="panel">
            <h2>Resumen ejecutivo para solicitud</h2>
            <p style="font-size: 13px; line-height: 1.7; color: #0f172a; font-weight: 700; margin-bottom: 10px;">
                {{ $resumenNatural }}
            </p>
            <p class="small muted">
                {{ $pedidoRapido !== '' ? 'Pedido resumido: '.$pedidoRapido.'.' : 'No hay articulos con cantidad sugerida mayor a cero en el calculo actual.' }}
            </p>
        </div>
    </div>

    <div class="section">
        <h2>Cuadro rapido de pedido</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Articulo</th>
                    <th>Categoria</th>
                    <th>Cantidad requerida</th>
                    <th>Prioridad</th>
                    <th>Ventana de compra</th>
                    <th>Justificacion corta</th>
                </tr>
            </thead>
            <tbody>
                @forelse($itemsParaPedido as $item)
                    @php
                        $urgenciaClass = match ($item['urgencia'] ?? '') {
                            'inmediata' => 'badge-now',
                            'proxima' => 'badge-soon',
                            'planificada' => 'badge-plan',
                            'sin_datos' => 'badge-stable',
                            default => 'badge-stable',
                        };
                        $cuandoTexto = match ($item['urgencia'] ?? '') {
                            'inmediata' => 'Ahora',
                            'proxima' => 'Pronto',
                            'planificada' => 'Planificada',
                            default => 'Luego',
                        };
                        $justificacionCorta = trim(implode(', ', array_filter([
                            ((int) ($item['futuro_inoperativo'] ?? 0)) > 0 ? ((int) $item['futuro_inoperativo']).' futuras inoperativas' : null,
                            ((int) ($item['futuro_con_defectos'] ?? 0)) > 0 ? ((int) $item['futuro_con_defectos']).' futuras con defectos' : null,
                        ])));
                    @endphp
                    <tr>
                        <td><strong>{{ $item['articulo'] ?? '--' }}</strong></td>
                        <td>{{ $item['categoria'] ?? 'Sin categoria' }}</td>
                        <td><strong>{{ $item['cantidad_sugerida'] ?? 0 }}</strong></td>
                        <td><span class="badge {{ $urgenciaClass }}">{{ ucfirst($item['urgencia'] ?? 'estable') }}</span></td>
                        <td>{{ $cuandoTexto }}</td>
                        <td>{{ $justificacionCorta !== '' ? ucfirst($justificacionCorta).'.' : 'Seguimiento preventivo.' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">No hay equipamiento sugerido para pedido rapido.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Detalle de recomendacion por articulo</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Articulo</th>
                    <th>Categoria</th>
                    <th>Estado operacional</th>
                    <th>Urgencia</th>
                    <th>Cuando pedir</th>
                    <th>Cuanto pedir</th>
                    <th>Salud</th>
                    <th>Motivo tecnico</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recomendaciones as $item)
                    @php
                        $urgenciaClass = match ($item['urgencia'] ?? '') {
                            'inmediata' => 'badge-now',
                            'proxima' => 'badge-soon',
                            'planificada' => 'badge-plan',
                            default => 'badge-stable',
                        };
                        $estadoTexto = match ($item['urgencia'] ?? '') {
                            'inmediata' => 'Estado critico',
                            'proxima' => 'Requiere atencion',
                            'planificada' => 'Seguimiento preventivo',
                            'sin_datos' => 'Sin historial suficiente',
                            default => 'Estado estable',
                        };
                        $cuandoTexto = match ($item['urgencia'] ?? '') {
                            'inmediata' => 'Ahora a 30 dias',
                            'proxima' => '30 a 60 dias',
                            'planificada' => '60 a 90 dias',
                            'sin_datos' => 'Pendiente de inspecciones',
                            default => 'Mas de 90 dias',
                        };
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $item['articulo'] ?? '--' }}</strong><br>
                            <span class="muted">ID: {{ $item['articulo_id'] ?? '--' }}</span>
                        </td>
                        <td>{{ $item['categoria'] ?? 'Sin categoria' }}</td>
                        <td>
                            <strong>{{ $estadoTexto }}</strong><br>
                            <span class="muted">Unidad: {{ $item['unidad_nombre'] ?? 'Sin unidad' }}. Total: {{ $item['total_series'] ?? 0 }}, futuras inoperativas: {{ $item['futuro_inoperativo'] ?? 0 }}</span>
                        </td>
                        <td><span class="badge {{ $urgenciaClass }}">{{ ucfirst($item['urgencia'] ?? 'estable') }}</span></td>
                        <td>
                            {{ $cuandoTexto }}
                            @if($item['fecha_sugerida_desde'] ?? null)
                                <br><span class="muted">Desde {{ $item['fecha_sugerida_desde'] }}</span>
                            @endif
                        </td>
                        <td><strong>{{ $item['cantidad_sugerida'] ?? 0 }}</strong></td>
                        <td>{{ number_format((1 - (float) ($item['tasa_reposicion_esperada'] ?? 0)) * 100, 2) }}%</td>
                        <td>
                            {{ $item['motivo'] ?? '--' }}<br>
                            <span class="muted">Confianza: {{ number_format(((float) ($item['confianza_futura_promedio'] ?? 0)) * 100, 1) }}%, cobertura histórica: {{ number_format((float) ($item['cobertura_historica_pct'] ?? 0), 1) }}%</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">No hay recomendaciones disponibles para exportar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="footer">
        Este reporte se genera a partir del calculo actual de reposicion de armamento y resume prioridad, ventana de compra y cantidad sugerida.
    </div>
</body>
</html>
