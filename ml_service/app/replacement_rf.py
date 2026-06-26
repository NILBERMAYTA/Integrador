from __future__ import annotations

from datetime import datetime, timedelta, timezone
from pathlib import Path
from typing import Any

import joblib
import numpy as np
import pandas as pd
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

from app.data import build_future_prediction_frame, load_armamento_dataset


INSPECTION_TO_CONDITION = {
    "apto": "bueno",
    "observado": "con_defectos",
    "inoperativo": "inoperativo",
}
CATEGORICAL_COLUMNS = [
    "articulo_id",
    "categoria_id",
    "tipo_articulo",
    "seguimiento",
    "ultimo_resultado_inspeccion",
]


def model_exists(model_path: Path) -> bool:
    return model_path.exists()


def load_model_bundle(model_path: Path) -> dict[str, Any]:
    if not model_path.exists():
        raise FileNotFoundError(f"No existe el modelo Random Forest de reposicion en {model_path}")

    bundle = joblib.load(model_path)
    if "model" not in bundle:
        raise RuntimeError("El modelo de reposicion guardado no tiene el formato esperado.")

    return bundle


def _pipeline(columns: list[str]) -> Pipeline:
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
                    n_estimators=220,
                    random_state=42,
                    class_weight="balanced_subsample",
                    max_depth=14,
                    min_samples_leaf=4,
                    max_features="sqrt",
                    n_jobs=-1,
                ),
            ),
        ]
    )


def _replacement_target(future_df: pd.DataFrame) -> pd.Series:
    future_condition = (
        future_df["resultado_futuro"]
        .astype(str)
        .map(INSPECTION_TO_CONDITION)
        .fillna("con_defectos")
    )

    return future_condition.isin(["malo", "inoperativo"]).astype(int)


def _metrics(y_true: pd.Series, y_pred: np.ndarray) -> dict[str, Any]:
    return {
        "total_registros": int(len(y_true)),
        "total_entrenamiento": int(len(y_true)),
        "total_prueba": 0,
        "accuracy": float(accuracy_score(y_true, y_pred)),
        "balanced_accuracy": float(balanced_accuracy_score(y_true, y_pred)),
        "precision_macro": float(precision_score(y_true, y_pred, average="macro", zero_division=0)),
        "recall_macro": float(recall_score(y_true, y_pred, average="macro", zero_division=0)),
        "f1_macro": float(f1_score(y_true, y_pred, average="macro", zero_division=0)),
        "clases": {
            "no_reponer": int((y_true == 0).sum()),
            "reponer": int((y_true == 1).sum()),
        },
    }


def train_replacement_model(
    future_df: pd.DataFrame,
    model_path: Path,
    model_version: str,
) -> dict[str, Any]:
    if future_df.empty:
        raise RuntimeError("No existen transiciones historicas para entrenar reposicion.")

    horizon_days = max(1, int(round(float(future_df["horizonte_dias"].median()))))
    frame = build_future_prediction_frame(future_df, horizon_days=horizon_days)
    frame["horizonte_dias"] = pd.to_numeric(
        future_df["horizonte_dias"],
        errors="coerce",
    ).fillna(horizon_days)
    target = _replacement_target(future_df)

    if target.nunique() < 2:
        raise RuntimeError(
            f"Se necesitan casos con y sin reposicion para entrenar. Distribucion: {target.value_counts().to_dict()}"
        )

    model = _pipeline(list(frame.columns))
    stratify = target if int(target.value_counts().min()) >= 2 else None
    test_size = 0.2 if len(frame) >= 100 else 0.3
    X_train, X_test, y_train, y_test = train_test_split(
        frame,
        target,
        test_size=test_size,
        random_state=42,
        stratify=stratify,
    )
    model.fit(X_train, y_train)
    test_pred = model.predict(X_test)
    test_metrics = _metrics(y_test, test_pred)

    model.fit(frame, target)
    train_pred = model.predict(frame)
    full_metrics = _metrics(target, train_pred)
    full_metrics.update({
        "total_entrenamiento": int(len(X_train)),
        "total_prueba": int(len(X_test)),
        "test_accuracy": test_metrics["accuracy"],
        "test_balanced_accuracy": test_metrics["balanced_accuracy"],
        "test_f1_macro": test_metrics["f1_macro"],
    })

    model_path.parent.mkdir(parents=True, exist_ok=True)
    joblib.dump(
        {
            "engine": "random_forest",
            "model": model,
            "feature_columns": list(frame.columns),
            "horizon_days": horizon_days,
            "model_version": model_version,
            "metrics": full_metrics,
            "saved_at": datetime.now(timezone.utc).isoformat(),
        },
        model_path,
    )

    return full_metrics


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


def _replacement_probability(bundle: dict[str, Any], frame: pd.DataFrame) -> list[float]:
    model: Pipeline = bundle["model"]
    probabilities = model.predict_proba(frame)
    classes = [int(value) for value in model.named_steps["classifier"].classes_]
    if 1 not in classes:
        return [0.0 for _ in range(len(frame))]

    index = classes.index(1)
    return [round(float(row[index]), 6) for row in probabilities]


def _future_bucket(probability: float) -> str:
    if probability >= 0.55:
        return "inoperativo"
    if probability >= 0.35:
        return "malo"
    if probability >= 0.18:
        return "con_defectos"
    return "bueno"


def _urgency(probability: float) -> tuple[str, int, int]:
    if probability >= 0.55:
        return "inmediata", 0, 30
    if probability >= 0.38:
        return "proxima", 30, 60
    if probability >= 0.18:
        return "planificada", 60, 90
    return "estable", 90, 180


def _series_predictions(
    unidad_id: int | None,
    model_path: Path,
) -> list[dict[str, Any]]:
    bundle = load_model_bundle(model_path)
    df = load_armamento_dataset(unidad_id=unidad_id)
    if df.empty:
        return []

    horizon_days = int(bundle.get("horizon_days", 90))
    frame = build_future_prediction_frame(df, horizon_days=horizon_days)
    probabilities = _replacement_probability(bundle, frame)
    predictions: list[dict[str, Any]] = []

    for index, row in df.reset_index(drop=True).iterrows():
        coverage = _history_coverage(row)
        probability = 0.0 if coverage == "sin_historial" else probabilities[index]
        predictions.append(
            {
                "serie_id": int(row["serie_id"]),
                "articulo_id": int(row["articulo_id"]),
                "articulo_nombre": str(row["articulo_nombre"]),
                "categoria_nombre": str(row["categoria_nombre"]),
                "unidad_id": int(row["unidad_id"]) if not pd.isna(row["unidad_id"]) else None,
                "unidad_nombre": str(row["unidad_nombre"]),
                "codigo_serie": str(row["codigo_serie"]),
                "cobertura_historica": coverage,
                "probabilidad_reposicion": probability,
                "condicion_futura_reposicion": "indeterminada" if coverage == "sin_historial" else _future_bucket(probability),
                "confianza_reposicion": max(probability, 1 - probability),
            }
        )

    return predictions


def replacement_recommendations(
    unidad_id: int | None,
    model_path: Path,
) -> dict[str, Any]:
    bundle = load_model_bundle(model_path)
    horizon_days = int(bundle.get("horizon_days", 90))
    predictions = _series_predictions(unidad_id, model_path)
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
            unit_group["reposicion_esperada"] += float(item["probabilidad_reposicion"])

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
        condition_key = f"futuro_{item['condicion_futura_reposicion']}"
        group[condition_key] = int(group.get(condition_key, 0)) + 1
        if item["cobertura_historica"] != "sin_historial":
            group["series_con_historial"] += 1
            group["reposicion_esperada"] += float(item["probabilidad_reposicion"])
            group["confianza_futura_acumulada"] += float(item["confianza_reposicion"])

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
                    "motor_reposicion": "random_forest",
                    "motivo": "No existen datos historicos suficientes para estimar reposicion con Random Forest.",
                }
            )
            continue

        ratio = expected / evaluated
        urgency, days_min, days_max = _urgency(ratio)
        recommendations.append(
            {
                **group,
                "cantidad_sugerida": int(np.ceil(expected)),
                "reposicion_esperada": round(expected, 2),
                "tasa_reposicion_esperada": round(ratio, 6),
                "confianza_futura_promedio": round(confidence_total / evaluated, 6),
                "cobertura_historica_pct": round((evaluated / total) * 100, 2),
                "urgencia": urgency,
                "dias_recomendados_min": days_min,
                "dias_recomendados_max": days_max,
                "fecha_sugerida_desde": (datetime.now(timezone.utc) + timedelta(days=days_min)).date().isoformat(),
                "horizonte_dias": horizon_days,
                "motor_reposicion": "random_forest",
                "motivo": f"Random Forest estima {expected:.2f} reposiciones entre {total} series para el horizonte analizado.",
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
        else:
            urgency = _urgency(expected / evaluated)[0]
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
        "motor_reposicion": "random_forest",
        "resumen": {
            "articulos_evaluados": len(recommendations),
            "reposicion_inmediata": urgency_counts["inmediata"],
            "reposicion_proxima": urgency_counts["proxima"],
            "cantidad_sugerida_total": int(sum(item["cantidad_sugerida"] for item in recommendations)),
            "series_evaluadas": len(predictions),
        },
        "unidades": unit_summaries,
        "recomendaciones": recommendations,
    }

