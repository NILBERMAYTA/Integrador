# ML Service

Servicio Python separado para entrenamiento y prediccion de armamento.

## Instalacion

```powershell
cd ml_service
python -m venv .venv
.venv\Scripts\activate
pip install -r requirements.txt
Copy-Item .env.example .env
```

## Variables

Configura `DB_PASSWORD` en `ml_service/.env`.

## Entrenar

```powershell
cd ml_service
.venv\Scripts\activate
python -m app.train
```

Si falla por falta de clases, exporta el dataset y revisa la distribucion:

```powershell
cd ml_service
.venv\Scripts\activate
python -m app.dataset_tools
```

## Levantar API

```powershell
cd ml_service
.venv\Scripts\activate
uvicorn app.main:app --reload --host 127.0.0.1 --port 8002
```

## Endpoints

- `GET /health`
- `POST /train/armamento`
- `GET /predictions/armamento?limit=20`
- `POST /predict/armamento/persist`
