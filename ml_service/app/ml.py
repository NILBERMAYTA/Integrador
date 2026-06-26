from __future__ import annotations

import sys
from pathlib import Path
from typing import Any

import pandas as pd

ROOT_DIR = Path(__file__).resolve().parents[2]
if str(ROOT_DIR) not in sys.path:
    sys.path.insert(0, str(ROOT_DIR))

from app.config import get_settings
from app.data import load_armamento_dataset, load_future_training_dataset
from fuzzy_logic.armamento import (
    CONDITION_ORDER,
    classify_risk,
    list_armamento_predictions as fuzzy_list_armamento_predictions,
    load_model_bundle as fuzzy_load_model_bundle,
    model_exists as fuzzy_model_exists,
    predict_armamento_dataframe as fuzzy_predict_armamento_dataframe,
    recommend_action,
    save_model_bundle as fuzzy_save_model_bundle,
    summarize_armamento_predictions as fuzzy_summarize_armamento_predictions,
    train_armamento_model as fuzzy_train_armamento_model,
)
from app.replacement_rf import (
    model_exists as rf_model_exists,
    replacement_recommendations as rf_replacement_recommendations,
    train_replacement_model,
)


def model_exists() -> bool:
    settings = get_settings()
    return fuzzy_model_exists(settings.model_path) and rf_model_exists(settings.replacement_model_path)


def load_model_bundle() -> dict[str, Any]:
    return fuzzy_load_model_bundle(get_settings().model_path)


def save_model_bundle(bundle: dict[str, Any]) -> Path:
    return fuzzy_save_model_bundle(get_settings().model_path, bundle)


def train_armamento_model() -> dict[str, Any]:
    settings = get_settings()
    current_df = load_armamento_dataset()
    future_df = load_future_training_dataset()
    payload = fuzzy_train_armamento_model(
        current_df=current_df,
        future_df=future_df,
        model_path=settings.model_path,
        model_version=settings.model_version,
    )
    replacement_metrics = train_replacement_model(
        future_df=future_df,
        model_path=settings.replacement_model_path,
        model_version=settings.model_version,
    )
    fuzzy_bundle = load_model_bundle()
    fuzzy_bundle["future_metrics"] = replacement_metrics
    fuzzy_bundle["replacement_engine"] = "random_forest"
    fuzzy_bundle["replacement_model_path"] = str(settings.replacement_model_path)
    save_model_bundle(fuzzy_bundle)

    payload["message"] = "Motor difuso calibrado y Random Forest de reposicion entrenado correctamente."
    payload["future_metrics"] = replacement_metrics
    payload["accuracy"] = payload["current_metrics"]["accuracy"]
    payload["precision"] = payload["current_metrics"]["precision_macro"]
    payload["recall"] = payload["current_metrics"]["recall_macro"]
    payload["f1"] = payload["current_metrics"]["f1_macro"]
    return payload


def train_fuzzy_state_model() -> dict[str, Any]:
    settings = get_settings()
    return fuzzy_train_armamento_model(
        current_df=load_armamento_dataset(),
        future_df=load_future_training_dataset(),
        model_path=settings.model_path,
        model_version=settings.model_version,
    )


def predict_armamento_dataframe(df: pd.DataFrame) -> list[dict[str, Any]]:
    settings = get_settings()
    return fuzzy_predict_armamento_dataframe(
        df=df,
        model_path=settings.model_path,
        model_version=settings.model_version,
    )


def list_armamento_predictions(
    limit: int = 100,
    unidad_id: int | None = None,
) -> list[dict[str, Any]]:
    settings = get_settings()
    return fuzzy_list_armamento_predictions(
        limit=limit,
        unidad_id=unidad_id,
        model_path=settings.model_path,
        model_version=settings.model_version,
    )


def summarize_armamento_predictions(
    unidad_id: int | None = None,
    page: int = 1,
    per_page: int = 10,
) -> dict[str, Any]:
    settings = get_settings()
    return fuzzy_summarize_armamento_predictions(
        unidad_id=unidad_id,
        page=page,
        per_page=per_page,
        model_path=settings.model_path,
        model_version=settings.model_version,
    )


def replacement_recommendations(
    unidad_id: int | None = None,
) -> dict[str, Any]:
    settings = get_settings()
    return rf_replacement_recommendations(
        unidad_id=unidad_id,
        model_path=settings.replacement_model_path,
    )


if __name__ == "__main__":
    summary = train_armamento_model()
    for key, value in summary.items():
        print(f"{key}: {value}")
