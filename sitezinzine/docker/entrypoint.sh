#!/bin/bash

set -e

echo "⏳ Attente de la base de données..."
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
