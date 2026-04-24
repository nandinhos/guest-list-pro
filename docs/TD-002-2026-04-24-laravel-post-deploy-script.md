# TD-002 — Script Pós-Deploy Laravel em Plataformas PaaS

**Data:** 2026-04-24  
**Tipo:** Lição Aprendida / Referência Técnica  
**Contexto:** Derivado do deploy do projeto `guest-list-pro` em plataforma PaaS com integração GitHub  
**Aplicação:** Qualquer projeto Laravel 10, 11 ou 12

---

## 1. Problema

Plataformas PaaS com integração GitHub (Heroku, Railway, Render, Coolify, CapRover e similares) clonam o repositório e servem os arquivos, mas **não rodam nenhum comando automaticamente** além do que for configurado no script pós-deploy.

Sem o script correto, o resultado após cada push é erro 500, pois:

| O que falta | Sintoma |
|-------------|---------|
| `composer install` não rodou | `Class not found`, autoload desatualizado |
| `php artisan optimize` não rodou | Config/routes/views sem cache, lentidão ou erro |
| `php artisan migrate` não rodou | Banco desatualizado, erro de coluna/tabela |
| `npm run build` não rodou | Assets sem compilar, página sem estilo/JS |
| `storage:link` não criado | Upload de arquivos quebrado |

---

## 2. Script Padrão para Projetos Laravel

### 2.1 Com Filament

Use em projetos que utilizam **Filament v3 ou v4**:

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

### 2.2 Sem Filament

Use em projetos Laravel puros, Livewire sem Filament, ou APIs:

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

echo "Creating storage link..."
php artisan storage:link 2>/dev/null || true

echo "Deploy script complete."
```

---

## 3. Anatomia do Script — Linha por Linha

### `set -euo pipefail`
Aborta o script imediatamente se qualquer comando falhar. Sem isso, erros são ignorados e o deploy "passa" mesmo que algo esteja quebrado.

| Flag | Comportamento |
|------|---------------|
| `-e` | Aborta no primeiro erro |
| `-u` | Erro em variável indefinida |
| `-o pipefail` | Propaga erro em pipes (`cmd1 \| cmd2`) |

### `composer install --no-dev --optimize-autoloader --no-interaction`

| Flag | Motivo |
|------|--------|
| `--no-dev` | Não instala pacotes de teste em produção (PHPUnit, Faker, etc.) |
| `--optimize-autoloader` | Gera classmap estático — 30-40% mais rápido que autoload dinâmico |
| `--no-interaction` | Evita que o processo trave esperando input do usuário |

> **Atenção:** Se a plataforma não tiver Composer no PATH, adicione `--working-dir=/caminho/para/projeto`.

### `npm ci --prefer-offline || npm install || echo "Skipping"`

- `npm ci` é preferível ao `npm install` em CI/deploy — usa o `package-lock.json` exato, sem resolver versões
- `--prefer-offline` reutiliza cache local se disponível (deploy mais rápido)
- O fallback `|| echo "Skipping"` evita que projetos sem `package.json` quebrem

### `npm run build`

Compila os assets com Vite. O output vai para `public/build/`. Sem isso, o Laravel lança `ViteException: Unable to locate file in Vite manifest`.

### `php artisan migrate --force`

O `--force` é obrigatório em produção — sem ele, o artisan pede confirmação interativa e o script trava.

> **Cuidado:** Sempre garanta que as migrations são reversíveis e testadas localmente antes do deploy.

### `php artisan optimize`

Executa três comandos em sequência:
- `config:cache` — serializa todos os arquivos de `config/` em um único arquivo PHP
- `route:cache` — compila todas as rotas em cache
- `view:cache` — pré-compila todas as views Blade

Resultado: eliminação de I/O em cada request de produção.

### `php artisan filament:cache-components`

Específico para projetos com Filament. Gera cache dos componentes Filament para evitar descoberta dinâmica em cada request. **Remover em projetos sem Filament.**

### `php artisan storage:link 2>/dev/null || true`

Cria o symlink `public/storage → storage/app/public`. O `|| true` garante que o script não falhe se o link já existir.

---

## 4. Ordem de Execução — Por Que Importa

A ordem dos comandos não é arbitrária:

```
1. composer install     → vendor/ precisa existir antes de qualquer artisan
2. npm ci + build       → assets compilados antes de cachear views
3. migrate              → banco atualizado antes de cachear config
4. optimize             → cacheia config/routes/views após tudo pronto
5. filament:cache       → após optimize (depende de config cacheada)
6. storage:link         → independente, mas por convenção vai por último
```

> Executar `php artisan optimize` antes do `composer install` causa erro de `Class not found` porque o autoload ainda não existe.

---

## 5. Variáveis de Ambiente Obrigatórias na Plataforma

O `.env` da plataforma (configurado no painel, nunca commitado) deve ter no mínimo:

```env
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:...         # php artisan key:generate --show
APP_URL=https://seu-dominio.com

DB_CONNECTION=mysql
DB_HOST=...
DB_PORT=3306
DB_DATABASE=...
DB_USERNAME=...
DB_PASSWORD=...
```

> **APP_KEY ausente** → `No application encryption key has been specified` → erro 500 imediato.  
> **APP_DEBUG=true em produção** → expõe stack traces com dados sensíveis ao usuário final.

---

## 6. Diagnóstico de Erros 500 Pós-Deploy

Use esta árvore de decisão ao encontrar erro 500:

```
Erro 500 após deploy
│
├── "Class not found" ou "Failed opening required"
│   └── composer install não rodou → verificar PATH do Composer na plataforma
│
├── "No application encryption key"
│   └── APP_KEY não está no .env da plataforma → php artisan key:generate --show
│
├── "SQLSTATE" / erro de banco
│   ├── Credenciais erradas → verificar DB_* no .env da plataforma
│   └── Migration falhou → verificar logs da migration
│
├── "Unable to locate file in Vite manifest"
│   └── npm run build não rodou → verificar se Node/npm está disponível
│
└── Página em branco ou CSS quebrado
    └── php artisan optimize não rodou → executar manualmente via CLI da plataforma
```

---

## 7. GitHub Actions — Workflow de Validação

Antes de cada deploy, valide o processo com este workflow. Ele simula os mesmos passos do script pós-deploy em ambiente limpo:

```yaml
name: Deploy Check

on:
  push:
    branches: [main]
  pull_request:
    branches: [main]

env:
  FORCE_JAVASCRIPT_ACTIONS_TO_NODE24: true

jobs:
  deploy-simulation:
    name: Simulate Production Deploy
    runs-on: ubuntu-latest
    timeout-minutes: 20

    permissions:
      contents: read

    services:
      mysql:
        image: mysql:8.4
        env:
          MYSQL_ROOT_PASSWORD: root
          MYSQL_DATABASE: testing
        ports:
          - 3306:3306
        options: >-
          --health-cmd="mysqladmin ping"
          --health-interval=10s
          --health-timeout=5s
          --health-retries=5

    steps:
      - uses: actions/checkout@11bd71901bbe5b1630ceea73d27597364c9af683 # v4.2.2

      - uses: shivammathur/setup-php@cf4cade2721270509d5b1c766ab3549210a39a2a # v2.33.0
        with:
          php-version: '8.4'
          extensions: pdo, pdo_mysql, mbstring, xml, curl, zip, bcmath, intl
          coverage: none

      - uses: actions/cache@5a3ec84eff668545956fd18022155c47e93e2684 # v4.2.3
        with:
          path: vendor
          key: composer-${{ hashFiles('composer.lock') }}

      - run: composer install --optimize-autoloader --no-interaction --prefer-dist

      - uses: actions/setup-node@49933ea5288caeca8642d1e84afbd3f7d6820020 # v4.4.0
        with:
          node-version: '20'
          cache: 'npm'

      - run: npm ci
      - run: npm run build

      - name: Prepare .env
        run: |
          cp .env.example .env
          sed -i 's|DB_CONNECTION=.*|DB_CONNECTION=mysql|' .env
          sed -i 's|# DB_HOST=.*|DB_HOST=127.0.0.1|' .env
          sed -i 's|# DB_PORT=.*|DB_PORT=3306|' .env
          sed -i 's|# DB_DATABASE=.*|DB_DATABASE=testing|' .env
          sed -i 's|# DB_USERNAME=.*|DB_USERNAME=root|' .env
          sed -i 's|# DB_PASSWORD=.*|DB_PASSWORD=root|' .env
          php artisan key:generate --no-interaction

      - run: php artisan migrate --force
      - run: php artisan test --compact
      - run: php artisan optimize
      # Remover a linha abaixo em projetos sem Filament:
      - run: php artisan filament:cache-components
```

> **Nota sobre o workflow:** Em CI, o `composer install` inclui pacotes de dev (sem `--no-dev`) para que o PHPUnit esteja disponível. No script de produção, use `--no-dev`.

---

## 8. Lições Aprendidas — guest-list-pro

Problemas encontrados durante o primeiro deploy deste projeto e suas causas raiz:

| # | Problema | Causa Raiz | Solução |
|---|----------|------------|---------|
| 1 | Erro 500 após deploy | `composer install` ausente no script | Adicionar como primeiro passo |
| 2 | Migration falhava no CI | `dropColumn('cpf')` antes de `dropUnique()` no SQLite | Sempre dropar índice antes da coluna que ele referencia |
| 3 | `fake()->cpf()` desconhecido | `APP_FAKER_LOCALE=en_US` no `.env.example` | Definir `APP_FAKER_LOCALE=pt_BR` em projetos brasileiros |
| 4 | Testes de login falhando | Redirects desatualizados após adicionar middleware | Atualizar testes ao mudar comportamento de redirect |

### Lição sobre migrations e SQLite

SQLite não permite remover uma coluna que está referenciada em um índice único. A ordem correta é sempre:

```php
// ✅ CORRETO
Schema::table('tabela', fn($t) => $t->dropUnique(['col_a', 'col_b']));
Schema::table('tabela', fn($t) => $t->dropColumn('col_b'));

// ❌ ERRADO — falha no SQLite
Schema::table('tabela', function($t) {
    $t->dropColumn('col_b');        // falha: índice ainda referencia col_b
    $t->dropUnique(['col_a', 'col_b']);
});
```

> Esta restrição não afeta MySQL, mas o CI usa SQLite por padrão em muitos projetos — o bug só aparece no CI, não localmente com MySQL.

---

## 9. Checklist de Novo Projeto

Ao iniciar um novo projeto Laravel com deploy em PaaS:

- [ ] Configurar script pós-deploy no painel da plataforma
- [ ] Definir `APP_FAKER_LOCALE=pt_BR` no `.env.example` (projetos brasileiros)
- [ ] Criar workflow `.github/workflows/deploy-check.yml`
- [ ] Garantir que todas as variáveis de ambiente obrigatórias estão no painel
- [ ] Verificar se migrations são compatíveis com SQLite (para CI) ou usar MySQL no CI
- [ ] Testar o deploy com um push vazio após configurar o script

---

## 10. Lição Aprendida — Cache Corrompido por Rodar Artisan Fora do Container

### Sintoma

Testes passam no GitHub Actions (CI) mas falham localmente com:

```
There is no existing directory at "/home/user/projects/app/storage/logs" and it could not be created: Permission denied
```

O path no erro é o path do **host**, mas os testes rodam dentro do container Docker (onde o path correto seria `/var/www/html/storage/logs`).

### Root Cause

O `php artisan optimize` foi executado **diretamente no host** (fora do container Sail) em algum momento. Isso gerava o arquivo `bootstrap/cache/config.php` com paths do host hardcoded:

```
/home/user/projects/app/storage/logs   ← path do HOST gravado no cache
```

Quando os testes rodam dentro do container (que usa `/var/www/html/...`), Laravel carrega o cache corrompido e tenta acessar paths que não existem dentro do container — causando `Permission denied`.

### Diagnóstico

```bash
grep -c "home/usuario" bootstrap/cache/config.php
# Se retornar > 0, o cache foi gerado fora do container
```

### Solução

```bash
vendor/bin/sail artisan optimize:clear
```

Isso limpa todos os caches e na próxima execução dentro do container os paths são gravados corretamente como `/var/www/html/...`.

### Regra de Ouro

> **Nunca rodar `php artisan` diretamente no host em projetos Sail. Sempre usar `vendor/bin/sail artisan`.**

| Errado | Certo |
|--------|-------|
| `php artisan optimize` | `vendor/bin/sail artisan optimize` |
| `php artisan migrate` | `vendor/bin/sail artisan migrate` |
| `php artisan test` | `vendor/bin/sail artisan test` |

Adicionar ao checklist de novos projetos: se testes falharem com paths inesperados, rodar `vendor/bin/sail artisan optimize:clear` antes de investigar outros problemas.

---

## 11. Referências

- [Laravel Deployment — documentação oficial](https://laravel.com/docs/deployment)
- [Composer — install flags](https://getcomposer.org/doc/03-cli.md#install-i)
- [GitHub Actions — services](https://docs.github.com/en/actions/using-containerized-services)
- [Filament — deployment](https://filamentphp.com/docs/panels/installation#deploying-to-production)
