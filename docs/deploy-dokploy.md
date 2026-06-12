# Despliegue en Dokploy

Esta guia asume que ya tienes un proyecto y ambiente en Dokploy, como `Armutop / production`.

## 1. Sube el codigo a GitHub

Dokploy necesita leer el proyecto desde un repositorio Git.

```bash
git status
git add Dockerfile .dockerignore docker/start.sh .env.production.example docs/deploy-dokploy.md
git commit -m "Prepare Dokploy deployment"
git push
```

## 2. Crea PostgreSQL en Dokploy

En la pantalla del proyecto:

1. Haz clic en `Create Service`.
2. Elige `Database`.
3. Elige `PostgreSQL`.
4. Nombre recomendado: `armutop-postgres`.
5. Crea la base y copia los datos de conexion internos: host, puerto, database, username y password.

## 3. Crea la aplicacion Laravel

1. Vuelve al proyecto `Armutop / production`.
2. Haz clic en `Create Service`.
3. Elige `Application`.
4. Conecta tu repositorio de GitHub.
5. Selecciona despliegue con `Dockerfile`.
6. Puerto de la aplicacion: `80`.

## 4. Variables de entorno

En la aplicacion Laravel, abre `Environment` y pega variables basadas en `.env.production.example`.

Genera una llave de Laravel en tu maquina:

```bash
php artisan key:generate --show
```

Usa ese valor en `APP_KEY`.

Ejemplo minimo:

```env
APP_NAME=ARMUTOP
APP_ENV=production
APP_KEY=base64:PEGA_AQUI_TU_LLAVE
APP_DEBUG=false
APP_URL=https://tu-dominio.com

DB_CONNECTION=pgsql
DB_HOST=HOST_INTERNO_DE_DOKPLOY
DB_PORT=5432
DB_DATABASE=BASE_DE_DATOS
DB_USERNAME=USUARIO
DB_PASSWORD=PASSWORD

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=public

RUN_MIGRATIONS=true
RUN_SEEDERS=false
PORT=80
```

Para una demo con datos de prueba, cambia temporalmente:

```env
RUN_SEEDERS=true
```

Despues del primer despliegue, vuelve a poner `RUN_SEEDERS=false`.

## 5. Dominio

1. En tu proveedor DNS crea un registro `A` apuntando a la IP del servidor.
2. En Dokploy, entra al servicio Laravel.
3. Agrega el dominio.
4. Asegurate de que el puerto del dominio sea `80`.

## 6. Despliega

Haz clic en `Deploy`.

Si falla, revisa `Deployments` o `Logs`. Los errores mas comunes son:

- `APP_KEY` vacio.
- `DB_HOST` incorrecto.
- La base PostgreSQL no esta creada.
- El dominio apunta a otro servidor.
- El puerto del dominio no es `8000`.

## 7. Credenciales demo

Si activaste `RUN_SEEDERS=true`, puedes entrar con:

- `admin@armutop.local` / `admin123`
- `utop-ea@armutop.local` / `adminunidad123`
- `furriel.utop-ea@armutop.local` / `furriel123`
