#!/bin/sh
set -e
cd /var/www/html

mkdir -p storage/app/private storage/app/public storage/framework/cache storage/framework/sessions storage/framework/views storage/logs
chown -R www-data:www-data storage bootstrap/cache

if [ -z "$APP_KEY" ]; then
  echo "WARN: APP_KEY vacío, se genera uno temporal (defínelo en .env para que sea estable)"
  export APP_KEY=$(php artisan key:generate --show)
fi

echo "Esperando a la base de datos..."
until php -r 'new PDO("pgsql:host=".getenv("DB_HOST").";port=".getenv("DB_PORT").";dbname=".getenv("DB_DATABASE"), getenv("DB_USERNAME"), getenv("DB_PASSWORD"));' 2>/dev/null; do
  sleep 2
done

php artisan migrate --force

if [ "$RUN_SEEDERS" = "true" ]; then
  COUNT=$(php artisan tinker --execute='echo DB::table("usuarios")->count();' 2>/dev/null | tail -n1 | tr -d '[:space:]')
  if [ "$COUNT" = "0" ]; then
    echo "Ejecutando seeders (primer arranque)..."
    php artisan db:seed --force
  fi
fi

exec "$@"
