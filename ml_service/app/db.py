from __future__ import annotations

import os
from functools import lru_cache

from dotenv import load_dotenv
from sqlalchemy import create_engine
from sqlalchemy.engine import Engine

from app.settings import BASE_DIR


load_dotenv(BASE_DIR / ".env")


@lru_cache(maxsize=1)
def get_engine() -> Engine:
    host = os.getenv("DB_HOST", "127.0.0.1")
    port = os.getenv("DB_PORT", "5432")
    name = os.getenv("DB_NAME", "armutop")
    user = os.getenv("DB_USER", "postgres")
    password = os.getenv("DB_PASSWORD", "")

    database_url = f"postgresql+psycopg2://{user}:{password}@{host}:{port}/{name}"

    return create_engine(database_url, future=True)
