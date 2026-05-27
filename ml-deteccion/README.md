# ML Deteccion

Servicio Python separado para deteccion de articulos con YOLO.

## Instalacion

```powershell
cd ml-deteccion
python -m venv .venv
.venv\Scripts\activate
pip install -r requirements.txt
```

## Variables

Laravel usa estas variables para conectar con el servicio:

```env
DETECCION_API_URL=http://127.0.0.1:8001
DETECCION_API_TIMEOUT=30
```

Opcionalmente puedes cambiar la ruta del modelo:

```env
YOLO_MODEL_PATH=C:\Proyecto\Armutop\armutop\ml-deteccion\yolo_service\best.pt
```

Si no se configura `YOLO_MODEL_PATH`, el servicio usa `yolo_service/best.pt`.

## Levantar API

```powershell
cd ml-deteccion
.venv\Scripts\activate
uvicorn yolo_service.main:app --reload --host 127.0.0.1 --port 8001
```

## Endpoints

- `GET /health`
- `POST /detect`
