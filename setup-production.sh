#!/bin/bash
# Setup para servidor de produção (sem Docker / cPanel / Forge)
# Rode este script após cada deploy

set -e

echo "📁 Criando estrutura de diretórios..."
mkdir -p storage/framework/views
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/logs
mkdir -p bootstrap/cache

echo "🔒 Ajustando permissões..."
chmod -R 777 storage bootstrap/cache 2>/dev/null || chmod -R 777 storage bootstrap/cache

echo "⚡ Gerando caches..."
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Setup concluído!"
