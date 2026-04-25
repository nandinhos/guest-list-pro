# Design: Simulação de Deploy com GitHub Actions

**Data:** 2026-04-24  
**Status:** Aprovado

---

## Contexto

O projeto é hospedado em uma plataforma PaaS de terceiros que:
- Clona o repositório GitHub automaticamente
- Cria o banco de dados e configura nginx automaticamente
- Executa um script pós-deploy configurável pelo usuário

O script pós-deploy atual não roda `composer install`, causando erro 500 após pushes com alterações de código PHP.

---

## Objetivo

1. Corrigir o script pós-deploy da plataforma para incluir os passos ausentes
2. Criar um workflow GitHub Actions que simula o deploy antes de cada push chegar à produção

---

## Script Pós-Deploy Corrigido

Substituir o script atual da plataforma por:

```bash
#!/bin/bash
set -euo pipefail

echo "Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

echo "Installing npm dependencies..."
npm ci --prefer-offline 2>/dev/null || npm install 2>/dev/null || echo "Skipping npm install"

echo "Building frontend assets..."
npm run build 2>/dev/null || echo "Skipping asset build"

echo "Running migrations..."
php artisan migrate --force

echo "Caching config, routes and views..."
php artisan optimize
php artisan filament:cache-components

echo "Creating storage link..."
php artisan storage:link 2>/dev/null || true

echo "Deploy script complete."
```

**Mudanças em relação ao script anterior:**
- Adicionado `composer install --no-dev --optimize-autoloader` antes das migrations
- Adicionado `php artisan optimize` após migrations
- Adicionado `php artisan filament:cache-components` para cache dos componentes Filament
- Removido `npx vite build` duplicado (unificado em `npm run build`)

---

## GitHub Actions Workflow

Arquivo: `.github/workflows/deploy-check.yml`

**Gatilho:** Push e Pull Request para a branch `main`

**Passos:**

| # | Passo | Detalhe |
|---|-------|---------|
| 1 | Checkout | Clona o código |
| 2 | Setup PHP 8.2 | Com extensões: pdo, pdo_sqlite, mbstring, xml, curl |
| 3 | Composer install | `--no-dev --optimize-autoloader --no-interaction` |
| 4 | Setup Node 20 | Com cache npm |
| 5 | npm ci + build | `npm ci` seguido de `npm run build` |
| 6 | Gerar .env | Copia `.env.example`, configura SQLite em memória, gera APP_KEY |
| 7 | Migrations | `php artisan migrate --force` |
| 8 | Optimize | `php artisan optimize` + `filament:cache-components` |
| 9 | Testes | `php artisan test --compact` |

**Banco de dados no CI:** SQLite (`:memory:`) — evita dependência de MySQL no CI e é suficiente para validar migrations e testes.

---

## Arquivos a Criar/Modificar

| Arquivo | Ação |
|---------|------|
| `.github/workflows/deploy-check.yml` | Criar |
| `docs/superpowers/specs/2026-04-24-deploy-check-design.md` | Criar (este arquivo) |

O script da plataforma é configurado externamente (no painel), não versionado no repositório.

---

## Critério de Sucesso

- Workflow passa ✅ no GitHub após o push
- Deploy na plataforma não retorna erro 500
- Qualquer quebra futura é detectada antes do deploy
