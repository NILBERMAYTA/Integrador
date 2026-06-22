from __future__ import annotations

import base64
import io
from datetime import datetime, timezone
from functools import lru_cache
from pathlib import Path
from typing import Any

import matplotlib

matplotlib.use("Agg")

import matplotlib.pyplot as plt
import numpy as np
import pandas as pd
import shap

from app.data import build_prediction_frame, load_armamento_dataset
from app.ml import classify_risk, load_model_bundle, recommend_action


FEATURE_LABELS = {
    "categoria_id": "Categoría",
    "tipo_articulo": "Tipo de artículo",
    "seguimiento": "Tipo de seguimiento",
    "operaciones_total": "Operaciones históricas",
    "operaciones_90d": "Operaciones en 90 días",
    "incidencias_total": "Incidencias históricas",
    "incidencias_90d": "Incidencias en 90 días",
    "mantenimientos_total": "Mantenimientos históricos",
    "mantenimientos_180d": "Mantenimientos en 180 días",
    "sin_mantenimiento": "Sin mantenimiento previo",
    "sin_operacion": "Sin operación previa",
    "sin_incidencia": "Sin incidencias",
    "sin_inspeccion": "Sin inspección previa",
    "dias_desde_ultimo_mantenimiento": "Días desde mantenimiento",
    "dias_desde_ultima_operacion": "Días desde operación",
    "dias_desde_ultima_incidencia": "Días desde incidencia",
    "ultimo_resultado_inspeccion": "Última inspección",
    "dias_desde_ultima_inspeccion": "Días desde inspección",
}


def _model_signature() -> str:
    path = Path(load_model_bundle_path())
    return f"{path.stat().st_mtime_ns}:{path.stat().st_size}"


def load_model_bundle_path() -> str:
    from app.config import get_settings

    return str(get_settings().model_path)


def _sample_dataset(df: pd.DataFrame, sample_size: int) -> pd.DataFrame:
    if len(df) <= sample_size:
        return df.copy()

    groups: list[pd.DataFrame] = []
    for _, group in df.groupby("resultado", dropna=False):
        group_size = max(1, round(sample_size * len(group) / len(df)))
        groups.append(group.sample(n=min(group_size, len(group)), random_state=42))

    sampled = pd.concat(groups).drop_duplicates(subset=["serie_id"])
    if len(sampled) > sample_size:
        sampled = sampled.sample(n=sample_size, random_state=42)
    elif len(sampled) < sample_size:
        remaining = df.loc[~df.index.isin(sampled.index)]
        extra = remaining.sample(
            n=min(sample_size - len(sampled), len(remaining)),
            random_state=42,
        )
        sampled = pd.concat([sampled, extra])

    return sampled.sort_values("serie_id", ascending=False)


def _components() -> tuple[Any, Any, Any]:
    bundle = load_model_bundle()
    pipeline = bundle["model"]
    preprocessor = pipeline.named_steps["preprocessor"]
    classifier = pipeline.named_steps["classifier"]
    return bundle, preprocessor, classifier


def _positive_explanation(
    frame: pd.DataFrame,
) -> tuple[shap.Explanation, np.ndarray, list[str]]:
    _, preprocessor, classifier = _components()
    transformed = preprocessor.transform(frame)
    if hasattr(transformed, "toarray"):
        transformed = transformed.toarray()
    transformed = np.asarray(transformed, dtype=float)

    feature_names = list(preprocessor.get_feature_names_out())
    explainer = shap.TreeExplainer(classifier)
    explanation = explainer(transformed)
    values = np.asarray(explanation.values)
    base_values = np.asarray(explanation.base_values)

    if values.ndim == 3:
        values = values[:, :, 1]
    if base_values.ndim == 2:
        base_values = base_values[:, 1]

    positive = shap.Explanation(
        values=values,
        base_values=base_values,
        data=transformed,
        feature_names=[_encoded_label(name) for name in feature_names],
    )
    return positive, transformed, feature_names


def _original_feature(encoded_name: str) -> str:
    if encoded_name.startswith("numeric__"):
        return encoded_name.removeprefix("numeric__")

    encoded_name = encoded_name.removeprefix("categorical__")
    for feature in (
        "categoria_id",
        "tipo_articulo",
        "seguimiento",
        "ultimo_resultado_inspeccion",
    ):
        prefix = f"{feature}_"
        if encoded_name.startswith(prefix):
            return feature

    return encoded_name


def _encoded_label(encoded_name: str) -> str:
    original = _original_feature(encoded_name)
    label = FEATURE_LABELS.get(original, original.replace("_", " ").title())

    if encoded_name.startswith("categorical__"):
        encoded = encoded_name.removeprefix("categorical__")
        suffix = encoded.removeprefix(f"{original}_").replace("_", " ")
        return f"{label}: {suffix}"

    return label


def _aggregate_values(
    shap_values: np.ndarray,
    encoded_names: list[str],
) -> tuple[list[str], np.ndarray]:
    grouped: dict[str, np.ndarray] = {}
    for index, encoded_name in enumerate(encoded_names):
        original = _original_feature(encoded_name)
        grouped.setdefault(original, np.zeros(shap_values.shape[0], dtype=float))
        grouped[original] += shap_values[:, index]

    names = list(grouped.keys())
    values = np.column_stack([grouped[name] for name in names])
    return names, values


def _figure_data_uri() -> str:
    buffer = io.BytesIO()
    plt.tight_layout()
    plt.savefig(
        buffer,
        format="png",
        dpi=150,
        bbox_inches="tight",
        facecolor="#ffffff",
    )
    plt.close()
    return "data:image/png;base64," + base64.b64encode(buffer.getvalue()).decode("ascii")


def _beeswarm_image(explanation: shap.Explanation) -> str:
    plt.figure(figsize=(10, 6.5))
    shap.plots.beeswarm(explanation, max_display=12, show=False, plot_size=None)
    plt.title("Impacto SHAP global de las variables", fontsize=14, pad=14)
    plt.xlabel("Impacto sobre la probabilidad de inoperatividad")
    return _figure_data_uri()


def _dependence_image(
    explanation: shap.Explanation,
    encoded_names: list[str],
    importance: np.ndarray,
) -> str | None:
    numeric_indexes = [
        index for index, name in enumerate(encoded_names) if name.startswith("numeric__")
    ]
    if not numeric_indexes:
        return None

    feature_index = max(numeric_indexes, key=lambda index: importance[index])
    plt.figure(figsize=(9, 5.5))
    shap.plots.scatter(explanation[:, feature_index], show=False)
    plt.title(
        f"Dependencia: {_encoded_label(encoded_names[feature_index])}",
        fontsize=14,
        pad=14,
    )
    plt.ylabel("Impacto SHAP")
    return _figure_data_uri()


@lru_cache(maxsize=24)
def _global_explanation_cached(
    model_signature: str,
    unidad_id: int | None,
    sample_size: int,
) -> dict[str, Any]:
    del model_signature
    df = load_armamento_dataset(unidad_id=unidad_id)
    if df.empty:
        raise RuntimeError("No existen series para explicar en el alcance seleccionado.")

    sampled = _sample_dataset(df, sample_size)
    frame = build_prediction_frame(sampled)
    explanation, _, encoded_names = _positive_explanation(frame)
    encoded_values = np.asarray(explanation.values)
    names, grouped_values = _aggregate_values(encoded_values, encoded_names)

    mean_abs = np.abs(grouped_values).mean(axis=0)
    positive_mean = np.clip(grouped_values, 0, None).mean(axis=0)
    negative_mean = np.clip(grouped_values, None, 0).mean(axis=0)
    order = np.argsort(mean_abs)[::-1]

    importance = [
        {
            "feature": name,
            "label": FEATURE_LABELS.get(name, name.replace("_", " ").title()),
            "importance": round(float(mean_abs[index]), 6),
            "positive_impact": round(float(positive_mean[index]), 6),
            "negative_impact": round(float(negative_mean[index]), 6),
        }
        for index, name in sorted(
            enumerate(names),
            key=lambda pair: mean_abs[pair[0]],
            reverse=True,
        )
    ]

    encoded_importance = np.abs(encoded_values).mean(axis=0)
    return {
        "unidad_id": unidad_id,
        "total_records": int(len(df)),
        "sample_size": int(len(sampled)),
        "base_value": round(float(np.mean(explanation.base_values)), 6),
        "generated_at": datetime.now(timezone.utc).isoformat(),
        "importance": importance,
        "beeswarm_image": _beeswarm_image(explanation),
        "dependence_image": _dependence_image(
            explanation,
            encoded_names,
            encoded_importance,
        ),
        "top_feature": FEATURE_LABELS.get(
            names[order[0]],
            names[order[0]].replace("_", " ").title(),
        ),
    }


def global_armamento_explanation(
    unidad_id: int | None = None,
    sample_size: int = 500,
) -> dict[str, Any]:
    return _global_explanation_cached(
        _model_signature(),
        unidad_id,
        max(50, min(sample_size, 1000)),
    )


def individual_armamento_explanation(serie_id: int) -> dict[str, Any]:
    df = load_armamento_dataset(serie_id=serie_id)
    if df.empty:
        raise RuntimeError(f"No existe la serie {serie_id}.")

    row = df.iloc[[0]]
    frame = build_prediction_frame(row)
    explanation, _, encoded_names = _positive_explanation(frame)
    encoded_values = np.asarray(explanation.values)
    names, grouped_values = _aggregate_values(encoded_values, encoded_names)
    values = grouped_values[0]
    bundle = load_model_bundle()
    threshold = float(bundle.get("decision_threshold", 0.5))
    probability = float(bundle["model"].predict_proba(frame)[0, 1])
    predicted_state = "inoperativo" if probability >= threshold else "operativo"

    contributions = sorted(
        [
            {
                "feature": name,
                "label": FEATURE_LABELS.get(name, name.replace("_", " ").title()),
                "feature_value": str(frame.iloc[0][name]),
                "shap_value": round(float(values[index]), 6),
                "absolute_impact": round(float(abs(values[index])), 6),
                "direction": "aumenta" if values[index] >= 0 else "reduce",
            }
            for index, name in enumerate(names)
        ],
        key=lambda item: item["absolute_impact"],
        reverse=True,
    )

    plt.figure(figsize=(10, 6))
    shap.plots.waterfall(explanation[0], max_display=12, show=False)
    plt.title(f"Explicación individual: {row.iloc[0]['codigo_serie']}", fontsize=14)
    waterfall_image = _figure_data_uri()

    return {
        "serie_id": int(row.iloc[0]["serie_id"]),
        "codigo_serie": str(row.iloc[0]["codigo_serie"]),
        "unidad_id": int(row.iloc[0]["unidad_id"]),
        "unidad_nombre": str(row.iloc[0]["unidad_nombre"]),
        "probability": round(probability, 6),
        "predicted_state": predicted_state,
        "risk_level": classify_risk(probability, threshold),
        "recommendation": recommend_action(probability, predicted_state, threshold),
        "base_value": round(float(explanation.base_values[0]), 6),
        "contributions": contributions,
        "waterfall_image": waterfall_image,
        "generated_at": datetime.now(timezone.utc).isoformat(),
    }
