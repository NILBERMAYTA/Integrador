from __future__ import annotations

import base64
import io
import sys
from datetime import datetime, timezone
from functools import lru_cache
from pathlib import Path
from typing import Any

import matplotlib

matplotlib.use("Agg")

import matplotlib.pyplot as plt
import numpy as np
import pandas as pd

ROOT_DIR = Path(__file__).resolve().parents[2]
if str(ROOT_DIR) not in sys.path:
    sys.path.insert(0, str(ROOT_DIR))

from app.config import get_settings
from app.data import load_armamento_dataset
from app.ml import load_model_bundle, predict_armamento_dataframe
from fuzzy_logic.armamento import FEATURE_LABELS, explain_row


def _model_signature() -> str:
    path = get_settings().model_path
    return f"{path.stat().st_mtime_ns}:{path.stat().st_size}"


def _sample_dataset(df: pd.DataFrame, sample_size: int) -> pd.DataFrame:
    if len(df) <= sample_size:
        return df.copy()

    groups: list[pd.DataFrame] = []
    for _, group in df.groupby("condicion_actual", dropna=False):
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


def _bar_image(title: str, labels: list[str], values: list[float], color: str = "#2563eb") -> str:
    y = np.arange(len(labels))
    plt.figure(figsize=(9, 5.5))
    plt.barh(y, values, color=color)
    plt.yticks(y, labels)
    plt.gca().invert_yaxis()
    plt.xlabel("Aporte difuso")
    plt.title(title, fontsize=14, pad=14)
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

    bundle = load_model_bundle()
    sampled = _sample_dataset(df, sample_size)
    contributions = [explain_row(row, bundle) for _, row in sampled.iterrows()]
    features = list(FEATURE_LABELS.keys())
    matrix = np.array(
        [[float(item.get(feature, 0.0)) for feature in features] for item in contributions],
        dtype=float,
    )
    mean_abs = np.abs(matrix).mean(axis=0)
    positive_mean = np.clip(matrix, 0, None).mean(axis=0)
    negative_mean = np.clip(matrix, None, 0).mean(axis=0)
    order = np.argsort(mean_abs)[::-1]

    importance = [
        {
            "feature": features[index],
            "label": FEATURE_LABELS[features[index]],
            "importance": round(float(mean_abs[index]), 6),
            "positive_impact": round(float(positive_mean[index]), 6),
            "negative_impact": round(float(negative_mean[index]), 6),
        }
        for index in order
    ]

    labels = [FEATURE_LABELS[features[index]] for index in order]
    values = [float(mean_abs[index]) for index in order]
    positive_values = [float(positive_mean[index]) for index in order]

    return {
        "unidad_id": unidad_id,
        "total_records": int(len(df)),
        "sample_size": int(len(sampled)),
        "base_value": 0.0,
        "generated_at": datetime.now(timezone.utc).isoformat(),
        "importance": importance,
        "beeswarm_image": _bar_image("Importancia global de reglas difusas", labels, values),
        "dependence_image": _bar_image("Aporte positivo promedio", labels, positive_values, "#16a34a"),
        "top_feature": labels[0] if labels else "",
        "explained_class": "riesgo_operativo",
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
    bundle = load_model_bundle()
    prediction = predict_armamento_dataframe(row)[0]
    contributions_map = explain_row(row.iloc[0], bundle)
    contributions = sorted(
        [
            {
                "feature": feature,
                "label": FEATURE_LABELS.get(feature, feature.replace("_", " ").title()),
                "feature_value": str(round(float(value), 4)),
                "shap_value": round(float(value), 6),
                "absolute_impact": round(float(abs(value)), 6),
                "direction": "aumenta" if value >= 0 else "reduce",
            }
            for feature, value in contributions_map.items()
        ],
        key=lambda item: item["absolute_impact"],
        reverse=True,
    )

    labels = [item["label"] for item in contributions]
    values = [float(item["shap_value"]) for item in contributions]
    colors = ["#dc2626" if value >= 0 else "#2563eb" for value in values]
    y = np.arange(len(labels))
    plt.figure(figsize=(9, 5.5))
    plt.barh(y, values, color=colors)
    plt.yticks(y, labels)
    plt.gca().invert_yaxis()
    plt.xlabel("Aporte a riesgo futuro")
    plt.title(f"Explicacion difusa: {row.iloc[0]['codigo_serie']}", fontsize=14)
    waterfall_image = _figure_data_uri()

    return {
        "serie_id": int(row.iloc[0]["serie_id"]),
        "codigo_serie": str(row.iloc[0]["codigo_serie"]),
        "unidad_id": int(row.iloc[0]["unidad_id"]),
        "unidad_nombre": str(row.iloc[0]["unidad_nombre"]),
        "probability": round(float(prediction["confianza_actual"]), 6),
        "predicted_state": prediction["condicion_actual_predicha"],
        "future_condition": prediction["condicion_futura_predicha"],
        "future_confidence": prediction["confianza_futura"],
        "risk_level": prediction["nivel_riesgo"],
        "recommendation": prediction["recomendacion"],
        "base_value": 0.0,
        "contributions": contributions,
        "waterfall_image": waterfall_image,
        "generated_at": datetime.now(timezone.utc).isoformat(),
    }

