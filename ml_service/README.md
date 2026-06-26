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
python -m app.ml
```

El entrenamiento consulta todas las series activas de PostgreSQL. Se reserva
temporalmente un 20% para calcular métricas y, después, el modelo definitivo
se vuelve a ajustar con el 100% del dataset antes de guardarse.

El parámetro `limit` de los endpoints de predicción solo controla cuántos
resultados se muestran; no limita los registros usados para entrenar.

`estado_actual` y `condicion_actual` se usan para construir la etiqueta
histórica, pero no se entregan al clasificador como variables de entrada. Esto
evita que el modelo conozca directamente la respuesta que debe predecir.

`unidad_id` se conserva para filtrar y segmentar resultados, pero se excluye
del entrenamiento. La pertenencia administrativa a una unidad no debe
considerarse una causa directa de inoperatividad.

Los niveles de riesgo se presentan como una clasificación operativa híbrida:

- Alto: el modelo supera su umbral de decisión o la serie ya está en condición
  crítica.
- Medio: la serie está observada, en mantenimiento, con defectos o en mal
  estado y requiere seguimiento preventivo.
- Bajo: no presenta señales críticas ni preventivas conocidas.

El campo `estado_predicho` permanece como la salida binaria pura del modelo.

Si falla por falta de clases, exporta el dataset y revisa la distribucion:

```powershell
cd ml_service
.venv\Scripts\activate
python -m app.data
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
- `GET /predictions/armamento/summary`
- `GET /explainability/armamento/global?sample_size=500`
- `GET /explainability/armamento/{serie_id}`
- `POST /predict/armamento/persist`

## Explicabilidad SHAP

El servicio utiliza `TreeExplainer` sobre el `RandomForestClassifier` después
de transformar las variables con el preprocesador del pipeline.

- El análisis global usa una muestra estratificada y cacheada para evitar
  recalcular miles de explicaciones en cada carga.
- La importancia global agrupa las columnas generadas por `OneHotEncoder` en
  sus variables de negocio originales.
- El beeswarm muestra la distribución y dirección de los impactos.
- El gráfico de dependencia muestra el comportamiento de la variable numérica
  más influyente.
- La explicación individual genera un waterfall y una tabla de contribuciones
  positivas y negativas.
