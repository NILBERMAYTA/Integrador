from __future__ import annotations

from fastapi import FastAPI, HTTPException, Query
from pydantic import BaseModel

from app.config import get_settings
from app.explainability import (
    global_armamento_explanation,
    individual_armamento_explanation,
)
from app.ml import (
    list_armamento_predictions,
    model_exists,
    summarize_armamento_predictions,
    train_armamento_model,
)


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
    unidad_nombre: str
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


class PredictionCounts(BaseModel):
    alto: int | None = None
    medio: int | None = None
    bajo: int | None = None
    operativo: int | None = None
    inoperativo: int | None = None


class PredictionSummaryResponse(BaseModel):
    unidad_id: int | None = None
    total: int
    riesgo: PredictionCounts
    estado: PredictionCounts
    page: int
    per_page: int
    last_page: int
    items: list[PredictionItem]


class FeatureImportanceItem(BaseModel):
    feature: str
    label: str
    importance: float
    positive_impact: float
    negative_impact: float


class GlobalExplanationResponse(BaseModel):
    unidad_id: int | None = None
    total_records: int
    sample_size: int
    base_value: float
    generated_at: str
    importance: list[FeatureImportanceItem]
    beeswarm_image: str
    dependence_image: str | None = None
    top_feature: str


class IndividualContributionItem(BaseModel):
    feature: str
    label: str
    feature_value: str
    shap_value: float
    absolute_impact: float
    direction: str


class IndividualExplanationResponse(BaseModel):
    serie_id: int
    codigo_serie: str
    unidad_id: int
    unidad_nombre: str
    probability: float
    predicted_state: str
    risk_level: str
    recommendation: str
    base_value: float
    contributions: list[IndividualContributionItem]
    waterfall_image: str
    generated_at: str


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
def predictions_armamento(
    limit: int = Query(default=100, ge=1, le=500),
    unidad_id: int | None = Query(default=None, ge=1),
) -> list[PredictionItem]:
    try:
        return [
            PredictionItem(**item)
            for item in list_armamento_predictions(limit=limit, unidad_id=unidad_id)
        ]
    except FileNotFoundError as exc:
        raise HTTPException(status_code=404, detail="No existe un modelo entrenado.") from exc
    except Exception as exc:
        raise HTTPException(status_code=500, detail=str(exc)) from exc


@app.get("/predictions/armamento/summary", response_model=PredictionSummaryResponse)
def predictions_armamento_summary(
    unidad_id: int | None = Query(default=None, ge=1),
    page: int = Query(default=1, ge=1),
    per_page: int = Query(default=10, ge=1, le=50),
) -> PredictionSummaryResponse:
    try:
        return PredictionSummaryResponse(
            **summarize_armamento_predictions(
                unidad_id=unidad_id,
                page=page,
                per_page=per_page,
            )
        )
    except FileNotFoundError as exc:
        raise HTTPException(status_code=404, detail="No existe un modelo entrenado.") from exc
    except Exception as exc:
        raise HTTPException(status_code=500, detail=str(exc)) from exc


@app.get(
    "/explainability/armamento/global",
    response_model=GlobalExplanationResponse,
)
def explainability_armamento_global(
    unidad_id: int | None = Query(default=None, ge=1),
    sample_size: int = Query(default=500, ge=50, le=1000),
) -> GlobalExplanationResponse:
    try:
        return GlobalExplanationResponse(
            **global_armamento_explanation(
                unidad_id=unidad_id,
                sample_size=sample_size,
            )
        )
    except FileNotFoundError as exc:
        raise HTTPException(status_code=404, detail="No existe un modelo entrenado.") from exc
    except Exception as exc:
        raise HTTPException(status_code=500, detail=str(exc)) from exc


@app.get(
    "/explainability/armamento/{serie_id}",
    response_model=IndividualExplanationResponse,
)
def explainability_armamento_individual(
    serie_id: int,
) -> IndividualExplanationResponse:
    try:
        return IndividualExplanationResponse(
            **individual_armamento_explanation(serie_id)
        )
    except RuntimeError as exc:
        raise HTTPException(status_code=404, detail=str(exc)) from exc
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
