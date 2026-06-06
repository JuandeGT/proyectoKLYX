#!/bin/bash
set -e

# ──────────────────────────────────────────────────────────────
# docker-entrypoint.sh
# Se ejecuta cada vez que el contenedor arranca en Render.
# En este punto las variables de entorno del dashboard ya están
# inyectadas, por lo que config:cache captura los valores reales.
# ──────────────────────────────────────────────────────────────

# 1. Crear .env desde el ejemplo para que artisan no falle si
#    busca el archivo. Las variables reales vienen de los env vars
#    del proceso (inyectadas por Render), que tienen prioridad.
echo "==> Preparando .env..."
cp .env.example .env

# 2. Cachear configuracion, rutas y vistas con los valores reales
#    de Render (DB_HOST, APP_KEY, FRONTEND_URL, etc.)
echo "==> Optimizando Laravel (config + routes + views)..."
php artisan optimize

# 3. Arrancar Apache en primer plano (obligatorio para Docker)
echo "==> Iniciando Apache..."
exec apache2-foreground
