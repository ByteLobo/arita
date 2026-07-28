# Evidencias reproducibles de auditoría

Fecha: 2026-07-26. Todas las comprobaciones de este archivo son de lectura o ejecución sobre la base de pruebas configurada. No se incluyen valores del `.env`, contraseñas, tokens ni cookies.

## Reproducir el inventario

Desde la raíz del repositorio:

```bash
rg --files -g '!vendor' -g '!node_modules'
git status --short --branch
find .. -name AGENTS.md -print
```

Resultado observado: Laravel/Composer con `app`, `database`, `routes`, `resources`, `tests`, `vendor`; no se encontró `AGENTS.md`; el repositorio no tenía commits y estaba sin seguimiento al inicio.

## Reproducir rutas y permisos

```bash
php artisan route:list --except-vendor
rg -n "middleware\('rol|Route::resource|login.store|logout" routes app tests
```

Hechos verificables:

- `routes/web.php:20-32` exige `auth` y `activo` para operaciones autenticadas.
- `routes/web.php:34-45` protege catálogos y compras con `rol:Administrador`.
- `routes/web.php:27-32,47-49` permite ventas/consulta operacional a ambos roles.
- No hay ruta `users`, `usuarios`, `UserController` ni equivalente.
- `app/Http/Requests/*.php` repite autorización por rol en servidor.

## Reproducir autenticación y sesión

```bash
nl -ba app/Http/Controllers/Auth/LoginController.php
nl -ba app/Http/Middleware/EnsureUsuarioActivo.php
nl -ba app/Providers/AppServiceProvider.php
nl -ba config/session.php
nl -ba config/auth.php
```

Evidencia: login valida email/password, restringe `activo`, regenera sesión en éxito; logout hace `invalidate()` y `regenerateToken()`; el limiter `login` es 5 por minuto por email/IP. La sesión usa el driver indicado por entorno y lifetime por entorno. La expiración real no pudo probarse con navegador/servidor.

## Reproducir compras, ventas y stock

```bash
nl -ba app/Services/CompraService.php
nl -ba app/Services/VentaService.php
nl -ba app/Http/Requests/CompraRequest.php
nl -ba app/Http/Requests/VentaRequest.php
rg -n "DB::transaction|lockForUpdate|decrement|precio|total|venc" app/Services app/Http/Requests
```

Evidencia:

- Compra: transacción en `CompraService.php:19-84`, bloqueos en proveedor y medicamento, cabecera/detalle, incremento de stock y total.
- Venta: transacción en `VentaService.php:18-75`, bloqueo de medicamento en línea 45, validación de vencimiento y stock en líneas 52-56, decremento en línea 68.
- El ordenamiento por medicamento (`sortBy`) reduce riesgo de deadlock entre transacciones que comparten varios productos.
- Los cálculos usan `round((float) ...)` en `CompraService.php:32,49,67,78,81` y `VentaService.php:59-60,69,72`; es la evidencia del riesgo monetario.

## Reproducir el esquema

```bash
nl -ba database/migrations/2026_07_20_000000_create_farmacia_schema.php
nl -ba database/migrations/2026_07_24_000001_add_unique_nit_to_proveedores.php
rg -n "foreignId|constrained|unique|CREATE UNIQUE INDEX|CHECK|index\(" database/migrations
```

Evidencia: las nueve tablas se crean en líneas 12-100; FKs usan `restrictOnDelete`; checks de stock/precios/cantidades/subtotales están en líneas 115-127. La búsqueda no muestra declaraciones de índices hijos mediante `index()`; las únicas declaraciones explícitas son índices únicos y claves compuestas. La confirmación final debe hacerse en PostgreSQL con `pg_constraint`/`pg_indexes`.

## Reproducir validación y salida HTML

```bash
rg -n "FormRequest|authorize\(|rules\(|whereRaw|where\(|{{|@csrf|old\(" app resources routes
```

Evidencia: FormRequests validan tipos, cantidades, precios, existencia, categorías/proveedores activos y duplicados. Las consultas usan bindings o builder. Las vistas interpolan datos con `{{ }}` y los formularios incluyen `@csrf`. No hay pruebas de payload XSS o auditoría automatizada de headers.

## Comandos ejecutados

```bash
composer validate --no-check-publish
find app database routes config bootstrap tests -name '*.php' -print0 | xargs -0 -n1 php -l
vendor/bin/pint --test
php artisan route:list --except-vendor
php artisan about --only=environment --no-ansi
php artisan test --without-tty
composer audit --format=plain
vendor/bin/phpstan analyse --no-progress
npm run build
php artisan db:show --database=pgsql --counts --no-ansi
```

Resultados:

- `composer validate`: correcto.
- `php -l`: todos los archivos PHP revisados sin errores de sintaxis.
- Pint: correcto.
- Rutas: correcto, 34 rutas y sin rutas de usuarios.
- `about`: entorno local, debug habilitado, Laravel 12.64.0, PHP 8.4.23, zona `America/La_Paz`.
- Tests: 2 pasaron y 36 fallaron al conectar con `farmacia_test` en `127.0.0.1:5432`; no llegaron a ejecutar migraciones/asserts funcionales.
- `composer audit`: no verificable por DNS/red hacia Packagist; no se afirma ausencia de vulnerabilidades.
- PHPStan: no ejecutado porque `vendor/bin/phpstan` no existe.
- Build: no ejecutado porque no existe `package.json`.
- `db:show`: no verificable; PostgreSQL rechazó la conexión.

## Procedimiento seguro para completar la verificación PostgreSQL

Usar una instancia autorizada de desarrollo o una base efímera, nunca la base productiva:

```bash
php artisan migrate:status
php artisan test --without-tty
psql -X -v ON_ERROR_STOP=1 -c "SELECT current_database(), current_user;"
psql -X -v ON_ERROR_STOP=1 -c "SELECT conrelid::regclass, conname, pg_get_constraintdef(oid) FROM pg_constraint WHERE contype='f';"
psql -X -v ON_ERROR_STOP=1 -c "SELECT schemaname, tablename, indexname, indexdef FROM pg_indexes WHERE schemaname='public' ORDER BY tablename,indexname;"
```

La última consulta debe comprobar índices sobre cada FK hija. Para probar rollback, stock cero, vencimiento y totales se deben activar las pruebas existentes de `tests/Feature/Admin/CompraTest.php`, `tests/Feature/VentaTest.php` y `tests/Feature/Database/EsquemaFarmaciaTest.php`. Para concurrencia falta crear la prueba indicada en el informe principal.

## Límites de evidencia

- No se accedió a valores del `.env`; solo se listaron nombres de variables definidos.
- No hay configuración Nginx/Apache/container en el repositorio; la protección de archivos, HTTPS, headers, logs y permisos no puede declararse.
- No hay frontend TypeScript ni paquete npm; las comprobaciones de `strict`, `any` y build TS no aplican al estado actual.
- La ausencia de una ruta/controlador/archivo se verificó mediante inventario y búsquedas `rg`, pero debe mantenerse como criterio de aceptación al implementar el módulo de usuarios.
