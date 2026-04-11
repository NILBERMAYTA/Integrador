from __future__ import annotations

from pydantic import BaseModel


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
