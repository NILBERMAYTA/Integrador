from __future__ import annotations

from dataclasses import asdict, dataclass
from datetime import datetime, timezone
from pathlib import Path
from typing import Any

import joblib
import pandas as pd
from sklearn.compose import ColumnTransformer
from sklearn.ensemble import RandomForestClassifier
from sklearn.metrics import accuracy_score, f1_score, precision_score, recall_score, roc_auc_score
from sklearn.model_selection import train_test_split
from sklearn.pipeline import Pipeline
from sklearn.preprocessing import OneHotEncoder

from app.config import get_settings
from app.data import build_prediction_frame, load_armamento_dataset, utc_now_iso


@dataclass
class TrainingSummary:
    total_registros: int
    total_entrenamiento: int
    total_prueba: int
    accuracy: float
    precision: float
    recall: float
    f1: float
    roc_auc: float | None


def model_exists() -> bool:
    return get_settings().model_path.exists()


def load_model_bundle() -> dict[str, Any]:
    settings = get_settings()
    if not settings.model_path.exists():
        raise FileNotFoundError(f"No existe el modelo en {settings.model_path}")
    return joblib.load(settings.model_path)


def save_model_bundle(bundle: dict[str, Any]) -> Path:
    settings = get_settings()
    settings.model_path.parent.mkdir(parents=True, exist_ok=True)
    bundle["saved_at"] = datetime.now(timezone.utc).isoformat()
    joblib.dump(bundle, settings.model_path)
    return settings.model_path


def train_armamento_model() -> dict[str, object]:
    df = load_armamento_dataset()

    if df.empty:
        raise RuntimeError("No se encontraron registros para entrenar el modelo.")

    if df["resultado"].nunique() < 2:
        distribution = df["resultado"].value_counts(dropna=False).to_dict()
        raise RuntimeError(
            "El dataset necesita al menos dos clases en 'resultado' para entrenar. "
            f"Distribucion actual: {distribution}. "
            "Necesitas al menos algunas series con estado o condicion critica para generar la clase positiva."
        )

    X = build_prediction_frame(df)
    y = df["resultado"].astype(int)

    categorical_columns = [
        "tipo_articulo",
        "seguimiento",
        "estado_actual",
        "condicion_actual",
        "ultimo_resultado_inspeccion",
    ]
    numeric_columns = [column for column in X.columns if column not in categorical_columns]

    preprocessor = ColumnTransformer(
        transformers=[
            ("categorical", OneHotEncoder(handle_unknown="ignore"), categorical_columns),
            ("numeric", "passthrough", numeric_columns),
        ]
    )

    model = Pipeline(
        steps=[
            ("preprocessor", preprocessor),
            (
                "classifier",
                RandomForestClassifier(
                    n_estimators=250,
                    random_state=42,
                    class_weight="balanced_subsample",
                    max_depth=12,
                    min_samples_leaf=2,
                ),
            ),
        ]
    )

    X_train, X_test, y_train, y_test = train_test_split(
        X,
        y,
        test_size=0.2,
        random_state=42,
        stratify=y,
    )

    model.fit(X_train, y_train)

    predicted = model.predict(X_test)
    probabilities = model.predict_proba(X_test)[:, 1]

    summary = TrainingSummary(
        total_registros=int(len(df)),
        total_entrenamiento=int(len(X_train)),
        total_prueba=int(len(X_test)),
        accuracy=float(accuracy_score(y_test, predicted)),
        precision=float(precision_score(y_test, predicted, zero_division=0)),
        recall=float(recall_score(y_test, predicted, zero_division=0)),
        f1=float(f1_score(y_test, predicted, zero_division=0)),
        roc_auc=float(roc_auc_score(y_test, probabilities)) if len(set(y_test)) > 1 else None,
    )

    settings = get_settings()
    model_path = save_model_bundle(
        {
            "model": model,
            "feature_columns": list(X.columns),
            "metrics": asdict(summary),
            "model_version": settings.model_version,
        }
    )

    payload = asdict(summary)
    payload["message"] = "Modelo entrenado correctamente."
    payload["model_path"] = str(model_path)
    payload["model_version"] = settings.model_version
    return payload


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


if __name__ == "__main__":
    summary = train_armamento_model()
    for key, value in summary.items():
        print(f"{key}: {value}")
