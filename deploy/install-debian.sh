#!/usr/bin/env bash
set -Eeuo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

if [[ "${EUID}" -eq 0 ]]; then
    SUDO=""
else
    SUDO="sudo"
fi

echo "Instalando dependencias del sistema..."
${SUDO} apt-get update
${SUDO} apt-get install -y \
    git unzip postgresql postgresql-contrib \
    php-cli php-pgsql php-mbstring php-xml php-curl php-zip php-bcmath php-gd

if ! command -v composer >/dev/null 2>&1; then
    echo "Composer no está instalado. Instálalo desde https://getcomposer.org/download/ y vuelve a ejecutar este script."
    exit 1
fi

cd "${APP_DIR}"

if [[ ! -f .env ]]; then
    cp .env.example .env
    php artisan key:generate
    echo "Se creó .env. Configura PostgreSQL y vuelve a ejecutar este script."
    exit 0
fi

composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan storage:link || true
php artisan optimize

${SUDO} chown -R www-data:www-data storage bootstrap/cache
${SUDO} chmod -R ug+rwX storage bootstrap/cache

echo
echo "Instalación preparada. Ejecuta los siguientes comandos para activar el servicio:"
echo "  sudo cp deploy/farmacia.service.example /etc/systemd/system/farmacia.service"
echo "  sudo systemctl daemon-reload"
echo "  sudo systemctl enable --now farmacia"
echo "  sudo systemctl status farmacia"
echo
echo "La aplicación quedará disponible en http://IP_DEL_EQUIPO:8000"
