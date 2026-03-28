#!/usr/bin/env bash
# =============================================================================
# Ejecutar EN EL SERVIDOR Hostinger, ya conectado por SSH (no guardes contraseñas aquí).
#
#   ssh -p 65002 u555747547@195.35.38.97
#   bash <(curl -s URL_RAW)    # o sube este .sh y: bash hostinger-sivso-io-deploy.sh
#
# Ajusta REPO y RAMA si hace falta.
# =============================================================================
set -euo pipefail

REPO="${REPO:-https://github.com/Elias8291/sivso_copia.git}"
BRANCH="${BRANCH:-main}"
# Ruta típica Hostinger (usuario + dominio)
BASE="${BASE:-$HOME/domains/sivso.io}"
WEBROOT="${WEBROOT:-$BASE/public_html}"

echo "==> WEBROOT = $WEBROOT"
mkdir -p "$BASE"
cd "$BASE"

if [[ ! -d "$WEBROOT/.git" ]]; then
  echo "==> Clonando repositorio en $WEBROOT ..."
  if [[ -d "$WEBROOT" && -n "$(ls -A "$WEBROOT" 2>/dev/null || true)" ]]; then
    echo "ERROR: $WEBROOT no está vacío. Haz backup y vacía la carpeta, o borra solo su contenido."
    exit 1
  fi
  git clone -b "$BRANCH" --depth 1 "$REPO" "$WEBROOT"
else
  echo "==> git pull en $WEBROOT"
  cd "$WEBROOT"
  git fetch origin "$BRANCH"
  git reset --hard "origin/$BRANCH"
fi

cd "$WEBROOT"

echo "==> Composer"
if command -v composer >/dev/null 2>&1; then
  composer install --no-dev --optimize-autoloader --no-interaction
else
  echo "AVISO: composer no está en PATH. En Hostinger suele ser: php ~/composer.phar o el binario del panel."
  exit 1
fi

if [[ ! -f .env ]]; then
  echo "==> Creando .env desde .env.example (edítalo con BD y APP_URL)"
  cp .env.example .env
  php artisan key:generate --force
  echo "IMPORTANTE: edita $WEBROOT/.env (DB_*, APP_URL=https://sivso.io)"
else
  echo "==> .env ya existe, no se sobrescribe"
fi

echo "==> Artisan"
php artisan migrate --force || true
php artisan storage:link || true
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# .htaccess en raíz del proyecto: reescribe hacia public/ si el dominio apunta a public_html
if [[ -f "$WEBROOT/deploy/hostinger-public_html-root/.htaccess" ]]; then
  cp "$WEBROOT/deploy/hostinger-public_html-root/.htaccess" "$WEBROOT/.htaccess"
  echo "==> Copiado deploy/hostinger-public_html-root/.htaccess → $WEBROOT/.htaccess"
else
  echo "AVISO: Falta deploy/hostinger-public_html-root/.htaccess en el repo clonado."
fi

echo ""
echo "==> Node / Vite (opcional en servidor)"
if command -v npm >/dev/null 2>&1; then
  npm ci
  npm run build
else
  echo "Sin npm aquí: en tu PC ejecuta 'npm ci && npm run build' y sube la carpeta public/build a $WEBROOT/public/build"
fi

echo ""
echo "Listo. Comprueba en hPanel que la raíz del sitio sea:"
echo "  - Opción A (mejor): $WEBROOT/public"
echo "  - Opción B: $WEBROOT (con el .htaccess que reescribe a public/)"
echo ""
echo "APP_URL en .env debe ser: https://sivso.io"
