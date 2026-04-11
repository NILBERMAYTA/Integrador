from __future__ import annotations

import os
from functools import lru_cache
from pathlib import Path

from dotenv import load_dotenv
from sqlalchemy import create_engine
from sqlalchemy.engine import Engine


BASE_DIR = Path(__file__).resolve().parent.parent
load_dotenv(BASE_DIR / ".env")


class Settings:
    def __init__(self) -> None:
        self.base_dir = BASE_DIR
        self.model_path = BASE_DIR / os.getenv("MODEL_PATH", "models/modelo_armamento.pkl")
        self.model_version = os.getenv("MODEL_VERSION", "v1")
        self.lookback_days = int(os.getenv("PREDICTION_LOOKBACK_DAYS", "90"))
        self.maintenance_lookback_days = int(os.getenv("PREDICTION_MAINTENANCE_LOOKBACK_DAYS", "180"))
        self.db_host = os.getenv("DB_HOST", "127.0.0.1")
        self.db_port = os.getenv("DB_PORT", "5432")
        self.db_name = os.getenv("DB_NAME", "armutop")
        self.db_user = os.getenv("DB_USER", "postgres")
        self.db_password = os.getenv("DB_PASSWORD", "")

    @property
    def database_url(self) -> str:
        return (
            f"postgresql+psycopg2://{self.db_user}:{self.db_password}"
            f"@{self.db_host}:{self.db_port}/{self.db_name}"
        )


@lru_cache(maxsize=1)
def get_settings() -> Settings:
    return Settings()


@lru_cache(maxsize=1)
def get_engine() -> Engine:
    return create_engine(get_settings().database_url, future=True)
