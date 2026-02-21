#!/bin/bash

# Guest List Pro - Script de Deploy Automatizado (DOCKER)
# Autor: Agent Gemini
# Data: 2026-02-21

set -e # Aborta o script em caso de erro

echo "🚀 Iniciando Deploy Isolado (Docker): Guest List Pro"

# 1. Atualização do Código
echo "📥 Sincronizando com o repositório remoto (main)..."
git pull origin main

# 2. Configuração do Ambiente
if [ ! -f .env ]; then
    echo "⚠️ Arquivo .env não encontrado! Criando a partir do .env.example..."
    cp .env.example .env
    echo "🚨 IMPORTANTE: Ajuste as credenciais no .env e rode o deploy novamente."
    exit 1
fi

# 3. Subir Containers
echo "🐳 Construindo e iniciando containers (modo daemon)..."
# Usamos --build para garantir que qualquer mudança no Dockerfile seja aplicada
docker compose up -d --build

# 4. Dependências de Backend (Dentro do Container)
echo "📦 Instalando dependências PHP (Otimizado)..."
docker compose exec -T laravel.test composer install --no-dev --optimize-autoloader --no-interaction

# 5. Dependências de Frontend (Dentro do Container)
# Nota: Se você buildar no host, precisa do node. Aqui buildamos dentro do container de teste ou app.
echo "🎨 Compilando assets do Frontend (Vite)..."
docker compose exec -T laravel.test npm install
docker compose exec -T laravel.test npm run build

# 6. Banco de Dados (Dentro do Container)
echo "🗄️ Executando migrações..."
docker compose exec -T laravel.test php artisan migrate --force

# 7. Otimização de Performance
echo "⚡ Gerando caches de alta performance..."
docker compose exec -T laravel.test php artisan optimize:cache
docker compose exec -T laravel.test php artisan filament:cache-components
docker compose exec -T laravel.test php artisan storage:link --force

# 8. Gestão de Permissões (Dentro e Fora)
echo "🔒 Ajustando permissões de escrita..."
# Garante que o container consiga escrever nas pastas necessárias
docker compose exec -T laravel.test chown -R www-data:www-data storage bootstrap/cache
docker compose exec -T laravel.test chmod -R 775 storage bootstrap/cache

# 9. Limpeza
echo "🧹 Limpando imagens antigas e caches inúteis..."
docker image prune -f

echo "✅ DEPLOY DOCKER FINALIZADO! 🚀"
echo "🌐 Acesse sua aplicação na porta configurada no .env"
