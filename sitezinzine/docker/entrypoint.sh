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

#echo "📦 Lancement des migrations..."
#php bin/console doctrine:migrations:migrate --no-interaction

echo "🧹 Nettoyage du cache..."
php bin/console cache:clear

echo "🔥 Warmup du cache..."
php bin/console cache:warmup

echo "🚀 Lancement d'Apache..."
exec apache2-foreground
