from __future__ import annotations

from pathlib import Path

from app.features import load_armamento_dataset


def export_dataset_csv(output_path: str = "data/dataset_armamento.csv") -> Path:
    df = load_armamento_dataset()
    path = Path(output_path)
    path.parent.mkdir(parents=True, exist_ok=True)
    df.to_csv(path, index=False)

    return path


def dataset_summary() -> dict[str, object]:
    df = load_armamento_dataset()

    return {
        "total_registros": int(len(df)),
        "clases_resultado": {str(k): int(v) for k, v in df["resultado"].value_counts(dropna=False).to_dict().items()},
        "condicion_actual": {str(k): int(v) for k, v in df["condicion_actual"].value_counts(dropna=False).to_dict().items()},
        "estado_actual": {str(k): int(v) for k, v in df["estado_actual"].value_counts(dropna=False).to_dict().items()},
    }


if __name__ == "__main__":
    path = export_dataset_csv()
    print(f"Dataset exportado en: {path}")
    print(dataset_summary())
