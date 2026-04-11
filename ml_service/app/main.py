from __future__ import annotations

from fastapi import FastAPI, HTTPException, Query
from pydantic import BaseModel

from app.config import get_settings
from app.ml import list_armamento_predictions, model_exists, train_armamento_model


class HealthResponse(BaseModel):
    status: str
    model_ready: bool
    model_path: str
    model_version: str


class TrainResponse(BaseModel):
    message: str
    model_path: str
    model_version: str
    total_registros: int
    total_entrenamiento: int
    total_prueba: int
    accuracy: float
    precision: float
    recall: float
    f1: float
    roc_auc: float | None = None


class PredictionItem(BaseModel):
    serie_id: int
    articulo_id: int
    unidad_id: int | None = None
    codigo_serie: str
    estado_predicho: str
    probabilidad: float
    nivel_riesgo: str
    recomendacion: str
    fecha_prediccion: str
    modelo_version: str


class PersistResponse(BaseModel):
    message: str
    total: int


app = FastAPI(
    title="Armutop ML Service",
    version="1.0.0",
    description="API de entrenamiento y prediccion para armamento.",
)


@app.get("/health", response_model=HealthResponse)
def health() -> HealthResponse:
    settings = get_settings()
    return HealthResponse(
        status="ok",
        model_ready=model_exists(),
        model_path=str(settings.model_path),
        model_version=settings.model_version,
    )


@app.post("/train/armamento", response_model=TrainResponse)
def train_armamento() -> TrainResponse:
    try:
        payload = train_armamento_model()
        return TrainResponse(**payload)
    except Exception as exc:
        raise HTTPException(status_code=500, detail=str(exc)) from exc


@app.get("/predictions/armamento", response_model=list[PredictionItem])
def predictions_armamento(limit: int = Query(default=100, ge=1, le=500)) -> list[PredictionItem]:
    try:
        return [PredictionItem(**item) for item in list_armamento_predictions(limit=limit)]
    except FileNotFoundError as exc:
        raise HTTPException(status_code=404, detail="No existe un modelo entrenado.") from exc
    except Exception as exc:
        raise HTTPException(status_code=500, detail=str(exc)) from exc


@app.post("/predict/armamento/persist", response_model=PersistResponse)
def predict_armamento_persist() -> PersistResponse:
    try:
        predictions = list_armamento_predictions(limit=500)
        return PersistResponse(
            message="Predicciones generadas correctamente. Persistencia aun no implementada en base de datos.",
            total=len(predictions),
        )
    except FileNotFoundError as exc:
        raise HTTPException(status_code=404, detail="No existe un modelo entrenado.") from exc
    except Exception as exc:
        raise HTTPException(status_code=500, detail=str(exc)) from exc


@app.get("/predictions/conflicto")
def predictions_conflicto(limit: int = Query(default=100, ge=1, le=500)) -> list[dict]:
    return []


@app.post("/train/conflicto")
def train_conflicto() -> dict[str, str]:
    return {"message": "Entrenamiento de conflicto aun no implementado."}


@app.post("/predict/conflicto/persist")
def predict_conflicto_persist() -> dict[str, str]:
    return {"message": "Predicciones de conflicto aun no implementadas."}
