# Instalación en Debian sin Nginx

Esta modalidad usa el servidor integrado de PHP administrado por `systemd`. Está pensada para una farmacia pequeña, una computadora de caja o una red local universitaria.

No requiere Nginx, Apache ni Docker.

## Requisitos

- Debian con `systemd`.
- PHP 8.2 o superior.
- PostgreSQL.
- Composer.
- Acceso de administrador mediante `sudo`.

## Instalación

Desde la raíz del proyecto:

```bash
cp .env.production.example .env
php artisan key:generate
```

Configura en `.env` la contraseña real de PostgreSQL. Luego ejecuta:

```bash
bash deploy/install-debian.sh
```

Si el instalador detecta que `.env` no existe, lo crea y termina para que puedas configurar la base de datos antes de continuar.

## PostgreSQL

```bash
sudo -u postgres psql
```

```sql
CREATE USER farmacia WITH PASSWORD 'cambia-esta-clave';
CREATE DATABASE farmacia OWNER farmacia;
\q
```

Después de configurar `.env`:

```bash
php artisan migrate --force
php artisan db:seed --force
```

## Activar la aplicación

Ajusta `WorkingDirectory` y las rutas si el proyecto no está en `/var/www/farmacia`:

```bash
sudo cp deploy/farmacia.service.example /etc/systemd/system/farmacia.service
sudo systemctl daemon-reload
sudo systemctl enable --now farmacia
sudo systemctl status farmacia
```

La aplicación estará disponible en:

```text
http://IP_DEL_EQUIPO:8000
```

Para ver logs:

```bash
sudo journalctl -u farmacia -f
```

## Actualizar

```bash
bash deploy/update-production.sh
```

Antes de una actualización importante, realiza un respaldo:

```bash
pg_dump -h 127.0.0.1 -U farmacia -d farmacia > farmacia-respaldo.sql
```

## Alcance de esta modalidad

Es adecuada para pocos usuarios y una red local. El servidor integrado de PHP no es la opción ideal para una aplicación pública de alto tráfico. Si la farmacia se publica directamente en Internet, debe colocarse delante un servidor web con HTTPS, como Apache, Caddy o Nginx.
