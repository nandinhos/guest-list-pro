# HANDOFF — Sessão 2026-04-24
## Importação Automática de Evento a partir de Arquivo .md

> **Para Minimax:** Leia este documento antes de iniciar. A feature de importação já está parcialmente implementada e funcionando. Sua tarefa é a SPEC-0014 — estender o processo para criar Event, Sectors e EventAssignments automaticamente a partir do arquivo.

---

## 1. ESTADO ATUAL DO PROJETO

### 1.1 Banco de Dados
- **Limpo:** apenas 1 usuário — `nando@guestlist.pro` (admin, senha: `password`)
- **Sem eventos, setores ou convidados** — tudo será criado pela importação

### 1.2 Testes
```bash
vendor/bin/sail artisan test --compact
# Tests: 73 passed (210 assertions) ✅
```

### 1.3 CI
- GitHub Actions → Deploy Check → ✅ Verde
- Branch: main

### 1.4 Tela de Importação (já funciona)
- URL: `/admin/import-guests`
- Upload `.md` ou `.txt` → preview instantâneo → botão "Confirmar Importação"
- Detecta e reporta duplicatas com auditoria (promotor + setor do registro existente)

---

## 2. O QUE FOI FEITO NESTA SESSÃO

| # | O que | Arquivo |
|---|-------|---------|
| 1 | Fix: `afterStateHydrated` → `afterStateUpdated` + `->live()` | `ImportGuestsPage.php` |
| 2 | Fix: `getSectorsByEvent()` usava `->toArray()` quebrando `$sector->id` | `GuestImportService.php` |
| 3 | Feature: relatório de duplicatas com tabela de auditoria (promotor + setor existente) | `GuestImportService.php` + view |
| 4 | Feature: emails de promoters gerados como `nome@guestlist.pro` com normalização de acentos | `GuestImportService.php` |
| 5 | Feature: seção de ajuda com novo estilo (dark code block + badge cards) | `import-guests.blade.php` |
| 6 | Fix: migration `seed_production_users` atualizada — só `nando@guestlist.pro` | `database/migrations/2026_04_07_000001_seed_production_users.php` |
| 7 | Fix: `UserSeeder.php` atualizado — só `nando@guestlist.pro` | `database/seeders/UserSeeder.php` |

---

## 3. PRÓXIMA TAREFA — SPEC-0014

**Spec completa:** `docs/superpowers/specs/SPEC-0014-auto-import-evento.md`

### Resumo do que fazer

O arquivo `.md` já tem o header:
```
# Dados do Evento #
**Evento:** XXXPERIENCE 30 ANOS
**Data:** 25/04/2026
**Local:** Fazenda Santa Rita - Itu-SP
**Horário:** 14:00 - 06:00
```

Implementar:
1. **`GuestImportService.php`** — novo método `parseEventData()` + atualizar `parseFile()` e `import()`
2. **`ImportGuestsPage.php`** — remover Select de evento; expor `$parsedEvent` para o preview
3. **`import-guests.blade.php`** — card "Evento Detectado" no preview

**O que é criado automaticamente na importação:**
- `Event` via `firstOrCreate(name + date)` com `status = active`
- `Sector` via `firstOrCreate(event_id + name)` para cada setor do arquivo
- `User` (promoter) via `firstOrCreate` — email `nome@guestlist.pro`, senha `password`
- `EventAssignment` via `firstOrCreate(user_id + event_id + sector_id)` — um por (promoter × setor)
- `Guest` — importados normalmente; duplicata por CPF gera warning

### Regra crítica

> **SEMPRE usar `vendor/bin/sail artisan` — NUNCA `php artisan` direto no host.** Rodar fora do container corrompe `bootstrap/cache/config.php` com paths do host, causando `Permission denied` nos testes.

---

## 4. ARQUIVOS CRÍTICOS

```
app/Services/GuestImportService.php                         ← MODIFICAR (principal)
app/Filament/Admin/Pages/ImportGuestsPage.php               ← MODIFICAR
resources/views/filament/admin/pages/import-guests.blade.php ← MODIFICAR
app/Models/Event.php                                         ← ler só (fillable já correto)
app/Models/Sector.php                                        ← ler só (fillable já correto)
app/Models/EventAssignment.php                               ← ler só (fillable já correto)
docs/lists/listageral.md                                     ← arquivo de teste real
```

---

## 5. COMANDOS DE REFERÊNCIA

```bash
# Testes (sempre via Sail)
vendor/bin/sail artisan test --compact

# Reset completo do banco
vendor/bin/sail artisan migrate:fresh --seed

# Formatar código (obrigatório antes de finalizar)
vendor/bin/sail bin pint --dirty

# Se testes falharem com paths inesperados (cache corrompido):
vendor/bin/sail artisan optimize:clear
```

---

## 6. VERIFICAÇÃO FINAL (após implementar)

1. `vendor/bin/sail artisan migrate:fresh --seed`
2. Acessar `/admin/import-guests` como `nando@guestlist.pro`
3. Upload de `docs/lists/listageral.md`
4. Preview mostra card: XXXPERIENCE 30 ANOS, 25/04/2026, Fazenda Santa Rita, 14:00—06:00
5. Confirmar importação
6. Verificar banco:
   - `events`: 1 registro
   - `sectors`: PISTA e BACKSTAGE vinculados ao evento
   - `users`: promoters com email `@guestlist.pro`
   - `event_assignments`: (promoter × setor) — sem duplicatas
   - `guests`: todos importados
7. `vendor/bin/sail artisan test --compact` → 73 testes passando

---

*Handoff gerado em: 2026-04-24*
*Agente: Claude Sonnet 4.6 via Claude Code*
