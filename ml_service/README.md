# Fuzzy Logic Service

Servicio Python separado para prediccion de armamento.

- Estado/condicion del armamento: logica difusa.
- Reposicion esperada: Random Forest.

## Instalacion

```bash
cd fuzzy_logic
python -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt -r ../ml_service/requirements.txt
```

En Windows, activa el entorno con `.venv\Scripts\activate`.

## Variables

Configura `DB_PASSWORD` en `ml_service/.env` o en `.env` del proyecto Laravel.

Por defecto la configuracion calibrada se guarda en:

```text
ml_service/models/logica_difusa_armamento.json
ml_service/models/random_forest_reposicion.joblib
```

Puedes cambiarla con `MODEL_PATH`.

## Calibrar y entrenar

```bash
source fuzzy_logic/.venv/bin/activate
cd ml_service
python -m app.ml
```

La calibracion consulta las series activas de PostgreSQL y calcula umbrales
operativos desde percentiles de uso, incidencias, mantenimiento e inspecciones
para el motor difuso. En el mismo proceso se entrena un Random Forest separado
para estimar la probabilidad de reposicion.

## Levantar API

```bash
composer run dev:ml
```

o manualmente:

```bash
source fuzzy_logic/.venv/bin/activate
cd ml_service
uvicorn app.main:app --reload --host 127.0.0.1 --port 8002
```

## Endpoints

- `GET /health`
- `POST /train/armamento`
- `GET /predictions/armamento?limit=20`
- `GET /predictions/armamento/summary`
- `GET /explainability/armamento/global?sample_size=500`
- `GET /explainability/armamento/{serie_id}`
- `GET /recommendations/replacement`

El endpoint `POST /train/armamento` conserva su nombre para mantener
compatibilidad con Laravel, pero ahora calibra reglas difusas.

## Logica Difusa: Estado Del Armamento

El motor usa funciones de pertenencia para:

- Uso operativo reciente.
- Incidencias recientes.
- Brecha de mantenimiento.
- Antiguedad de inspeccion.
- Resultado de ultima inspeccion.
- Cobertura historica.

Las reglas combinan esas senales para estimar condicion actual, condicion
futura y riesgo. Las series sin historial quedan como `indeterminada` para
prediccion futura.

## Random Forest: Reposicion

El endpoint `GET /recommendations/replacement` usa
`models/random_forest_reposicion.joblib`. El modelo aprende de transiciones
historicas entre inspecciones consecutivas y estima la probabilidad de que una
serie requiera reposicion dentro del horizonte calculado.

## Explicabilidad

Los endpoints de explicabilidad de predicciones devuelven aportes de reglas
difusas con el mismo contrato que consumia la pantalla anterior. Ya no usan
SHAP ni `TreeExplainer`.
