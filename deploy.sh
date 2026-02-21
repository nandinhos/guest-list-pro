#!/bin/bash

# Guest List Pro - Script de Deploy Automatizado (v2)
# Autor: Agent Gemini
# Data: 2026-02-21

set -e # Aborta o script em caso de erro

# Configurações - AJUSTE ESTAS VARIÁVEIS CONFORME SUA VPS
WEB_USER="www-data" # Usuário do servidor web (Nginx/Apache)
REPO_DIR="/var/www/guest-list-pro" # Caminho da aplicação na VPS

echo "🚀 Iniciando Deploy Estratégico: Guest List Pro"

# 1. Manutenção
echo "🚧 Ativando modo de manutenção..."
php artisan down || true

# 2. Atualização do Código
echo "📥 Sincronizando com o repositório remoto (main)..."
git pull origin main

# 3. Dependências de Backend (Composer)
echo "📦 Instalando dependências PHP (Otimizado para Produção)..."
composer install --no-dev --optimize-autoloader --no-interaction

# 4. Dependências de Frontend (NPM)
echo "🎨 Compilando assets do Frontend (Vite)..."
npm ci
npm run build

# 5. Banco de Dados
echo "🗄️ Executando migrações críticas..."
php artisan migrate --force

# 6. Links Simbólicos
echo "🔗 Criando link simbólico para storage..."
php artisan storage:link --force

# 7. Otimização de Performance (Cache de Produção)
echo "⚡ Gerando caches de alta performance..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan filament:cache-components # Específico para Filament v4

# 8. Gestão de Permissões (Crítico para VPS)
echo "🔒 Aplicando permissões de segurança..."
sudo chown -R $USER:$WEB_USER .
sudo find . -type f -exec chmod 644 {} \;
sudo find . -type d -exec chmod 755 {} \;

# Permissões especiais para pastas de escrita
sudo chgrp -R $WEB_USER storage bootstrap/cache
sudo chmod -R ug+rwx storage bootstrap/cache

# 9. Reiniciar Processos
if php artisan queue:restart > /dev/null 2>&1; then
    echo "🔄 Trabalhadores de fila reiniciados."
fi

# 10. Finalização
echo "🚀 Desativando modo de manutenção..."
php artisan up

echo "✅ DEPLOY FINALIZADO COM SUCESSO! Aplicação pronta para uso."
