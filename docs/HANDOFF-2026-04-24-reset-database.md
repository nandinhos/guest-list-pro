# HANDOFF — Sessão 2026-04-24
## Reset Database Button (SPEC-0015)

> **Para Minimax:** Leia este documento antes de iniciar. A feature de reset de banco está implementada e funcionando.

---

## 1. ESTADO ATUAL DO PROJETO

### 1.1 Banco de Dados
- Reset via botão em `/admin/backups` — disponível apenas em `APP_ENV=local|development`
- Comando: `migrate:fresh --seed` com backup automático de segurança

### 1.2 Testes
```bash
vendor/bin/sail artisan test --compact
# Tests: 73 passed (210 assertions) ✅
```

### 1.3 CI
- GitHub Actions → Deploy Check → ✅ Verde
- Branch: main

---

## 2. O QUE FOI FEITO NESTA SESSÃO

| # | O que | Arquivo |
|---|-------|---------|
| 1 | SPEC-0014: auto-import Event from .md header | GuestImportService + ImportGuestsPage + blade |
| 2 | SPEC-0015: reset database button | BackupManagement + backup-management.blade.php |

---

## 3. PRÓXIMA TAREFA — Testar Importação

**Ambiente limpo:** Resetar banco e testar importação:
```bash
# 1. Resetar banco
# Acesse /admin/backups como nando@guestlist.pro
# Clique em "Zerar Banco de Dados" no card Ferramentas de Desenvolvimento

# 2. Fazer import
# Acesse /admin/import-guests
# Upload de docs/lists/listageral.md
# Confirme a importação

# 3. Verifique no banco:
# - events: XXXPERIENCE 30 ANOS
# - sectors: PISTA e BACKSTAGE
# - users: promoters com email @guestlist.pro
# - guests: todos os convidados importados
```

---

*Handoff gerado em: 2026-04-24*
