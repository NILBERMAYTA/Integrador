<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de predicciones de armamento</title>
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
        .card { border: 1px solid #dbe2ea; border-radius: 10px; padding: 12px; background: #fff; min-height: 72px; }
        .label { font-size: 9px; text-transform: uppercase; letter-spacing: 1px; color: #64748b; margin-bottom: 8px; }
        .value { font-size: 24px; font-weight: 700; color: #0f172a; }
        .hint { font-size: 10px; color: #475569; margin-top: 4px; }
        .split { width: 100%; border-collapse: separate; border-spacing: 14px 0; margin: 0 -14px; }
        .split td { width: 50%; vertical-align: top; }
        .panel { border: 1px solid #dbe2ea; border-radius: 10px; padding: 14px; background: #fff; }
        .stats-table, .data-table { width: 100%; border-collapse: collapse; }
        .stats-table td { padding: 7px 0; border-bottom: 1px solid #eef2f7; }
        .stats-table tr:last-child td { border-bottom: none; }
        .data-table th, .data-table td { border: 1px solid #dbe2ea; padding: 8px; vertical-align: top; }
        .data-table th { background: #e2e8f0; text-transform: uppercase; font-size: 9px; letter-spacing: .8px; color: #334155; }
        .muted { color: #64748b; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 999px; font-size: 9px; font-weight: 700; }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-danger { background: #ffe4e6; color: #be123c; }
        .badge-high { background: #ffe4e6; color: #be123c; }
        .badge-medium { background: #fef3c7; color: #b45309; }
        .badge-low { background: #dcfce7; color: #166534; }
        .small { font-size: 10px; }
        .footer { margin-top: 14px; font-size: 10px; color: #64748b; }
        .status-text { margin-top: 14px; font-size: 13px; line-height: 1.6; color: #0f172a; font-weight: 700; }
        .page-break { page-break-before: always; }
    </style>
</head>
<body>
    @php
        $generatedByName = data_get($generatedBy, 'nombre_completo')
            ?: trim((data_get($generatedBy, 'name', '').' '.data_get($generatedBy, 'apellido_paterno', '')))
            ?: data_get($generatedBy, 'email', 'Sistema');
        $totalSeries = max(1, (int) ($stats['total'] ?? 0));
        $operativas = (int) ($stats['operativo'] ?? 0);
        $inoperativas = (int) ($stats['inoperativo'] ?? 0);
        $alto = (int) ($stats['alto'] ?? 0);
        $medio = (int) ($stats['medio'] ?? 0);
        $bajo = (int) ($stats['bajo'] ?? 0);
        $disponibilidad = ($operativas / $totalSeries) * 100;
        $indisponibilidad = ($inoperativas / $totalSeries) * 100;
        $estadoGeneral = match (true) {
            $alto >= max(1, (int) ceil($totalSeries * 0.3)) || $indisponibilidad >= 30 => 'Estado general critico del armamento. Se recomienda intervencion prioritaria sobre las series con mayor riesgo y disponibilidad reducida.',
            $alto > 0 || $medio >= max(1, (int) ceil($totalSeries * 0.35)) || $indisponibilidad >= 15 => 'Estado general en observacion. El armamento mantiene operatividad parcial, pero requiere seguimiento preventivo y correcciones focalizadas.',
            default => 'Estado general favorable del armamento. Predomina la operatividad y el riesgo actual se mantiene en niveles controlados.',
        };
    @endphp

    <div class="section">
        <h1>Reporte de predicciones de armamento</h1>
        <p class="subtitle">Consolidado operativo del modelo de prediccion para series evaluadas.</p>
    </div>

    <table class="meta">
        <tr>
            <td><strong>Generado:</strong> {{ $generatedAt->format('d/m/Y H:i') }}</td>
            <td><strong>Usuario:</strong> {{ $generatedByName }}</td>
            <td><strong>Unidad:</strong> {{ $unidadNombre ?? 'Todas las unidades' }}</td>
        </tr>
    </table>

    <table class="grid-4">
        <tr>
            <td>
                <div class="card">
                    <div class="label">Series analizadas</div>
                    <div class="value">{{ $stats['total'] ?? 0 }}</div>
                    <div class="hint">Total completo del alcance seleccionado.</div>
                </div>
            </td>
            <td>
                <div class="card">
                    <div class="label">Riesgo alto</div>
                    <div class="value" style="color:#be123c;">{{ $stats['alto'] ?? 0 }}</div>
                    <div class="hint">Series que requieren atencion prioritaria.</div>
                </div>
            </td>
            <td>
                <div class="card">
                    <div class="label">Operativas</div>
                    <div class="value" style="color:#166534;">{{ $stats['operativo'] ?? 0 }}</div>
                    <div class="hint">Predichas como operativas por el modelo.</div>
                </div>
            </td>
            <td>
                <div class="card">
                    <div class="label">Inoperativas</div>
                    <div class="value" style="color:#be123c;">{{ $stats['inoperativo'] ?? 0 }}</div>
                    <div class="hint">Predichas como inoperativas por el modelo.</div>
                </div>
            </td>
        </tr>
    </table>

    <table class="split section">
        <tr>
            <td>
                <div class="panel">
                    <h2>Resumen analitico</h2>
                    <table class="stats-table">
                        <tr>
                            <td>Riesgo alto</td>
                            <td style="text-align:right;">{{ $stats['alto'] ?? 0 }}</td>
                        </tr>
                        <tr>
                            <td>Riesgo medio</td>
                            <td style="text-align:right;">{{ $stats['medio'] ?? 0 }}</td>
                        </tr>
                        <tr>
                            <td>Riesgo bajo</td>
                            <td style="text-align:right;">{{ $stats['bajo'] ?? 0 }}</td>
                        </tr>
                        <tr>
                            <td>Prediccion operativa</td>
                            <td style="text-align:right;">{{ $stats['operativo'] ?? 0 }}</td>
                        </tr>
                        <tr>
                            <td>Prediccion inoperativa</td>
                            <td style="text-align:right;">{{ $stats['inoperativo'] ?? 0 }}</td>
                        </tr>
                    </table>
                </div>
            </td>
            <td>
                <div class="panel">
                    <h2>Lectura operativa</h2>
                    <table class="stats-table">
                        <tr>
                            <td>Series con atencion inmediata</td>
                            <td style="text-align:right;">{{ $stats['alto'] ?? 0 }}</td>
                        </tr>
                        <tr>
                            <td>Series en monitoreo preventivo</td>
                            <td style="text-align:right;">{{ ($stats['medio'] ?? 0) + ($stats['bajo'] ?? 0) }}</td>
                        </tr>
                        <tr>
                            <td>Disponibilidad estimada</td>
                            <td style="text-align:right;">
                                {{ ($stats['total'] ?? 0) > 0 ? number_format($disponibilidad, 2) : '0.00' }}%
                            </td>
                        </tr>
                        <tr>
                            <td>Indisponibilidad estimada</td>
                            <td style="text-align:right;">
                                {{ ($stats['total'] ?? 0) > 0 ? number_format($indisponibilidad, 2) : '0.00' }}%
                            </td>
                        </tr>
                    </table>
                    <p class="status-text">
                        {{ $estadoGeneral }}
                    </p>
                </div>
            </td>
        </tr>
    </table>

    @if($trainingSummary)
        <div class="section">
            <h2>Ultimo entrenamiento registrado</h2>
            <table class="grid-4">
                <tr>
                    <td>
                        <div class="card">
                            <div class="label">Accuracy</div>
                            <div class="value">{{ number_format(((float) ($trainingSummary['accuracy'] ?? 0)) * 100, 2) }}%</div>
                        </div>
                    </td>
                    <td>
                        <div class="card">
                            <div class="label">F1</div>
                            <div class="value">{{ number_format(((float) ($trainingSummary['f1'] ?? 0)) * 100, 2) }}%</div>
                        </div>
                    </td>
                    <td>
                        <div class="card">
                            <div class="label">Precision</div>
                            <div class="value">{{ number_format(((float) ($trainingSummary['precision'] ?? 0)) * 100, 2) }}%</div>
                        </div>
                    </td>
                    <td>
                        <div class="card">
                            <div class="label">Recall</div>
                            <div class="value">{{ number_format(((float) ($trainingSummary['recall'] ?? 0)) * 100, 2) }}%</div>
                        </div>
                    </td>
                </tr>
            </table>
            <p class="small muted">Registros usados: {{ $trainingSummary['total_registros'] ?? 0 }}. Entrenamiento: {{ $trainingSummary['total_entrenamiento'] ?? 0 }}. Prueba: {{ $trainingSummary['total_prueba'] ?? 0 }}. ROC AUC: {{ isset($trainingSummary['roc_auc']) ? number_format(((float) $trainingSummary['roc_auc']) * 100, 2).'%' : 'No disponible' }}.</p>
        </div>
    @endif

    <div class="section page-break">
        <h2>Detalle de predicciones</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Serie</th>
                    <th>Articulo</th>
                    <th>Unidad</th>
                    <th>Estado predicho</th>
                    <th>Probabilidad</th>
                    <th>Riesgo</th>
                    <th>Recomendacion</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                @forelse($predicciones as $item)
                    @php
                        $estado = $item['estado_predicho'] ?? '--';
                        $riesgo = $item['nivel_riesgo'] ?? '--';
                        $estadoClass = $estado === 'inoperativo' ? 'badge-danger' : 'badge-success';
                        $riesgoClass = match ($riesgo) {
                            'alto' => 'badge-high',
                            'medio' => 'badge-medium',
                            default => 'badge-low',
                        };
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $item['codigo_serie'] ?? '--' }}</strong><br>
                            <span class="muted">Serie ID: {{ $item['serie_id'] ?? '--' }}</span>
                        </td>
                        <td>{{ $item['articulo_id'] ?? '--' }}</td>
                        <td>{{ $item['unidad_id'] ?? '--' }}</td>
                        <td><span class="badge {{ $estadoClass }}">{{ ucfirst($estado) }}</span></td>
                        <td>{{ isset($item['probabilidad']) ? number_format(((float) $item['probabilidad']) * 100, 2).'%' : '--' }}</td>
                        <td><span class="badge {{ $riesgoClass }}">{{ ucfirst($riesgo) }}</span></td>
                        <td>{{ $item['recomendacion'] ?? '--' }}</td>
                        <td>{{ $item['fecha_prediccion'] ?? '--' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">No hay predicciones disponibles para exportar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="footer">
        Este reporte resume la salida del modelo de prediccion consumido por ARMUTOP al momento de la exportacion.
    </div>
</body>
</html>
