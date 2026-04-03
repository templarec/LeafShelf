#!/bin/bash

echo "🚀 Starting LeafShelf dev environment..."

trap "kill 0" EXIT

echo "📦 Laravel server"
php artisan serve &

echo "⚡ Vite frontend"
npm run dev &

echo "📜 Laravel logs"
tail -f storage/logs/laravel.log &

wait
