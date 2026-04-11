from __future__ import annotations

from datetime import datetime, timezone
from pathlib import Path
from typing import Any

import joblib

from app.settings import get_settings


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
