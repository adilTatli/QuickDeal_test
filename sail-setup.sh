#!/bin/bash

./vendor/bin/sail up -d

echo "Запуск контейнеров..."
sleep 10

CONTAINER_NAME=$(docker ps --filter "name=laravel.test" --format "{{.Names}}")

cp .env.example .env

docker exec "$CONTAINER_NAME" php artisan migrate --force
docker exec "$CONTAINER_NAME" php artisan key:generate --force
echo "Установка завершена"
