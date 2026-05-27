from fastapi import FastAPI, File, HTTPException, UploadFile
from fastapi.responses import JSONResponse
from io import BytesIO
from pathlib import Path
from PIL import Image
import os
import time

from ultralytics import YOLO


BASE_DIR = Path(__file__).resolve().parent
MODEL_PATH = Path(os.getenv("YOLO_MODEL_PATH", BASE_DIR / "best.pt")).resolve()

app = FastAPI(
    title="Armutop ML Deteccion",
    version="1.0.0",
    description="API de deteccion de articulos con YOLO.",
)

print(f"Cargando modelo YOLO desde {MODEL_PATH}...")
if not MODEL_PATH.exists():
    raise RuntimeError(f"No existe el modelo YOLO en {MODEL_PATH}")

model = YOLO(str(MODEL_PATH))
print("Modelo cargado exitosamente.")


@app.get("/health")
def health() -> dict[str, object]:
    return {
        "status": "ok",
        "model_ready": True,
        "model_path": str(MODEL_PATH),
    }


@app.post("/detect")
async def detect(file: UploadFile = File(...)):
    start_time = time.time()

    print("\n==============================")
    print("Solicitud recibida")
    print(f"Nombre archivo: {file.filename}")
    print(f"Tipo MIME: {file.content_type}")

    image_bytes = await file.read()
    print(f"Bytes recibidos: {len(image_bytes)}")

    try:
        image = Image.open(BytesIO(image_bytes)).convert("RGB")
        print(f"Resolucion imagen: {image.width}x{image.height}")
    except Exception as exc:
        print("Error al leer imagen:", exc)
        raise HTTPException(status_code=400, detail="No se pudo leer la imagen") from exc

    print("Procesando deteccion...")
    results = model.predict(
        image,
        imgsz=640,
        conf=0.4,
        iou=0.5,
        verbose=False,
    )

    det = results[0]
    boxes = det.boxes
    names = model.model.names if hasattr(model, "model") else model.names

    print(f"Numero de objetos detectados: {len(boxes)}")

    counts = {}
    detections_debug = []

    for box in boxes:
        cls_id = int(box.cls[0].item())
        conf = float(box.conf[0].item())
        xyxy = box.xyxy[0].tolist()
        label = names[cls_id]

        counts[label] = counts.get(label, 0) + 1

        detections_debug.append({
            "label": label,
            "confidence": round(conf, 3),
            "box": [round(v, 2) for v in xyxy],
        })

    elapsed = round(time.time() - start_time, 3)

    print("Resumen detecciones:", counts)
    print(f"Tiempo total procesamiento: {elapsed}s")
    print("==============================\n")

    return JSONResponse(content={
        "summary": counts,
        "detections": detections_debug,
        "processing_time": elapsed,
    })
