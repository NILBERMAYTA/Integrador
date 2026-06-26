from __future__ import annotations

import json
from datetime import datetime, timezone
from functools import lru_cache
from pathlib import Path
from typing import Any

import numpy as np
import pandas as pd


INSPECTION_TO_CONDITION = {
    "apto": "bueno",
    "observado": "con_defectos",
    "inoperativo": "inoperativo",
}
CONDITION_ORDER = ("bueno", "con_defectos", "malo", "inoperativo")
FEATURE_LABELS = {
    "usage": "Uso operativo",
    "incidents": "Incidencias",
    "maintenance_gap": "Brecha de mantenimiento",
    "inspection_gap": "Antiguedad de inspeccion",
    "inspection_alert": "Resultado de inspeccion",
    "history": "Cobertura historica",
}


def utc_now_iso() -> str:
    return datetime.now(timezone.utc).isoformat()


def model_exists(model_path: Path) -> bool:
    return model_path.exists()


def load_model_bundle(model_path: Path) -> dict[str, Any]:
    if not model_path.exists():
        raise FileNotFoundError(f"No existe la configuracion difusa en {model_path}")

    with model_path.open("r", encoding="utf-8") as handle:
        return json.load(handle)


def save_model_bundle(model_path: Path, bundle: dict[str, Any]) -> Path:
    model_path.parent.mkdir(parents=True, exist_ok=True)
    bundle["saved_at"] = utc_now_iso()
    with model_path.open("w", encoding="utf-8") as handle:
        json.dump(bundle, handle, ensure_ascii=False, indent=2, sort_keys=True)
    _cached_predictions.cache_clear()
    return model_path


def _clamp(value: float, low: float = 0.0, high: float = 1.0) -> float:
    return max(low, min(high, float(value)))


def _ramp_up(value: float, start: float, full: float) -> float:
    if full <= start:
        return 1.0 if value >= full else 0.0
    return _clamp((float(value) - start) / (full - start))


def _ramp_down(value: float, start: float, zero: float) -> float:
    if zero <= start:
        return 0.0 if value >= zero else 1.0
    return _clamp((zero - float(value)) / (zero - start))


def _triangle(value: float, left: float, peak: float, right: float) -> float:
    value = float(value)
    if value <= left or value >= right:
        return 0.0
    if value == peak:
        return 1.0
    if value < peak:
        return _clamp((value - left) / (peak - left))
    return _clamp((right - value) / (right - peak))


def _safe_quantile(series: pd.Series, quantile: float, fallback: float) -> float:
    numeric = pd.to_numeric(series, errors="coerce").dropna()
    if numeric.empty:
        return fallback
    return max(fallback, float(numeric.quantile(quantile)))


def _profile_from_data(
    current_df: pd.DataFrame,
    future_df: pd.DataFrame,
    model_version: str,
) -> dict[str, Any]:
    horizon_days = 90
    if not future_df.empty and "horizonte_dias" in future_df.columns:
        horizon_days = max(
            1,
            int(round(float(pd.to_numeric(future_df["horizonte_dias"], errors="coerce").median()))),
        )

    thresholds = {
        "operations_high": _safe_quantile(current_df.get("operaciones_90d", pd.Series()), 0.82, 4.0),
        "incidents_high": _safe_quantile(current_df.get("incidencias_90d", pd.Series()), 0.78, 1.0),
        "maintenance_late": _safe_quantile(current_df.get("dias_desde_ultimo_mantenimiento", pd.Series()), 0.65, 120.0),
        "maintenance_critical": _safe_quantile(current_df.get("dias_desde_ultimo_mantenimiento", pd.Series()), 0.88, 365.0),
        "inspection_late": _safe_quantile(current_df.get("dias_desde_ultima_inspeccion", pd.Series()), 0.65, 90.0),
        "inspection_critical": _safe_quantile(current_df.get("dias_desde_ultima_inspeccion", pd.Series()), 0.88, 240.0),
    }

    return {
        "engine": "fuzzy_logic",
        "model_version": model_version,
        "horizon_days": horizon_days,
        "thresholds": thresholds,
        "rules": [
            "Incidencias recientes y resultados inoperativos elevan riesgo critico.",
            "Mantenimiento e inspeccion vencidos elevan deterioro progresivo.",
            "Uso operativo frecuente sin mantenimiento desplaza la condicion hacia defectos.",
            "Series sin historial quedan como indeterminadas para futuro.",
        ],
    }


def _feature_memberships(row: pd.Series, bundle: dict[str, Any], horizon_days: int = 0) -> dict[str, float]:
    thresholds = bundle["thresholds"]
    horizon_factor = _clamp(horizon_days / max(1.0, float(bundle.get("horizon_days", 90))), 0.0, 1.5)

    operations_90d = float(row.get("operaciones_90d", 0) or 0)
    incidents_90d = float(row.get("incidencias_90d", 0) or 0)
    maint_days = float(row.get("dias_desde_ultimo_mantenimiento", 0) or 0)
    insp_days = float(row.get("dias_desde_ultima_inspeccion", 0) or 0)
    sin_mantenimiento = float(row.get("sin_mantenimiento", 0) or 0)
    sin_inspeccion = float(row.get("sin_inspeccion", 0) or 0)
    last_inspection = str(row.get("ultimo_resultado_inspeccion", "sin_inspeccion"))

    usage = _ramp_up(operations_90d, thresholds["operations_high"] * 0.35, thresholds["operations_high"])
    incidents = _ramp_up(incidents_90d, 0.0, thresholds["incidents_high"])
    maintenance_gap = max(
        _ramp_up(maint_days + horizon_days, thresholds["maintenance_late"], thresholds["maintenance_critical"]),
        0.78 if sin_mantenimiento >= 1 else 0.0,
    )
    inspection_gap = max(
        _ramp_up(insp_days + horizon_days, thresholds["inspection_late"], thresholds["inspection_critical"]),
        0.68 if sin_inspeccion >= 1 else 0.0,
    )
    inspection_alert = {
        "apto": 0.0,
        "observado": 0.58,
        "inoperativo": 1.0,
        "sin_inspeccion": max(0.35, 0.35 + (0.15 * horizon_factor)),
    }.get(last_inspection, 0.35)
    history = 1.0 - (0.25 * float(row.get("sin_operacion", 0) or 0)) - (0.25 * float(row.get("sin_incidencia", 0) or 0)) - (0.25 * sin_mantenimiento) - (0.25 * sin_inspeccion)

    return {
        "usage": _clamp(usage),
        "incidents": _clamp(incidents),
        "maintenance_gap": _clamp(maintenance_gap),
        "inspection_gap": _clamp(inspection_gap),
        "inspection_alert": _clamp(inspection_alert),
        "history": _clamp(history),
    }


def _condition_scores(features: dict[str, float], future: bool = False) -> dict[str, float]:
    usage = features["usage"]
    incidents = features["incidents"]
    maintenance = features["maintenance_gap"]
    inspection = features["inspection_gap"]
    alert = features["inspection_alert"]
    history = features["history"]
    horizon_bias = 0.1 if future else 0.0

    critical = max(
        min(alert, 1.0),
        min(incidents, max(maintenance, inspection)),
        0.7 * incidents + 0.2 * maintenance + 0.1 * usage,
    )
    degraded = max(
        min(max(maintenance, inspection), max(0.35, usage)),
        0.45 * alert + 0.35 * maintenance + 0.2 * usage,
        0.4 * incidents + 0.35 * inspection,
    )
    defective = max(
        min(usage, 1.0 - (critical * 0.35)),
        0.35 * maintenance + 0.25 * inspection + 0.25 * alert + 0.15 * usage,
    )
    good = min(
        _ramp_down(critical, 0.08, 0.5),
        _ramp_down(degraded, 0.15, 0.65),
        max(0.2, history),
    )

    return {
        "bueno": _clamp(good - horizon_bias),
        "con_defectos": _clamp(defective + (horizon_bias * 0.45)),
        "malo": _clamp(degraded + (horizon_bias * 0.6)),
        "inoperativo": _clamp(critical + (horizon_bias * 0.75)),
    }


def _score_probabilities(scores: dict[str, float]) -> dict[str, float]:
    values = np.array([scores[condition] for condition in CONDITION_ORDER], dtype=float)
    if float(values.sum()) <= 0:
        values = np.array([1.0, 0.0, 0.0, 0.0], dtype=float)
    values = values + 0.025
    values = values / values.sum()
    return {
        condition: round(float(values[index]), 6)
        for index, condition in enumerate(CONDITION_ORDER)
    }


def _predicted_condition(probabilities: dict[str, float]) -> str:
    return max(probabilities.items(), key=lambda item: item[1])[0]


def _critical_probability(probabilities: dict[str, float]) -> float:
    return min(
        1.0,
        float(probabilities.get("inoperativo", 0.0))
        + (float(probabilities.get("malo", 0.0)) * 0.5),
    )


def classify_risk(probabilities: dict[str, float]) -> str:
    critical = _critical_probability(probabilities)
    defective = float(probabilities.get("con_defectos", 0.0))
    if critical >= 0.45:
        return "alto"
    if critical >= 0.28 or defective >= 0.35:
        return "medio"
    return "bajo"


def recommend_action(future_condition: str, future_probabilities: dict[str, float]) -> str:
    risk = classify_risk(future_probabilities)
    if risk == "alto":
        return "Preparar reposicion e inspeccion prioritaria antes del horizonte previsto."
    if risk == "medio":
        return "Programar inspeccion preventiva y reservar capacidad de reposicion."
    if future_condition == "con_defectos":
        return "Mantener seguimiento preventivo de la serie."
    return "Mantener monitoreo rutinario."


def _history_coverage(row: pd.Series) -> str:
    sources = sum(
        [
            int(float(row.get("operaciones_total", 0)) > 0),
            int(float(row.get("incidencias_total", 0)) > 0),
            int(float(row.get("mantenimientos_total", 0)) > 0),
            int(float(row.get("sin_inspeccion", 1)) == 0),
        ]
    )
    if sources >= 3:
        return "alta"
    if sources >= 1:
        return "parcial"
    return "sin_historial"


def _predict_row(row: pd.Series, bundle: dict[str, Any]) -> dict[str, Any]:
    horizon_days = int(bundle.get("horizon_days", 90))
    current_features = _feature_memberships(row, bundle, horizon_days=0)
    future_features = _feature_memberships(row, bundle, horizon_days=horizon_days)
    current_probs = _score_probabilities(_condition_scores(current_features))
    future_probs = _score_probabilities(_condition_scores(future_features, future=True))
    coverage = _history_coverage(row)
    current_condition = _predicted_condition(current_probs)
    future_condition = _predicted_condition(future_probs)

    if coverage == "sin_historial":
        future_condition = "indeterminada"
        risk = "sin_datos"
        expected_replacement = 0.0
        future_confidence = 0.0
    else:
        risk = classify_risk(future_probs)
        expected_replacement = _critical_probability(future_probs)
        future_confidence = max(future_probs.values())

    return {
        "current_features": current_features,
        "future_features": future_features,
        "current_condition": current_condition,
        "future_condition": future_condition,
        "current_probabilities": current_probs,
        "future_probabilities": future_probs,
        "risk": risk,
        "coverage": coverage,
        "expected_replacement": expected_replacement,
        "future_confidence": future_confidence,
    }


def predict_armamento_dataframe(
    df: pd.DataFrame,
    model_path: Path,
    model_version: str,
) -> list[dict[str, Any]]:
    bundle = load_model_bundle(model_path)
    if df.empty:
        return []

    prediction_time = utc_now_iso()
    results: list[dict[str, Any]] = []
    for _, row in df.reset_index(drop=True).iterrows():
        prediction = _predict_row(row, bundle)
        current_probs = prediction["current_probabilities"]
        future_probs = prediction["future_probabilities"]
        future_condition = prediction["future_condition"]

        results.append(
            {
                "serie_id": int(row["serie_id"]),
                "articulo_id": int(row["articulo_id"]),
                "articulo_nombre": str(row["articulo_nombre"]),
                "categoria_nombre": str(row["categoria_nombre"]),
                "unidad_id": int(row["unidad_id"]) if not pd.isna(row["unidad_id"]) else None,
                "unidad_nombre": str(row["unidad_nombre"]),
                "codigo_serie": str(row["codigo_serie"]),
                "condicion_actual_predicha": prediction["current_condition"],
                "confianza_actual": round(max(current_probs.values()), 6),
                "probabilidades_actuales": current_probs,
                "condicion_futura_predicha": future_condition,
                "confianza_futura": round(float(prediction["future_confidence"]), 6),
                "probabilidades_futuras": future_probs,
                "horizonte_dias": int(bundle.get("horizon_days", 90)),
                "cobertura_historica": prediction["coverage"],
                "nivel_riesgo": prediction["risk"],
                "probabilidad_reposicion": round(float(prediction["expected_replacement"]), 6),
                "recomendacion": recommend_action(future_condition, future_probs),
                "fecha_prediccion": prediction_time,
                "modelo_version": bundle.get("model_version", model_version),
                "estado_predicho": prediction["current_condition"],
                "probabilidad": round(max(current_probs.values()), 6),
            }
        )
    return results


def _classification_metrics(y_true: list[str], y_pred: list[str]) -> dict[str, Any]:
    labels = sorted(set(y_true) | set(y_pred))
    total = max(1, len(y_true))
    accuracy = sum(1 for real, pred in zip(y_true, y_pred) if real == pred) / total
    recalls = []
    precisions = []
    f1s = []
    classes = {label: y_true.count(label) for label in labels}

    for label in labels:
        tp = sum(1 for real, pred in zip(y_true, y_pred) if real == label and pred == label)
        fp = sum(1 for real, pred in zip(y_true, y_pred) if real != label and pred == label)
        fn = sum(1 for real, pred in zip(y_true, y_pred) if real == label and pred != label)
        precision = tp / max(1, tp + fp)
        recall = tp / max(1, tp + fn)
        f1 = (2 * precision * recall / max(0.000001, precision + recall)) if (precision + recall) else 0.0
        precisions.append(precision)
        recalls.append(recall)
        f1s.append(f1)

    return {
        "total_registros": len(y_true),
        "total_entrenamiento": len(y_true),
        "total_prueba": 0,
        "accuracy": round(float(accuracy), 6),
        "balanced_accuracy": round(float(np.mean(recalls) if recalls else 0.0), 6),
        "precision_macro": round(float(np.mean(precisions) if precisions else 0.0), 6),
        "recall_macro": round(float(np.mean(recalls) if recalls else 0.0), 6),
        "f1_macro": round(float(np.mean(f1s) if f1s else 0.0), 6),
        "clases": classes,
    }


def train_armamento_model(
    current_df: pd.DataFrame,
    future_df: pd.DataFrame,
    model_path: Path,
    model_version: str,
) -> dict[str, Any]:
    if current_df.empty:
        raise RuntimeError("No se encontraron series para calibrar la logica difusa.")

    bundle = _profile_from_data(current_df, future_df, model_version)
    current_predictions = [
        _predict_row(row, bundle)["current_condition"]
        for _, row in current_df.reset_index(drop=True).iterrows()
    ]
    current_y = current_df["condicion_actual"].astype(str).fillna("bueno").tolist()
    current_metrics = _classification_metrics(current_y, current_predictions)

    future_metrics = {
        "total_registros": 0,
        "total_entrenamiento": 0,
        "total_prueba": 0,
        "accuracy": 0.0,
        "balanced_accuracy": 0.0,
        "precision_macro": 0.0,
        "recall_macro": 0.0,
        "f1_macro": 0.0,
        "clases": {},
    }
    if not future_df.empty:
        future_predictions = []
        for _, row in future_df.reset_index(drop=True).iterrows():
            fuzzy_row = row.copy()
            fuzzy_row["articulo_nombre"] = fuzzy_row.get("articulo_nombre", "")
            fuzzy_row["categoria_nombre"] = fuzzy_row.get("categoria_nombre", "")
            fuzzy_row["unidad_nombre"] = fuzzy_row.get("unidad_nombre", "")
            fuzzy_row["codigo_serie"] = fuzzy_row.get("codigo_serie", "")
            future_predictions.append(_predict_row(fuzzy_row, bundle)["future_condition"])
        future_y = (
            future_df["resultado_futuro"].astype(str).map(INSPECTION_TO_CONDITION).fillna("con_defectos").tolist()
        )
        future_predictions = [
            "con_defectos" if value == "indeterminada" else value
            for value in future_predictions
        ]
        future_metrics = _classification_metrics(future_y, future_predictions)

    bundle["current_metrics"] = current_metrics
    bundle["future_metrics"] = future_metrics
    bundle["total_historial_futuro"] = int(len(future_df))
    save_model_bundle(model_path, bundle)

    return {
        "message": "Motor de logica difusa calibrado correctamente.",
        "model_path": str(model_path),
        "model_version": model_version,
        "total_registros": int(len(current_df)),
        "total_historial_futuro": int(len(future_df)),
        "horizon_days": int(bundle["horizon_days"]),
        "current_metrics": current_metrics,
        "future_metrics": future_metrics,
        "accuracy": current_metrics["accuracy"],
        "precision": current_metrics["precision_macro"],
        "recall": current_metrics["recall_macro"],
        "f1": current_metrics["f1_macro"],
        "roc_auc": None,
        "total_entrenamiento": current_metrics["total_entrenamiento"],
        "total_prueba": current_metrics["total_prueba"],
    }


def _model_signature(model_path: Path) -> str:
    return f"{model_path.stat().st_mtime_ns}:{model_path.stat().st_size}"


@lru_cache(maxsize=96)
def _cached_predictions(
    model_signature: str,
    unidad_id: int | None,
    model_path: str,
    model_version: str,
    loader_id: int,
) -> tuple[dict[str, Any], ...]:
    del model_signature, loader_id
    from app.data import load_armamento_dataset

    return tuple(
        predict_armamento_dataframe(
            load_armamento_dataset(unidad_id=unidad_id),
            Path(model_path),
            model_version,
        )
    )


def all_armamento_predictions(
    unidad_id: int | None,
    model_path: Path,
    model_version: str,
) -> list[dict[str, Any]]:
    return [
        dict(item)
        for item in _cached_predictions(
            _model_signature(model_path),
            unidad_id,
            str(model_path),
            model_version,
            id(_cached_predictions),
        )
    ]


def list_armamento_predictions(
    limit: int,
    unidad_id: int | None,
    model_path: Path,
    model_version: str,
) -> list[dict[str, Any]]:
    return all_armamento_predictions(unidad_id, model_path, model_version)[: max(1, min(limit, 500))]


def summarize_armamento_predictions(
    unidad_id: int | None,
    page: int,
    per_page: int,
    model_path: Path,
    model_version: str,
) -> dict[str, Any]:
    predictions = all_armamento_predictions(unidad_id, model_path, model_version)
    bundle = load_model_bundle(model_path)
    risk = {"alto": 0, "medio": 0, "bajo": 0, "sin_datos": 0}
    current = {condition: 0 for condition in (*CONDITION_ORDER, "indeterminada")}
    future = {condition: 0 for condition in (*CONDITION_ORDER, "indeterminada")}
    coverage = {"alta": 0, "parcial": 0, "sin_historial": 0}

    for prediction in predictions:
        risk[prediction["nivel_riesgo"]] += 1
        current[prediction["condicion_actual_predicha"]] = current.get(prediction["condicion_actual_predicha"], 0) + 1
        future[prediction["condicion_futura_predicha"]] = future.get(prediction["condicion_futura_predicha"], 0) + 1
        coverage[prediction["cobertura_historica"]] += 1

    page = max(1, page)
    per_page = max(1, min(per_page, 50))
    last_page = max(1, (len(predictions) + per_page - 1) // per_page)
    page = min(page, last_page)
    offset = (page - 1) * per_page
    return {
        "unidad_id": unidad_id,
        "total": len(predictions),
        "riesgo": risk,
        "condicion_actual": current,
        "condicion_futura": future,
        "cobertura": coverage,
        "horizonte_dias": int(bundle.get("horizon_days", 90)),
        "page": page,
        "per_page": per_page,
        "last_page": last_page,
        "items": predictions[offset : offset + per_page],
    }


def explain_row(row: pd.Series, bundle: dict[str, Any]) -> dict[str, float]:
    features = _feature_memberships(row, bundle, horizon_days=int(bundle.get("horizon_days", 90)))
    return {
        "usage": 0.22 * features["usage"],
        "incidents": 0.32 * features["incidents"],
        "maintenance_gap": 0.2 * features["maintenance_gap"],
        "inspection_gap": 0.14 * features["inspection_gap"],
        "inspection_alert": 0.28 * features["inspection_alert"],
        "history": -0.12 * features["history"],
    }
