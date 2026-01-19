#!/bin/bash

# Guest List Pro - Script de Deploy Automatizado
# Executar este script no servidor VPS (Produção)

set -e # Parar execução em caso de erro

echo "🚀 Iniciando deploy do Guest List Pro..."

# 1. Atualizar Repositório
echo "📥 Baixando atualizações do Git..."
git pull origin main

# 2. Instalar Dependências PHP
echo "📦 Instalando dependências do Composer (Otimizado)..."
composer install --no-dev --optimize-autoloader --no-interaction

# 3. Instalar Dependências Frontend e Build
echo "🎨 Buildando assets do Frontend (Vite)..."
npm ci
npm run build

# 4. Migrações de Banco de Dados
echo "🗄️ Executando migrações de banco de dados..."
php artisan migrate --force

# 5. Otimizações do Laravel
echo "⚡ Otimizando caches do Laravel..."
php artisan config:cache
php artisan event:cache
php artisan route:cache
php artisan view:cache

# 6. Permissões (Ajustar conforme usuário do servidor web e proprietário)
# Assumindo www-data como usuário web comum. Ajuste se necessário.
echo "🔒 Ajustando permissões de diretórios..."
sudo chown -R $USER:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# 7. Reiniciar Filas (Se usar Supervisor)
if [ -f /etc/supervisor/conf.d/guest-list-pro-worker.conf ]; then
    echo "🔄 Reiniciando filas..."
    php artisan queue:restart
fi

echo "✅ Deploy concluído com sucesso! 🚀"
