from fastapi import FastAPI, File, UploadFile
from fastapi.responses import JSONResponse
from io import BytesIO
from PIL import Image
import numpy as np
import base64
import time

from ultralytics import YOLO

app = FastAPI()

# Carga el modelo una vez
print("Cargando modelo YOLO...")
model = YOLO("best.pt")
print("Modelo cargado exitosamente.")


@app.post("/detect")
async def detect(file: UploadFile = File(...)):

    start_time = time.time()

    # Mostrar información del archivo recibido
    print("\n==============================")
    print("Solicitud recibida")
    print(f"Nombre archivo: {file.filename}")
    print(f"Tipo MIME: {file.content_type}")

    # Leer bytes
    image_bytes = await file.read()
    print(f"Bytes recibidos: {len(image_bytes)}")

    # Convertir imagen
    try:
        image = Image.open(BytesIO(image_bytes)).convert("RGB")
        print(f"Resolución imagen: {image.width}x{image.height}")
    except Exception as e:
        print("Error al leer imagen:", e)
        return JSONResponse({"error": "No se pudo leer la imagen"}, status_code=400)

    # Inferencia YOLO
    print("Procesando detección...")
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

    # Debug detecciones
    print(f"Número de objetos detectados: {len(boxes)}")

    # Agrupamos detecciones
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

    # Respuesta detallada (útil para debug)
    return JSONResponse(content={
        "summary": counts,
        "detections": detections_debug,
        "processing_time": elapsed
    })
