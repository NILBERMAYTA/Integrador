from __future__ import annotations

from fastapi import FastAPI, HTTPException, Query

from app.model_registry import model_exists
from app.predict import list_armamento_predictions
from app.schemas import HealthResponse, PersistResponse, PredictionItem, TrainResponse
from app.settings import get_settings
from app.train import train_armamento_model


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
