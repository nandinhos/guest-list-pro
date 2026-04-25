# Deploy Check — GitHub Actions Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Criar workflow GitHub Actions que simula o deploy em ambiente limpo a cada push para `main`, detectando falhas antes que cheguem à produção.

**Architecture:** Um único arquivo `.github/workflows/deploy-check.yml` replica os passos do script pós-deploy da plataforma (composer install → npm build → migrate → optimize → tests). O `.env.example` já usa SQLite por padrão, então o CI não precisa de MySQL.

**Tech Stack:** GitHub Actions, PHP 8.2, Node 20, SQLite, PHPUnit 11, Vite

---

## Arquivos

| Ação | Arquivo |
|------|---------|
| Criar | `.github/workflows/deploy-check.yml` |
| Referenciar (não editar) | `.env.example` — já configurado com SQLite |
| Referenciar (não editar) | `phpunit.xml` — já configura env de teste |

---

## Task 1: Criar estrutura do GitHub Actions

**Files:**
- Create: `.github/workflows/deploy-check.yml`

- [ ] **Step 1: Criar o diretório `.github/workflows/`**

```bash
mkdir -p .github/workflows
```

- [ ] **Step 2: Criar o arquivo do workflow**

Criar `.github/workflows/deploy-check.yml` com o conteúdo exato abaixo:

```yaml
name: Deploy Check

on:
  push:
    branches: [main]
  pull_request:
    branches: [main]

jobs:
  deploy-simulation:
    name: Simulate Production Deploy
    runs-on: ubuntu-latest

    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Setup PHP 8.2
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          extensions: pdo, pdo_sqlite, mbstring, xml, curl, zip, bcmath, intl
          coverage: none

      - name: Cache Composer dependencies
        uses: actions/cache@v4
        with:
          path: vendor
          key: composer-${{ hashFiles('composer.lock') }}
          restore-keys: composer-

      - name: Install PHP dependencies (no dev)
        run: composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

      - name: Setup Node 20
        uses: actions/setup-node@v4
        with:
          node-version: '20'
          cache: 'npm'

      - name: Install npm dependencies
        run: npm ci

      - name: Build frontend assets
        run: npm run build

      - name: Prepare .env
        run: |
          cp .env.example .env
          sed -i 's|DB_CONNECTION=.*|DB_CONNECTION=sqlite|' .env
          echo "" >> .env
          echo "DB_DATABASE=:memory:" >> .env
          php artisan key:generate --no-interaction

      - name: Run database migrations
        run: php artisan migrate --force

      - name: Cache config, routes and views
        run: |
          php artisan optimize
          php artisan filament:cache-components

      - name: Run test suite
        run: php artisan test --compact
```

- [ ] **Step 3: Verificar a sintaxe do YAML localmente**

```bash
python3 -c "import yaml; yaml.safe_load(open('.github/workflows/deploy-check.yml'))" && echo "YAML válido"
```

Saída esperada: `YAML válido`

- [ ] **Step 4: Commit**

```bash
git add .github/workflows/deploy-check.yml
git commit -m "feat (devops): adiciona workflow GitHub Actions para simular deploy em produção"
```

---

## Task 2: Validar workflow no GitHub

**Files:**
- Modify: nenhum (apenas push e observação)

- [ ] **Step 1: Push para o repositório**

```bash
git push origin main
```

- [ ] **Step 2: Acompanhar a execução**

Acessar `https://github.com/<seu-usuario>/<repo>/actions` e aguardar o workflow `Deploy Check` aparecer.

Verificar que todos os steps ficam verdes ✅. Se algum step falhar, anotar o nome do step e a mensagem de erro.

- [ ] **Step 3: Verificar falha de testes (se houver)**

Se o step `Run test suite` falhar, rodar localmente para reproduzir:

```bash
vendor/bin/sail artisan test --compact
```

Corrigir os testes que falharem antes de continuar.

---

## Task 3: Atualizar script pós-deploy na plataforma

**Files:**
- Nenhum arquivo no repositório — alteração feita no painel da plataforma

- [ ] **Step 1: Acessar o painel da plataforma e substituir o script pós-deploy**

Substituir o script atual pelo conteúdo abaixo:

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

- [ ] **Step 2: Acionar novo deploy na plataforma**

Fazer um push vazio para forçar o redeploy:

```bash
git commit --allow-empty -m "chore: força redeploy com script corrigido"
git push origin main
```

- [ ] **Step 3: Verificar que a aplicação abre sem erro 500**

Acessar a URL da aplicação na plataforma e confirmar que carrega normalmente.

Se ainda der 500, verificar os logs da plataforma procurando por:
- `Class not found` → `composer install` não rodou
- `No application encryption key` → `APP_KEY` não está no `.env` da plataforma
- `SQLSTATE` → credenciais de banco incorretas no `.env` da plataforma

---

## Critério de Conclusão

- [ ] Workflow aparece verde no GitHub após cada push
- [ ] Aplicação abre na URL da plataforma sem erro 500
- [ ] Qualquer falha futura é detectada no CI antes do deploy
