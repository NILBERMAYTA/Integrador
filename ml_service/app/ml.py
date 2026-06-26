from __future__ import annotations

from datetime import datetime, timedelta, timezone
from functools import lru_cache
from pathlib import Path
from typing import Any

import joblib
import numpy as np
import pandas as pd
from sklearn.base import clone
from sklearn.compose import ColumnTransformer
from sklearn.ensemble import RandomForestClassifier
from sklearn.metrics import (
    accuracy_score,
    balanced_accuracy_score,
    f1_score,
    precision_score,
    recall_score,
)
from sklearn.model_selection import train_test_split
from sklearn.pipeline import Pipeline
from sklearn.preprocessing import OneHotEncoder

from app.config import get_settings
from app.data import (
    build_future_prediction_frame,
    build_prediction_frame,
    load_armamento_dataset,
    load_future_training_dataset,
    utc_now_iso,
)


INSPECTION_TO_CONDITION = {
    "apto": "bueno",
    "observado": "con_defectos",
    "inoperativo": "inoperativo",
}
CONDITION_ORDER = ("bueno", "con_defectos", "malo", "inoperativo")
CATEGORICAL_COLUMNS = [
    "articulo_id",
    "categoria_id",
    "tipo_articulo",
    "seguimiento",
    "ultimo_resultado_inspeccion",
]


def model_exists() -> bool:
    return get_settings().model_path.exists()


def load_model_bundle() -> dict[str, Any]:
    settings = get_settings()
    if not settings.model_path.exists():
        raise FileNotFoundError(f"No existe el modelo en {settings.model_path}")
    bundle = joblib.load(settings.model_path)
    if "current_model" not in bundle or "future_model" not in bundle:
        raise RuntimeError(
            "El modelo guardado usa el formato binario anterior. Reentrena el modelo."
        )
    return bundle


def save_model_bundle(bundle: dict[str, Any]) -> Path:
    settings = get_settings()
    settings.model_path.parent.mkdir(parents=True, exist_ok=True)
    bundle["saved_at"] = datetime.now(timezone.utc).isoformat()
    joblib.dump(bundle, settings.model_path)
    _cached_predictions.cache_clear()
    return settings.model_path


def _pipeline(columns: list[str], balanced: bool = False) -> Pipeline:
    categorical = [column for column in CATEGORICAL_COLUMNS if column in columns]
    numeric = [column for column in columns if column not in categorical]
    preprocessor = ColumnTransformer(
        transformers=[
            ("categorical", OneHotEncoder(handle_unknown="ignore"), categorical),
            ("numeric", "passthrough", numeric),
        ]
    )
    return Pipeline(
        steps=[
            ("preprocessor", preprocessor),
            (
                "classifier",
                RandomForestClassifier(
                    n_estimators=180,
                    random_state=42,
                    class_weight="balanced_subsample" if balanced else None,
                    max_depth=16,
                    min_samples_leaf=6,
                    max_features="sqrt",
                    n_jobs=-1,
                ),
            ),
        ]
    )


def _evaluate_and_fit(
    model: Pipeline,
    X: pd.DataFrame,
    y: pd.Series,
) -> tuple[Pipeline, dict[str, Any]]:
    if y.nunique() < 2:
        raise RuntimeError(
            f"Se necesitan al menos dos clases para entrenar. Distribución: {y.value_counts().to_dict()}"
        )

    test_size = 0.2 if len(X) >= 100 else 0.3
    stratify = y if int(y.value_counts().min()) >= 2 else None
    X_train, X_test, y_train, y_test = train_test_split(
        X,
        y,
        test_size=test_size,
        random_state=42,
        stratify=stratify,
    )

    evaluation_model = clone(model)
    evaluation_model.fit(X_train, y_train)
    predicted = evaluation_model.predict(X_test)

    metrics = {
        "total_registros": int(len(X)),
        "total_entrenamiento": int(len(X_train)),
        "total_prueba": int(len(X_test)),
        "accuracy": float(accuracy_score(y_test, predicted)),
        "balanced_accuracy": float(balanced_accuracy_score(y_test, predicted)),
        "precision_macro": float(
            precision_score(y_test, predicted, average="macro", zero_division=0)
        ),
        "recall_macro": float(
            recall_score(y_test, predicted, average="macro", zero_division=0)
        ),
        "f1_macro": float(
            f1_score(y_test, predicted, average="macro", zero_division=0)
        ),
        "clases": {
            str(key): int(value)
            for key, value in y.value_counts(dropna=False).to_dict().items()
        },
    }
    model.fit(X, y)
    return model, metrics


def train_armamento_model() -> dict[str, object]:
    current_df = load_armamento_dataset()
    future_df = load_future_training_dataset()

    if current_df.empty:
        raise RuntimeError("No se encontraron series para entrenar la condición actual.")
    if future_df.empty:
        raise RuntimeError(
            "No existen pares de inspecciones consecutivas para entrenar la condición futura."
        )

    current_X = build_prediction_frame(current_df)
    current_y = current_df["condicion_actual"].astype(str)
    current_model, current_metrics = _evaluate_and_fit(
        _pipeline(list(current_X.columns), balanced=False),
        current_X,
        current_y,
    )

    horizon_days = max(1, int(round(float(future_df["horizonte_dias"].median()))))
    future_X = build_future_prediction_frame(future_df, horizon_days=horizon_days)
    future_X["horizonte_dias"] = pd.to_numeric(
        future_df["horizonte_dias"], errors="coerce"
    ).fillna(horizon_days)
    future_y = (
        future_df["resultado_futuro"]
        .astype(str)
        .map(INSPECTION_TO_CONDITION)
        .fillna("con_defectos")
    )
    future_model, future_metrics = _evaluate_and_fit(
        _pipeline(list(future_X.columns), balanced=True),
        future_X,
        future_y,
    )

    settings = get_settings()
    model_path = save_model_bundle(
        {
            "current_model": current_model,
            "future_model": future_model,
            "current_feature_columns": list(current_X.columns),
            "future_feature_columns": list(future_X.columns),
            "current_metrics": current_metrics,
            "future_metrics": future_metrics,
            "horizon_days": horizon_days,
            "model_version": settings.model_version,
        }
    )

    return {
        "message": "Modelos de condición actual y futura entrenados correctamente.",
        "model_path": str(model_path),
        "model_version": settings.model_version,
        "total_registros": int(len(current_df)),
        "total_historial_futuro": int(len(future_df)),
        "horizon_days": horizon_days,
        "current_metrics": current_metrics,
        "future_metrics": future_metrics,
        # Alias temporales para consumidores existentes.
        "accuracy": current_metrics["accuracy"],
        "precision": current_metrics["precision_macro"],
        "recall": current_metrics["recall_macro"],
        "f1": current_metrics["f1_macro"],
        "roc_auc": None,
        "total_entrenamiento": current_metrics["total_entrenamiento"],
        "total_prueba": current_metrics["total_prueba"],
    }


def _probability_map(model: Pipeline, frame: pd.DataFrame) -> list[dict[str, float]]:
    probabilities = model.predict_proba(frame)
    classes = [str(value) for value in model.named_steps["classifier"].classes_]
    return [
        {
            condition: round(float(row[index]), 6)
            for index, condition in enumerate(classes)
        }
        for row in probabilities
    ]


def _critical_probability(probabilities: dict[str, float]) -> float:
    return min(
        1.0,
        float(probabilities.get("inoperativo", 0.0))
        + (float(probabilities.get("malo", 0.0)) * 0.5),
    )


def classify_risk(probabilities: dict[str, float]) -> str:
    """Riesgo derivado exclusivamente de probabilidades del modelo futuro."""
    critical = _critical_probability(probabilities)
    defective = float(probabilities.get("con_defectos", 0.0))
    if critical >= 0.28:
        return "alto"
    if critical >= 0.15 or defective >= 0.35:
        return "medio"
    return "bajo"


def recommend_action(
    future_condition: str,
    future_probabilities: dict[str, float],
) -> str:
    risk = classify_risk(future_probabilities)
    if risk == "alto":
        return "Preparar reposición e inspección prioritaria antes del horizonte previsto."
    if risk == "medio":
        return "Programar inspección preventiva y reservar capacidad de reposición."
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


def predict_armamento_dataframe(df: pd.DataFrame) -> list[dict[str, Any]]:
    bundle = load_model_bundle()
    settings = get_settings()
    if df.empty:
        return []

    current_frame = build_prediction_frame(df)
    horizon_days = int(bundle.get("horizon_days", 90))
    future_frame = build_future_prediction_frame(df, horizon_days=horizon_days)
    current_model = bundle["current_model"]
    future_model = bundle["future_model"]

    current_conditions = current_model.predict(current_frame)
    future_conditions = future_model.predict(future_frame)
    current_probabilities = _probability_map(current_model, current_frame)
    future_probabilities = _probability_map(future_model, future_frame)
    prediction_time = utc_now_iso()

    results: list[dict[str, Any]] = []
    for index, row in df.reset_index(drop=True).iterrows():
        current_condition = str(current_conditions[index])
        future_condition = str(future_conditions[index])
        current_probs = current_probabilities[index]
        future_probs = future_probabilities[index]
        coverage = _history_coverage(row)
        if coverage == "sin_historial":
            future_condition = "indeterminada"
            risk = "sin_datos"
            expected_replacement = 0.0
            future_confidence = 0.0
        else:
            risk = classify_risk(future_probs)
            expected_replacement = _critical_probability(future_probs)
            future_confidence = max(future_probs.values())

        results.append(
            {
                "serie_id": int(row["serie_id"]),
                "articulo_id": int(row["articulo_id"]),
                "articulo_nombre": str(row["articulo_nombre"]),
                "categoria_nombre": str(row["categoria_nombre"]),
                "unidad_id": int(row["unidad_id"]) if not pd.isna(row["unidad_id"]) else None,
                "unidad_nombre": str(row["unidad_nombre"]),
                "codigo_serie": str(row["codigo_serie"]),
                "condicion_actual_predicha": current_condition,
                "confianza_actual": round(max(current_probs.values()), 6),
                "probabilidades_actuales": current_probs,
                "condicion_futura_predicha": future_condition,
                "confianza_futura": round(future_confidence, 6),
                "probabilidades_futuras": future_probs,
                "horizonte_dias": horizon_days,
                "cobertura_historica": coverage,
                "nivel_riesgo": risk,
                "probabilidad_reposicion": round(expected_replacement, 6),
                "recomendacion": recommend_action(future_condition, future_probs),
                "fecha_prediccion": prediction_time,
                "modelo_version": bundle.get("model_version", settings.model_version),
                # Compatibilidad con clientes anteriores; ya no usa reglas híbridas.
                "estado_predicho": current_condition,
                "probabilidad": round(max(current_probs.values()), 6),
            }
        )
    return results


def _model_signature() -> str:
    path = get_settings().model_path
    return f"{path.stat().st_mtime_ns}:{path.stat().st_size}"


@lru_cache(maxsize=96)
def _cached_predictions(
    model_signature: str,
    unidad_id: int | None,
) -> tuple[dict[str, Any], ...]:
    del model_signature
    return tuple(
        predict_armamento_dataframe(load_armamento_dataset(unidad_id=unidad_id))
    )


def all_armamento_predictions(
    unidad_id: int | None = None,
) -> list[dict[str, Any]]:
    return [dict(item) for item in _cached_predictions(_model_signature(), unidad_id)]


def list_armamento_predictions(
    limit: int = 100,
    unidad_id: int | None = None,
) -> list[dict[str, Any]]:
    return all_armamento_predictions(unidad_id=unidad_id)[: max(1, min(limit, 500))]


def summarize_armamento_predictions(
    unidad_id: int | None = None,
    page: int = 1,
    per_page: int = 10,
) -> dict[str, Any]:
    predictions = all_armamento_predictions(unidad_id=unidad_id)
    risk = {"alto": 0, "medio": 0, "bajo": 0, "sin_datos": 0}
    current = {condition: 0 for condition in (*CONDITION_ORDER, "indeterminada")}
    future = {condition: 0 for condition in (*CONDITION_ORDER, "indeterminada")}
    coverage = {"alta": 0, "parcial": 0, "sin_historial": 0}

    for prediction in predictions:
        risk[prediction["nivel_riesgo"]] += 1
        current[prediction["condicion_actual_predicha"]] = (
            current.get(prediction["condicion_actual_predicha"], 0) + 1
        )
        future[prediction["condicion_futura_predicha"]] = (
            future.get(prediction["condicion_futura_predicha"], 0) + 1
        )
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
        "horizonte_dias": int(load_model_bundle().get("horizon_days", 90)),
        "page": page,
        "per_page": per_page,
        "last_page": last_page,
        "items": predictions[offset : offset + per_page],
    }


def replacement_recommendations(
    unidad_id: int | None = None,
) -> dict[str, Any]:
    predictions = all_armamento_predictions(unidad_id=unidad_id)
    horizon_days = int(load_model_bundle().get("horizon_days", 90))
    groups: dict[tuple[int | None, int], dict[str, Any]] = {}
    unit_groups: dict[int | None, dict[str, Any]] = {}

    for item in predictions:
        unit_group = unit_groups.setdefault(
            item["unidad_id"],
            {
                "unidad_id": item["unidad_id"],
                "unidad_nombre": item["unidad_nombre"],
                "total_series": 0,
                "series_con_historial": 0,
                "reposicion_esperada": 0.0,
            },
        )
        unit_group["total_series"] += 1
        if item["cobertura_historica"] != "sin_historial":
            unit_group["series_con_historial"] += 1
            unit_group["reposicion_esperada"] += float(
                item["probabilidad_reposicion"]
            )

        key = (item["unidad_id"], item["articulo_id"])
        group = groups.setdefault(
            key,
            {
                "unidad_id": item["unidad_id"],
                "unidad_nombre": item["unidad_nombre"],
                "articulo_id": item["articulo_id"],
                "articulo": item["articulo_nombre"],
                "categoria": item["categoria_nombre"],
                "total_series": 0,
                "futuro_bueno": 0,
                "futuro_con_defectos": 0,
                "futuro_malo": 0,
                "futuro_inoperativo": 0,
                "reposicion_esperada": 0.0,
                "confianza_futura_acumulada": 0.0,
                "series_con_historial": 0,
            },
        )
        group["total_series"] += 1
        condition_key = f"futuro_{item['condicion_futura_predicha']}"
        group[condition_key] = int(group.get(condition_key, 0)) + 1
        if item["cobertura_historica"] != "sin_historial":
            group["series_con_historial"] += 1
            group["reposicion_esperada"] += float(item["probabilidad_reposicion"])
            group["confianza_futura_acumulada"] += float(item["confianza_futura"])

    recommendations: list[dict[str, Any]] = []
    for group in groups.values():
        total = max(1, int(group["total_series"]))
        expected = float(group.pop("reposicion_esperada"))
        confidence_total = float(group.pop("confianza_futura_acumulada"))
        evaluated = int(group["series_con_historial"])
        if evaluated == 0:
            if unidad_id is None:
                continue
            recommendations.append(
                {
                    **group,
                    "cantidad_sugerida": 0,
                    "reposicion_esperada": 0.0,
                    "tasa_reposicion_esperada": 0.0,
                    "confianza_futura_promedio": 0.0,
                    "cobertura_historica_pct": 0.0,
                    "urgencia": "sin_datos",
                    "dias_recomendados_min": 0,
                    "dias_recomendados_max": 0,
                    "fecha_sugerida_desde": None,
                    "horizonte_dias": horizon_days,
                    "motivo": (
                        "No existen inspecciones históricas suficientes para "
                        "estimar reposición con el modelo."
                    ),
                }
            )
            continue
        quantity = int(np.ceil(expected))
        ratio = expected / evaluated
        if ratio >= 0.24:
            urgency, days_min, days_max = "inmediata", 0, 30
        elif ratio >= 0.18:
            urgency, days_min, days_max = "proxima", 30, 60
        elif ratio >= 0.08:
            urgency, days_min, days_max = "planificada", 60, 90
        else:
            urgency, days_min, days_max = "estable", 90, 180

        recommendations.append(
            {
                **group,
                "cantidad_sugerida": quantity,
                "reposicion_esperada": round(expected, 2),
                "tasa_reposicion_esperada": round(ratio, 6),
                "confianza_futura_promedio": round(confidence_total / evaluated, 6),
                "cobertura_historica_pct": round(
                    (int(group["series_con_historial"]) / total) * 100, 2
                ),
                "urgencia": urgency,
                "dias_recomendados_min": days_min,
                "dias_recomendados_max": days_max,
                "fecha_sugerida_desde": (
                    datetime.now(timezone.utc) + timedelta(days=days_min)
                ).date().isoformat(),
                "horizonte_dias": horizon_days,
                "motivo": (
                    f"El modelo estima {expected:.2f} reposiciones entre {total} series "
                    f"para el horizonte analizado."
                ),
            }
        )

    recommendations.sort(
        key=lambda item: (
            float(item["tasa_reposicion_esperada"]),
            float(item["reposicion_esperada"]),
        ),
        reverse=True,
    )
    urgency_counts = {
        name: sum(1 for item in recommendations if item["urgencia"] == name)
        for name in ("inmediata", "proxima", "planificada", "estable")
    }
    unit_summaries: list[dict[str, Any]] = []
    for group in unit_groups.values():
        total = max(1, int(group["total_series"]))
        evaluated = int(group["series_con_historial"])
        expected = float(group["reposicion_esperada"])
        if evaluated == 0:
            urgency = "sin_datos"
            ratio = 0.0
        else:
            ratio = expected / evaluated
            urgency = (
                "inmediata"
                if ratio >= 0.24
                else "proxima"
                if ratio >= 0.18
                else "planificada"
                if ratio >= 0.08
                else "estable"
            )
        unit_summaries.append(
            {
                **group,
                "cantidad_sugerida": int(np.ceil(expected)),
                "reposicion_esperada": round(expected, 2),
                "cobertura_historica_pct": round((evaluated / total) * 100, 2),
                "urgencia": urgency,
            }
        )
    unit_summaries.sort(
        key=lambda item: (
            item["urgencia"] != "sin_datos",
            float(item["reposicion_esperada"]),
        ),
        reverse=True,
    )
    return {
        "unidad_id": unidad_id,
        "horizonte_dias": horizon_days,
        "resumen": {
            "articulos_evaluados": len(recommendations),
            "reposicion_inmediata": urgency_counts["inmediata"],
            "reposicion_proxima": urgency_counts["proxima"],
            "cantidad_sugerida_total": int(
                sum(item["cantidad_sugerida"] for item in recommendations)
            ),
            "series_evaluadas": len(predictions),
        },
        "unidades": unit_summaries,
        "recomendaciones": recommendations,
    }


if __name__ == "__main__":
    summary = train_armamento_model()
    for key, value in summary.items():
        print(f"{key}: {value}")
