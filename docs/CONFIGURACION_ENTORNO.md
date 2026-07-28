# Configuración del entorno

El archivo `.env` local no se versiona porque contiene la clave de la aplicación y las credenciales de la base de datos. Para crear un entorno nuevo, copia el ejemplo correspondiente:

## Desarrollo local

### Linux / macOS

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve
```

### Windows PowerShell

```powershell
Copy-Item .env.example .env
composer install
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve
```

Antes de ejecutar las migraciones, configura en `.env` estos valores para PostgreSQL:

```dotenv
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=farmacia
DB_USERNAME=farmacia
DB_PASSWORD=tu_clave
```

## Producción

Usa `.env.production.example` como plantilla, cambia especialmente `APP_KEY`, `APP_URL`, `DB_PASSWORD` y deja `APP_DEBUG=false`:

```bash
cp .env.production.example .env
php artisan key:generate
```

No copies el `.env` real al repositorio ni lo compartas públicamente. Si una clave se expone accidentalmente, debe reemplazarse.

## Dependencias necesarias

- PHP 8.2 o superior con `pgsql`, `mbstring`, `xml`, `curl`, `zip`, `bcmath` y `gd`.
- Composer 2.
- PostgreSQL 14 o superior.
- Navegador web moderno.

Bootstrap y el logo se encuentran dentro de `public/`, por lo que no se requiere Node.js para ejecutar la aplicación.
