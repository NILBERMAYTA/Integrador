from __future__ import annotations

import os
from functools import lru_cache
from pathlib import Path

from dotenv import load_dotenv


BASE_DIR = Path(__file__).resolve().parent.parent
load_dotenv(BASE_DIR / ".env")


class Settings:
    def __init__(self) -> None:
        self.base_dir = BASE_DIR
        self.model_path = BASE_DIR / os.getenv("MODEL_PATH", "models/modelo_armamento.pkl")
        self.model_version = os.getenv("MODEL_VERSION", "v1")
        self.lookback_days = int(os.getenv("PREDICTION_LOOKBACK_DAYS", "90"))
        self.maintenance_lookback_days = int(os.getenv("PREDICTION_MAINTENANCE_LOOKBACK_DAYS", "180"))


@lru_cache(maxsize=1)
def get_settings() -> Settings:
    return Settings()
