from ultralytics import YOLO

# Cargar tu modelo YOLO
# Puedes usar modelos pre-entrenados o tu modelo personalizado
modelo = YOLO('best.pt')  # Cambia esto por la ruta de tu modelo

# Método 1: Obtener los nombres de las clases
print("=== CLASES DEL MODELO ===\n")
nombres_clases = modelo.names
print(f"Total de clases: {len(nombres_clases)}\n")

# Mostrar todas las clases con su índice
for indice, nombre in nombres_clases.items():
    print(f"Clase {indice}: {nombre}")

print("\n" + "="*50 + "\n")

# Método 2: Información adicional del modelo
print("=== INFORMACIÓN DEL MODELO ===\n")
print(f"Tipo de tarea: {modelo.task}")
print(f"Número de clases: {len(modelo.names)}")

# Si quieres guardar las clases en un archivo de texto
with open('clases_yolo.txt', 'w', encoding='utf-8') as f:
    f.write("Clases del modelo YOLO\n")
    f.write("="*50 + "\n\n")
    for indice, nombre in nombres_clases.items():
        f.write(f"Clase {indice}: {nombre}\n")

print("\nLas clases también se han guardado en 'clases_yolo.txt'")