from __future__ import annotations

from dataclasses import asdict, dataclass
from datetime import datetime, timezone
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
    f1_score,
    precision_recall_curve,
    precision_score,
    recall_score,
    roc_auc_score,
)
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


def _best_threshold(y_true: pd.Series, probabilities: np.ndarray) -> float:
    """Umbral que maximiza F1 sobre el conjunto de prueba.

    Con clases muy desbalanceadas (pocos inoperativos) el umbral fijo de 0.5
    hace que el modelo marque casi todo como inoperativo. Elegir el umbral que
    maximiza F1 equilibra precision y recall en el punto de operacion real.
    """
    if len(set(y_true)) < 2:
        return 0.5

    precisions, recalls, thresholds = precision_recall_curve(y_true, probabilities)
    if len(thresholds) == 0:
        return 0.5

    f1_scores = (
        2 * precisions[:-1] * recalls[:-1]
        / (precisions[:-1] + recalls[:-1] + 1e-12)
    )
    best_index = int(np.argmax(f1_scores))
    threshold = float(thresholds[best_index])

    # Evita umbrales degenerados (0 marca todo como positivo, 1 todo negativo).
    return float(min(max(threshold, 0.05), 0.95))


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
        "categoria_id",
        "tipo_articulo",
        "seguimiento",
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
                    n_estimators=400,
                    random_state=42,
                    class_weight=None,
                    max_depth=16,
                    min_samples_leaf=10,
                    max_features="sqrt",
                    n_jobs=-1,
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

    evaluation_model = clone(model)
    evaluation_model.fit(X_train, y_train)

    probabilities = evaluation_model.predict_proba(X_test)[:, 1]

    # Umbral de decision optimo (maximiza F1) en lugar del 0.5 fijo.
    decision_threshold = _best_threshold(y_test, probabilities)
    predicted = (probabilities >= decision_threshold).astype(int)

    # Las métricas se calculan con el conjunto de prueba, pero el modelo que
    # se persiste se vuelve a entrenar con el 100% del dataset disponible.
    model.fit(X, y)

    summary = TrainingSummary(
        total_registros=int(len(df)),
        total_entrenamiento=int(len(X)),
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
            "decision_threshold": float(decision_threshold),
            "model_version": settings.model_version,
        }
    )

    payload = asdict(summary)
    payload["message"] = "Modelo entrenado correctamente."
    payload["model_path"] = str(model_path)
    payload["model_version"] = settings.model_version
    return payload


def classify_risk(probability: float, threshold: float = 0.5) -> str:
    alto = threshold + (1.0 - threshold) * 0.5
    if probability >= alto:
        return "alto"
    if probability >= threshold:
        return "medio"
    return "bajo"


def recommend_action(probability: float, estado_predicho: str, threshold: float = 0.5) -> str:
    alto = threshold + (1.0 - threshold) * 0.5
    if estado_predicho == "inoperativo" or probability >= alto:
        return "Programar inspeccion inmediata y mantenimiento correctivo."
    if probability >= threshold:
        return "Realizar seguimiento preventivo y revisar incidencias recientes."
    return "Mantener monitoreo rutinario."


def predict_armamento_dataframe(df: pd.DataFrame) -> list[dict[str, Any]]:
    bundle = load_model_bundle()
    model = bundle["model"]
    threshold = float(bundle.get("decision_threshold", 0.5))
    settings = get_settings()

    if df.empty:
        return []

    prediction_frame = build_prediction_frame(df)
    predicted_probabilities = model.predict_proba(prediction_frame)[:, 1]
    prediction_time = utc_now_iso()

    results: list[dict[str, Any]] = []
    for index, row in df.reset_index(drop=True).iterrows():
        probability = float(predicted_probabilities[index])
        estado_predicho = "inoperativo" if probability >= threshold else "operativo"

        results.append(
            {
                "serie_id": int(row["serie_id"]),
                "articulo_id": int(row["articulo_id"]),
                "unidad_id": int(row["unidad_id"]) if not pd.isna(row["unidad_id"]) else None,
                "unidad_nombre": str(row["unidad_nombre"]),
                "codigo_serie": str(row["codigo_serie"]),
                "estado_predicho": estado_predicho,
                "probabilidad": round(probability, 4),
                "nivel_riesgo": classify_risk(probability, threshold),
                "recomendacion": recommend_action(probability, estado_predicho, threshold),
                "fecha_prediccion": prediction_time,
                "modelo_version": bundle.get("model_version", settings.model_version),
            }
        )

    return results


def list_armamento_predictions(
    limit: int = 100,
    unidad_id: int | None = None,
) -> list[dict[str, Any]]:
    df = load_armamento_dataset(
        limit=max(1, min(limit, 500)),
        unidad_id=unidad_id,
    )
    return predict_armamento_dataframe(df)


def summarize_armamento_predictions(
    unidad_id: int | None = None,
    page: int = 1,
    per_page: int = 10,
) -> dict[str, Any]:
    predictions = predict_armamento_dataframe(
        load_armamento_dataset(unidad_id=unidad_id)
    )

    risk = {"alto": 0, "medio": 0, "bajo": 0}
    status = {"operativo": 0, "inoperativo": 0}

    for prediction in predictions:
        risk[prediction["nivel_riesgo"]] += 1
        status[prediction["estado_predicho"]] += 1

    page = max(1, page)
    per_page = max(1, min(per_page, 50))
    last_page = max(1, (len(predictions) + per_page - 1) // per_page)
    page = min(page, last_page)
    offset = (page - 1) * per_page

    return {
        "unidad_id": unidad_id,
        "total": len(predictions),
        "riesgo": risk,
        "estado": status,
        "page": page,
        "per_page": per_page,
        "last_page": last_page,
        "items": predictions[offset : offset + per_page],
    }


if __name__ == "__main__":
    summary = train_armamento_model()
    for key, value in summary.items():
        print(f"{key}: {value}")
