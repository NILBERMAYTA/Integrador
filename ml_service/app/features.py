from __future__ import annotations

from datetime import datetime, timezone

import pandas as pd
from sqlalchemy import text

from app.db import get_engine
from app.settings import get_settings


def load_armamento_dataset(limit: int | None = None) -> pd.DataFrame:
    settings = get_settings()
    engine = get_engine()

    query = text(
        f"""
        WITH operaciones_agregadas AS (
            SELECT
                ods.serie_id,
                COUNT(*) FILTER (
                    WHERE o.fecha >= NOW() - (:lookback_days || ' days')::interval
                ) AS operaciones_90d,
                COUNT(*) AS operaciones_total,
                MAX(o.fecha) AS ultima_operacion_at
            FROM operacion_detalle_series ods
            INNER JOIN operacion_detalles od ON od.id = ods.operacion_detalle_id
            INNER JOIN operaciones o ON o.id = od.operacion_id
            WHERE ods.deleted_at IS NULL
              AND od.deleted_at IS NULL
              AND o.deleted_at IS NULL
            GROUP BY ods.serie_id
        ),
        incidencias_agregadas AS (
            SELECT
                i.serie_id,
                COUNT(*) FILTER (
                    WHERE i.fecha >= NOW() - (:lookback_days || ' days')::interval
                ) AS incidencias_90d,
                COUNT(*) AS incidencias_total,
                MAX(i.fecha) AS ultima_incidencia_at
            FROM incidencias i
            WHERE i.deleted_at IS NULL
            GROUP BY i.serie_id
        ),
        mantenimientos_agregados AS (
            SELECT
                m.serie_id,
                COUNT(*) FILTER (
                    WHERE COALESCE(m.fecha_inicio, m.created_at) >= NOW() - (:maintenance_lookback_days || ' days')::interval
                ) AS mantenimientos_180d,
                COUNT(*) AS mantenimientos_total,
                MAX(COALESCE(m.fecha_fin, m.fecha_inicio, m.created_at)) AS ultimo_mantenimiento_at
            FROM mantenimientos m
            WHERE m.deleted_at IS NULL
              AND m.serie_id IS NOT NULL
            GROUP BY m.serie_id
        ),
        inspecciones_agregadas AS (
            SELECT DISTINCT ON (ins.serie_id)
                ins.serie_id,
                ins.resultado::text AS ultimo_resultado_inspeccion,
                COALESCE(ins.realizada_en, ins.created_at) AS ultima_inspeccion_at
            FROM inspecciones ins
            WHERE ins.deleted_at IS NULL
              AND ins.serie_id IS NOT NULL
            ORDER BY ins.serie_id, COALESCE(ins.realizada_en, ins.created_at) DESC, ins.id DESC
        )
        SELECT
            s.id AS serie_id,
            s.articulo_id,
            s.unidad_id,
            s.codigo_serie,
            a.tipo::text AS tipo_articulo,
            a.seguimiento::text AS seguimiento,
            s.estado::text AS estado_actual,
            s.condicion_actual::text AS condicion_actual,
            COALESCE(op.operaciones_total, 0) AS operaciones_total,
            COALESCE(op.operaciones_90d, 0) AS operaciones_90d,
            COALESCE(inc.incidencias_total, 0) AS incidencias_total,
            COALESCE(inc.incidencias_90d, 0) AS incidencias_90d,
            COALESCE(m.mantenimientos_total, 0) AS mantenimientos_total,
            COALESCE(m.mantenimientos_180d, 0) AS mantenimientos_180d,
            COALESCE(EXTRACT(DAY FROM NOW() - m.ultimo_mantenimiento_at), 9999) AS dias_desde_ultimo_mantenimiento,
            COALESCE(EXTRACT(DAY FROM NOW() - op.ultima_operacion_at), 9999) AS dias_desde_ultima_operacion,
            COALESCE(EXTRACT(DAY FROM NOW() - inc.ultima_incidencia_at), 9999) AS dias_desde_ultima_incidencia,
            COALESCE(ins.ultimo_resultado_inspeccion, 'sin_inspeccion') AS ultimo_resultado_inspeccion,
            COALESCE(EXTRACT(DAY FROM NOW() - ins.ultima_inspeccion_at), 9999) AS dias_desde_ultima_inspeccion,
            CASE
                WHEN s.condicion_actual::text = 'inoperativo' OR s.estado::text = 'inoperativo' THEN 1
                ELSE 0
            END AS resultado
        FROM articulo_series s
        INNER JOIN articulos a ON a.id = s.articulo_id
        LEFT JOIN operaciones_agregadas op ON op.serie_id = s.id
        LEFT JOIN incidencias_agregadas inc ON inc.serie_id = s.id
        LEFT JOIN mantenimientos_agregados m ON m.serie_id = s.id
        LEFT JOIN inspecciones_agregadas ins ON ins.serie_id = s.id
        WHERE s.deleted_at IS NULL
          AND a.deleted_at IS NULL
        ORDER BY s.id DESC
        """
    )

    with engine.connect() as connection:
        df = pd.read_sql(
            query,
            connection,
            params={
                "lookback_days": settings.lookback_days,
                "maintenance_lookback_days": settings.maintenance_lookback_days,
            },
        )

    if limit is not None:
        df = df.head(limit)

    numeric_columns = [
        "articulo_id",
        "unidad_id",
        "operaciones_total",
        "operaciones_90d",
        "incidencias_total",
        "incidencias_90d",
        "mantenimientos_total",
        "mantenimientos_180d",
        "dias_desde_ultimo_mantenimiento",
        "dias_desde_ultima_operacion",
        "dias_desde_ultima_incidencia",
        "dias_desde_ultima_inspeccion",
        "resultado",
    ]

    for column in numeric_columns:
        if column in df.columns:
            df[column] = pd.to_numeric(df[column], errors="coerce").fillna(0)

    return df


def build_prediction_frame(df: pd.DataFrame) -> pd.DataFrame:
    frame = df.copy()

    columns = [
        "articulo_id",
        "unidad_id",
        "tipo_articulo",
        "seguimiento",
        "estado_actual",
        "condicion_actual",
        "operaciones_total",
        "operaciones_90d",
        "incidencias_total",
        "incidencias_90d",
        "mantenimientos_total",
        "mantenimientos_180d",
        "dias_desde_ultimo_mantenimiento",
        "dias_desde_ultima_operacion",
        "dias_desde_ultima_incidencia",
        "ultimo_resultado_inspeccion",
        "dias_desde_ultima_inspeccion",
    ]

    return frame[columns].copy()


def utc_now_iso() -> str:
    return datetime.now(timezone.utc).isoformat()
