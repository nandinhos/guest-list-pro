# HANDOFF — Sessão 2026-04-24
## CI/CD, Deploy e Correções de Ambiente

> **Para o próximo agente:** Leia este documento antes de continuar qualquer feature. O projeto acabou de ganhar pipeline de CI e deploy funcional. Todos os testes estão passando.

---

## 1. O QUE FOI FEITO NESTA SESSÃO

### 1.1 GitHub Actions — Deploy Check

**Arquivo criado:** `.github/workflows/deploy-check.yml`

Workflow que roda em cada push/PR para `main`. Simula o deploy de produção:

```
composer install → npm ci → npm run build → .env → migrate → tests → optimize → filament:cache
```

**Configurações importantes:**
- PHP 8.4 (alinhado com o container Sail)
- MySQL 8.4 como serviço (igual à produção — não SQLite)
- SHA pins em todas as actions (segurança supply chain)
- `FORCE_JAVASCRIPT_ACTIONS_TO_NODE24: true` (sem warnings de deprecação)
- `timeout-minutes: 20`
- `permissions: contents: read`

**Status:** ✅ Verde — 73 testes passando

---

### 1.2 Script Pós-Deploy Corrigido na Plataforma

**Problema:** Script anterior não tinha `composer install` → erro 500 após qualquer push.

**Script atual no painel da plataforma PaaS:**

```bash
#!/bin/bash
set -euo pipefail
composer install --no-dev --optimize-autoloader --no-interaction
npm ci --prefer-offline 2>/dev/null || npm install 2>/dev/null || true
npm run build 2>/dev/null || true
php artisan migrate --force
php artisan optimize
php artisan filament:cache-components
php artisan storage:link 2>/dev/null || true
```

**URL de produção:** `testeguestlist.fssdev.com.br` (DNS no Cloudflare)

**Referência completa:** `docs/TD-002-2026-04-24-laravel-post-deploy-script.md`

---

### 1.3 Bugs Corrigidos

#### Bug 1 — Migration `monitores` falhava no CI

**Arquivo:** `database/migrations/2026_04_22_120000_update_monitores_add_document_fields.php`

**Problema:** `dropColumn('cpf')` executado ANTES de `dropUnique(['event_id', 'cpf'])`. SQLite não permite remover coluna referenciada em índice único.

**Fix:** Inverter a ordem — `dropUnique` primeiro, depois `dropColumn`.

**Regra geral:**
```php
// ✅ SEMPRE assim
Schema::table('t', fn($t) => $t->dropUnique(['col_a', 'col_b']));
Schema::table('t', fn($t) => $t->dropColumn('col_b'));
```

#### Bug 2 — `fake()->cpf()` desconhecido

**Arquivo:** `.env.example`

**Problema:** `APP_FAKER_LOCALE=en_US` — o formato `cpf` só existe no locale `pt_BR`.

**Fix:** `APP_FAKER_LOCALE=pt_BR`

#### Bug 3 — Testes de redirect desatualizados

**Arquivo:** `tests/Feature/Auth/LoginTest.php`

**Problema:** Testes esperavam redirect para `/promoter`, `/validator`, etc. mas o middleware `EnsureEventSelected` faz o redirect ir para `/promoter/select-event`, `/validator/select-event`, etc.

**Fix:** Atualizar todos os `assertRedirect()` para incluir `/select-event`.

---

### 1.4 Cache Corrompido — Problema de Ambiente Local

**Sintoma:** Testes falhavam localmente com:
```
Permission denied at /home/nandodev/projects/guest-list-pro/storage/logs
```
Mas passavam no CI (GitHub Actions).

**Root cause:** `php artisan optimize` foi rodado diretamente no host (fora do container Docker). Isso gravou paths do HOST (`/home/nandodev/...`) no `bootstrap/cache/config.php`. Os testes dentro do container usam `/var/www/html/...` — path diferente.

**Fix imediato:**
```bash
vendor/bin/sail artisan optimize:clear
```

**Diagnóstico:**
```bash
grep -c "home/nandodev" bootstrap/cache/config.php
# Se > 0 → cache corrompido → rodar optimize:clear
```

**Regra de ouro:** NUNCA usar `php artisan` direto no host em projetos Sail. SEMPRE `vendor/bin/sail artisan`.

---

## 2. ESTADO ATUAL DO PROJETO

### 2.1 Testes

```bash
vendor/bin/sail artisan test --compact
# Tests: 73 passed (210 assertions) ✅
```

### 2.2 CI

```
GitHub Actions → Deploy Check → ✅ Verde
Branch: main
```

### 2.3 Produção

```
URL: testeguestlist.fssdev.com.br
Deploy: automático via push para main (PaaS + GitHub)
Status: ✅ Funcionando
```

---

## 3. PRÓXIMAS FEATURES SUGERIDAS

### 3.1 SPEC-0007 — Painel Excursionista (Prioritária)

**Spec:** `docs/SPECS/SPEC-0007-painel-excursionista.md` (criada em 2026-04-22)
**Plano:** `.devorq/plans/2026-04-22-painel-excursionista.md`

A SPEC e o plano já estão escritos. É só executar.

### 3.2 E2E Tests para Ticket Pricing

Os testes unit/feature para a arquitetura de ticket pricing (SPEC-0005) existem, mas os E2E ainda não foram criados.

### 3.3 Outras Features

| Feature | Prioridade | Observação |
|---------|-----------|------------|
| Notificações push | Baixa | Enhancement opcional |
| Relatórios exportáveis | Baixa | Enhancement opcional |

---

## 4. CONTEXTO DE ARQUITETURA — O QUE NÃO MUDAR

### 4.1 Fluxo de Login → Painéis

Após login, todos os painéis (exceto Admin) redirecionam para `/select-event` antes de qualquer outra tela. O middleware `EnsureEventSelected` (`app/Http/Middleware/EnsureEventSelected.php`) garante isso.

```
Login → /promoter/select-event → EventSelectorGrid → session('selected_event_id') → Dashboard
```

### 4.2 Ticket Pricing

TicketType **não tem** campo `price`. O preço existe apenas na tabela pivot `ticket_type_sector` (combinação TicketType + Setor). Não voltar para o modelo antigo.

### 4.3 Migrations com SQLite

O CI usa MySQL (serviço no GitHub Actions). **Não mudar para SQLite no CI.** Embora testes locais usem SQLite em algumas configurações, a produção e o CI usam MySQL.

---

## 5. COMANDOS DE REFERÊNCIA RÁPIDA

```bash
# SEMPRE via Sail (nunca php artisan direto)
vendor/bin/sail artisan test --compact          # rodar testes
vendor/bin/sail artisan migrate                 # rodar migrations
vendor/bin/sail artisan optimize:clear          # limpar cache (se comportamento estranho)
vendor/bin/sail artisan migrate:fresh --seed    # reset completo do banco

# Verificar saúde do ambiente
grep -c "home/nandodev" bootstrap/cache/config.php  # > 0 = cache corrompido

# E2E
node node_modules/.bin/playwright test e2e/smoke-tests.spec.ts --reporter=list
```

---

## 6. DOCUMENTAÇÃO GERADA NESTA SESSÃO

| Arquivo | Conteúdo |
|---------|----------|
| `.github/workflows/deploy-check.yml` | Workflow CI/CD completo |
| `docs/TD-002-2026-04-24-laravel-post-deploy-script.md` | Referência técnica do script pós-deploy + lições aprendidas |
| `docs/superpowers/specs/2026-04-24-deploy-check-design.md` | Design da solução de CI/CD |
| `docs/superpowers/plans/2026-04-24-deploy-check.md` | Plano de implementação executado |
| `HANDOVER.md` | Atualizado para versão 2.2.0 |

---

*Handoff gerado em: 2026-04-24*
*Agente: Claude Sonnet 4.6 via Claude Code*
