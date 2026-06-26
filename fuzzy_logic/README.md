# Fuzzy Logic

Motor de logica difusa para prediccion de condicion del armamento.

## Entorno

```bash
python -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt -r ../ml_service/requirements.txt
```

`composer run dev:ml` usa este entorno para levantar la API FastAPI ubicada en
`ml_service`.

## Modulo principal

- `armamento.py`: funciones de pertenencia, reglas difusas, calibracion,
  predicciones de estado y explicabilidad.
