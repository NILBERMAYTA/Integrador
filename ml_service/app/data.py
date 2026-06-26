from __future__ import annotations

from datetime import datetime, timezone
from pathlib import Path

import pandas as pd
from sqlalchemy import text

from app.config import get_engine, get_settings


def load_armamento_dataset(
    limit: int | None = None,
    unidad_id: int | None = None,
    serie_id: int | None = None,
) -> pd.DataFrame:
    settings = get_settings()
    engine = get_engine()

    query = text(
        """
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
            a.nombre AS articulo_nombre,
            a.categoria_id,
            COALESCE(c.nombre, 'Sin categoría') AS categoria_nombre,
            s.unidad_id,
            COALESCE(u.sigla || ' - ' || u.nombre, u.nombre, 'Sin unidad') AS unidad_nombre,
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
            CASE WHEN m.ultimo_mantenimiento_at IS NULL THEN 1 ELSE 0 END AS sin_mantenimiento,
            CASE WHEN op.ultima_operacion_at IS NULL THEN 1 ELSE 0 END AS sin_operacion,
            CASE WHEN inc.ultima_incidencia_at IS NULL THEN 1 ELSE 0 END AS sin_incidencia,
            CASE WHEN ins.ultima_inspeccion_at IS NULL THEN 1 ELSE 0 END AS sin_inspeccion,
            LEAST(COALESCE(EXTRACT(DAY FROM NOW() - m.ultimo_mantenimiento_at), :dias_cap), :dias_cap) AS dias_desde_ultimo_mantenimiento,
            LEAST(COALESCE(EXTRACT(DAY FROM NOW() - op.ultima_operacion_at), :dias_cap), :dias_cap) AS dias_desde_ultima_operacion,
            LEAST(COALESCE(EXTRACT(DAY FROM NOW() - inc.ultima_incidencia_at), :dias_cap), :dias_cap) AS dias_desde_ultima_incidencia,
            COALESCE(ins.ultimo_resultado_inspeccion, 'sin_inspeccion') AS ultimo_resultado_inspeccion,
            LEAST(COALESCE(EXTRACT(DAY FROM NOW() - ins.ultima_inspeccion_at), :dias_cap), :dias_cap) AS dias_desde_ultima_inspeccion
        FROM articulo_series s
        INNER JOIN articulos a ON a.id = s.articulo_id
        LEFT JOIN categorias c ON c.id = a.categoria_id
        LEFT JOIN unidades u ON u.id = s.unidad_id
        LEFT JOIN operaciones_agregadas op ON op.serie_id = s.id
        LEFT JOIN incidencias_agregadas inc ON inc.serie_id = s.id
        LEFT JOIN mantenimientos_agregados m ON m.serie_id = s.id
        LEFT JOIN inspecciones_agregadas ins ON ins.serie_id = s.id
        WHERE s.deleted_at IS NULL
          AND a.deleted_at IS NULL
          AND (:unidad_id IS NULL OR s.unidad_id = :unidad_id)
          AND (:serie_id IS NULL OR s.id = :serie_id)
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
                "dias_cap": settings.dias_cap,
                "unidad_id": unidad_id,
                "serie_id": serie_id,
            },
        )

    if limit is not None:
        df = df.head(limit)

    numeric_columns = [
        "articulo_id",
        "categoria_id",
        "unidad_id",
        "operaciones_total",
        "operaciones_90d",
        "incidencias_total",
        "incidencias_90d",
        "mantenimientos_total",
        "mantenimientos_180d",
        "sin_mantenimiento",
        "sin_operacion",
        "sin_incidencia",
        "sin_inspeccion",
        "dias_desde_ultimo_mantenimiento",
        "dias_desde_ultima_operacion",
        "dias_desde_ultima_incidencia",
        "dias_desde_ultima_inspeccion",
    ]

    for column in numeric_columns:
        if column in df.columns:
            df[column] = pd.to_numeric(df[column], errors="coerce").fillna(0)

    return df


def build_prediction_frame(df: pd.DataFrame) -> pd.DataFrame:
    columns = [
        "articulo_id",
        "categoria_id",
        "tipo_articulo",
        "seguimiento",
        "operaciones_total",
        "operaciones_90d",
        "incidencias_total",
        "incidencias_90d",
        "mantenimientos_total",
        "mantenimientos_180d",
        "sin_mantenimiento",
        "sin_operacion",
        "sin_incidencia",
        "sin_inspeccion",
        "dias_desde_ultimo_mantenimiento",
        "dias_desde_ultima_operacion",
        "dias_desde_ultima_incidencia",
        "ultimo_resultado_inspeccion",
        "dias_desde_ultima_inspeccion",
    ]
    return df[columns].copy()


def load_future_training_dataset() -> pd.DataFrame:
    """Construye transiciones temporales entre inspecciones consecutivas.

    Cada fila usa únicamente información disponible en la inspección anterior
    para predecir el resultado de la siguiente. Así se evita utilizar el
    estado actual de la serie como una regla o como fuga de información.
    """
    settings = get_settings()
    engine = get_engine()

    query = text(
        """
        WITH inspecciones_ordenadas AS (
            SELECT
                ins.id,
                ins.serie_id,
                ins.resultado::text AS ultimo_resultado_inspeccion,
                COALESCE(ins.realizada_en, ins.created_at) AS fecha_corte,
                LEAD(ins.resultado::text) OVER (
                    PARTITION BY ins.serie_id
                    ORDER BY COALESCE(ins.realizada_en, ins.created_at), ins.id
                ) AS resultado_futuro,
                LEAD(COALESCE(ins.realizada_en, ins.created_at)) OVER (
                    PARTITION BY ins.serie_id
                    ORDER BY COALESCE(ins.realizada_en, ins.created_at), ins.id
                ) AS fecha_futura
            FROM inspecciones ins
            WHERE ins.deleted_at IS NULL
              AND ins.serie_id IS NOT NULL
        )
        SELECT
            s.id AS serie_id,
            s.articulo_id,
            a.categoria_id,
            a.tipo::text AS tipo_articulo,
            a.seguimiento::text AS seguimiento,
            io.ultimo_resultado_inspeccion,
            io.resultado_futuro,
            GREATEST(1, EXTRACT(DAY FROM io.fecha_futura - io.fecha_corte)) AS horizonte_dias,
            COALESCE(op.operaciones_total, 0) AS operaciones_total,
            COALESCE(op.operaciones_90d, 0) AS operaciones_90d,
            COALESCE(inc.incidencias_total, 0) AS incidencias_total,
            COALESCE(inc.incidencias_90d, 0) AS incidencias_90d,
            COALESCE(m.mantenimientos_total, 0) AS mantenimientos_total,
            COALESCE(m.mantenimientos_180d, 0) AS mantenimientos_180d,
            CASE WHEN m.ultimo_mantenimiento_at IS NULL THEN 1 ELSE 0 END AS sin_mantenimiento,
            CASE WHEN op.ultima_operacion_at IS NULL THEN 1 ELSE 0 END AS sin_operacion,
            CASE WHEN inc.ultima_incidencia_at IS NULL THEN 1 ELSE 0 END AS sin_incidencia,
            0 AS sin_inspeccion,
            LEAST(COALESCE(EXTRACT(DAY FROM io.fecha_corte - m.ultimo_mantenimiento_at), :dias_cap), :dias_cap) AS dias_desde_ultimo_mantenimiento,
            LEAST(COALESCE(EXTRACT(DAY FROM io.fecha_corte - op.ultima_operacion_at), :dias_cap), :dias_cap) AS dias_desde_ultima_operacion,
            LEAST(COALESCE(EXTRACT(DAY FROM io.fecha_corte - inc.ultima_incidencia_at), :dias_cap), :dias_cap) AS dias_desde_ultima_incidencia,
            0 AS dias_desde_ultima_inspeccion
        FROM inspecciones_ordenadas io
        INNER JOIN articulo_series s ON s.id = io.serie_id
        INNER JOIN articulos a ON a.id = s.articulo_id
        LEFT JOIN LATERAL (
            SELECT
                COUNT(*) AS operaciones_total,
                COUNT(*) FILTER (
                    WHERE o.fecha >= io.fecha_corte - (:lookback_days || ' days')::interval
                ) AS operaciones_90d,
                MAX(o.fecha) AS ultima_operacion_at
            FROM operacion_detalle_series ods
            INNER JOIN operacion_detalles od ON od.id = ods.operacion_detalle_id
            INNER JOIN operaciones o ON o.id = od.operacion_id
            WHERE ods.serie_id = s.id
              AND ods.deleted_at IS NULL
              AND od.deleted_at IS NULL
              AND o.deleted_at IS NULL
              AND o.fecha <= io.fecha_corte
        ) op ON TRUE
        LEFT JOIN LATERAL (
            SELECT
                COUNT(*) AS incidencias_total,
                COUNT(*) FILTER (
                    WHERE i.fecha >= io.fecha_corte - (:lookback_days || ' days')::interval
                ) AS incidencias_90d,
                MAX(i.fecha) AS ultima_incidencia_at
            FROM incidencias i
            WHERE i.serie_id = s.id
              AND i.deleted_at IS NULL
              AND i.fecha <= io.fecha_corte
        ) inc ON TRUE
        LEFT JOIN LATERAL (
            SELECT
                COUNT(*) AS mantenimientos_total,
                COUNT(*) FILTER (
                    WHERE COALESCE(m.fecha_inicio, m.created_at) >=
                        io.fecha_corte - (:maintenance_lookback_days || ' days')::interval
                ) AS mantenimientos_180d,
                MAX(COALESCE(m.fecha_fin, m.fecha_inicio, m.created_at)) AS ultimo_mantenimiento_at
            FROM mantenimientos m
            WHERE m.serie_id = s.id
              AND m.deleted_at IS NULL
              AND COALESCE(m.fecha_inicio, m.created_at) <= io.fecha_corte
        ) m ON TRUE
        WHERE io.resultado_futuro IS NOT NULL
          AND io.fecha_futura > io.fecha_corte
          AND s.deleted_at IS NULL
          AND a.deleted_at IS NULL
        ORDER BY io.fecha_corte, s.id
        """
    )

    with engine.connect() as connection:
        df = pd.read_sql(
            query,
            connection,
            params={
                "lookback_days": settings.lookback_days,
                "maintenance_lookback_days": settings.maintenance_lookback_days,
                "dias_cap": settings.dias_cap,
            },
        )

    numeric_columns = [
        "serie_id",
        "articulo_id",
        "categoria_id",
        "horizonte_dias",
        "operaciones_total",
        "operaciones_90d",
        "incidencias_total",
        "incidencias_90d",
        "mantenimientos_total",
        "mantenimientos_180d",
        "sin_mantenimiento",
        "sin_operacion",
        "sin_incidencia",
        "sin_inspeccion",
        "dias_desde_ultimo_mantenimiento",
        "dias_desde_ultima_operacion",
        "dias_desde_ultima_incidencia",
        "dias_desde_ultima_inspeccion",
    ]
    for column in numeric_columns:
        df[column] = pd.to_numeric(df[column], errors="coerce").fillna(0)

    return df


def build_future_prediction_frame(
    df: pd.DataFrame,
    horizon_days: int,
) -> pd.DataFrame:
    frame = build_prediction_frame(df)
    frame["horizonte_dias"] = int(horizon_days)
    return frame


def export_dataset_csv(output_path: str = "data/dataset_armamento.csv") -> Path:
    df = load_armamento_dataset()
    path = Path(output_path)
    path.parent.mkdir(parents=True, exist_ok=True)
    df.to_csv(path, index=False)
    return path


def dataset_summary() -> dict[str, object]:
    df = load_armamento_dataset()
    return {
        "total_registros": int(len(df)),
        "condicion_actual": {str(k): int(v) for k, v in df["condicion_actual"].value_counts(dropna=False).to_dict().items()},
        "estado_actual": {str(k): int(v) for k, v in df["estado_actual"].value_counts(dropna=False).to_dict().items()},
    }


def utc_now_iso() -> str:
    return datetime.now(timezone.utc).isoformat()


if __name__ == "__main__":
    path = export_dataset_csv()
    print(f"Dataset exportado en: {path}")
    print(dataset_summary())
