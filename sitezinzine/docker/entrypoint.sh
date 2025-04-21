#!/bin/bash

set -e

# ✅ Log coloré (facultatif mais sympa)
log() {
  echo -e "\033[1;32m$1\033[0m"
}


log "⏳ Attente de la base de données..."
until php bin/console doctrine:query:sql "SELECT 1" > /dev/null 2>&1; do
  sleep 1
done

log "📦 Lancement des migrations..."
php bin/console doctrine:migrations:migrate --no-interaction || {
  echo "❌ Erreur lors des migrations"
  exit 1
}

log "🧹 Nettoyage du cache..."
if ! php bin/console cache:clear --env=${APP_ENV}; then
  echo "⚠️  Erreur lors du cache:clear, tentative de correction des permissions..."
  chown -R www-data:www-data var
  php bin/console cache:clear --env=${APP_ENV} || echo "❌ Échec définitif du cache:clear"
fi

log "🔥 Warmup du cache..."
php bin/console cache:warmup --env=${APP_ENV} || echo "⚠️  Erreur lors du warmup"

log "🚀 Lancement d'Apache..."
exec apache2-foreground
