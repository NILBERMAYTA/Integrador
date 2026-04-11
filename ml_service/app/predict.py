from __future__ import annotations

from typing import Any

import pandas as pd

from app.features import build_prediction_frame, load_armamento_dataset, utc_now_iso
from app.model_registry import load_model_bundle
from app.settings import get_settings


def classify_risk(probability: float) -> str:
    if probability >= 0.75:
        return "alto"
    if probability >= 0.4:
        return "medio"
    return "bajo"


def recommend_action(probability: float, estado_predicho: str) -> str:
    if estado_predicho == "inoperativo" or probability >= 0.75:
        return "Programar inspeccion inmediata y mantenimiento correctivo."
    if probability >= 0.4:
        return "Realizar seguimiento preventivo y revisar incidencias recientes."
    return "Mantener monitoreo rutinario."


def list_armamento_predictions(limit: int = 100) -> list[dict[str, Any]]:
    bundle = load_model_bundle()
    model = bundle["model"]
    settings = get_settings()

    df = load_armamento_dataset(limit=max(1, min(limit, 500)))
    if df.empty:
        return []

    prediction_frame = build_prediction_frame(df)
    predicted_classes = model.predict(prediction_frame)
    predicted_probabilities = model.predict_proba(prediction_frame)[:, 1]
    prediction_time = utc_now_iso()

    results: list[dict[str, Any]] = []
    for index, row in df.reset_index(drop=True).iterrows():
        probability = float(predicted_probabilities[index])
        predicted_class = int(predicted_classes[index])
        estado_predicho = "inoperativo" if predicted_class == 1 else "operativo"

        results.append(
            {
                "serie_id": int(row["serie_id"]),
                "articulo_id": int(row["articulo_id"]),
                "unidad_id": int(row["unidad_id"]) if not pd.isna(row["unidad_id"]) else None,
                "codigo_serie": str(row["codigo_serie"]),
                "estado_predicho": estado_predicho,
                "probabilidad": round(probability, 4),
                "nivel_riesgo": classify_risk(probability),
                "recomendacion": recommend_action(probability, estado_predicho),
                "fecha_prediccion": prediction_time,
                "modelo_version": bundle.get("model_version", settings.model_version),
            }
        )

    return results
