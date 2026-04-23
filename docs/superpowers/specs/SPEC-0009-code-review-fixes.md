# SPEC-0009: Code Review Fixes - Cleanup Completo

**Versão:** 1.0
**Data:** 2026-04-23
**Status:** Aprovada para implementação
**Branch:** `feat/SPEC-0009-code-review-fixes`

---

## 1. Resumo Executivo

Corrigir 8 problemas de code review identificados no sistema guest-list-pro, abordando issues de performance (N+1 queries), segurança (authorization bypass, mass assignment), bugs (validação, constraint) e manutenibilidade (código duplicado, God Class).

**Tempo estimado:** 4-6 tarefas rápidas
**Metodologia:** DEVORQ v3 (7 Gates bloqueantes)

---

## 2. Contexto Técnico

### Stack
- Laravel 12
- Filament v4
- Livewire v3
- MySQL
- Docker/Sail

### Arquivos Principais Envolvidos
```
app/Filament/Widgets/AdminPromoterQuotaWidget.php     [P0]
app/Filament/Excursionista/Resources/ExcursaoResource/RelationManagers/VeiculosRelationManager.php  [P1]
app/Filament/Excursionista/Resources/ExcursaoResource/RelationManagers/MonitoresRelationManager.php [P1]
app/Models/Guest.php                                   [P1]
app/Filament/Validator/Resources/Guests/Schemas/GuestForm.php  [P2]
database/migrations/*update_monitores_add_document_fields.php  [P2]
app/Services/GuestCheckInService.php                  [P2] (a criar)
app/Services/ApprovalRequestService.php                [P2] (documentar)
```

---

## 3. Issues Detalhados

### 3.1 ISSUE-001: N+1 Queries no AdminPromoterQuotaWidget [P0]

**Severidade:** CRÍTICO
**Arquivo:** `app/Filament/Widgets/AdminPromoterQuotaWidget.php`
**Linhas:** 44-81

**Problema:**
Os métodos `getStateUsing()` nas colunas executam queries individuais para CADA linha da tabela:
- `guest_count` (linha 44-47): 1 query por linha
- `remaining` (linha 56-59): 1 query por linha
- `usage` (linha 64-81): múltiplas queries por linha

**Impacto:** ~80 queries para 20 promoters (4 queries × 20 = 80)

**Solução:**
Usar `withCount()` com closure-based subquery para contar guests uma única vez.

**Critério de Aceitação:**
- [ ] Widget carrega com ≤5 queries (vs ~80 atual)
- [ ] Contadores de guests corretos
- [ ] E2E tests passando

---

### 3.2 ISSUE-002: veiculo_id não definido no Repeater [P1]

**Severidade:** ALTO
**Arquivo:** `app/Filament/Excursionista/Resources/ExcursaoResource/RelationManagers/VeiculosRelationManager.php`
**Linha:** 98-103

**Problema:**
O `mutateRelationshipDataBeforeCreateUsing` no Repeater de monitores não define `veiculo_id`. Quando um monitor é criado via repeater dentro de um veículo, o `veiculo_id` fica NULL.

**Solução:**
Adicionar `$data['veiculo_id'] = $this->getOwnerRecord()->id;`

**Critério de Aceitação:**
- [ ] Monitor criado via VeiculosRelationManager tem `veiculo_id` preenchido
- [ ] ListMonitores mostra monitores do veículo correto

---

### 3.3 ISSUE-003: Authorization bypass nos RelationManagers [P1]

**Severidade:** ALTO
**Arquivos:**
- `app/Filament/Excursionista/Resources/ExcursaoResource/RelationManagers/VeiculosRelationManager.php`
- `app/Filament/Excursionista/Resources/ExcursaoResource/RelationManagers/MonitoresRelationManager.php`

**Problema:**
Os RelationManagers não verificam se o usuário logado tem permissão para criar/editar/deletar registros.

**Solução:**
Implementar `canCreate()`, `canEdit()`, `canDelete()` com verificação de ownership.

**Critério de Aceitação:**
- [ ] Usuário não consegue criar/editar/deletar recursos de outros
- [ ] Mensagem de erro clara quando negado (403 Forbidden)

---

### 3.4 ISSUE-004: Mass Assignment em Guest [P1]

**Severidade:** ALTO
**Arquivo:** `app/Models/Guest.php`
**Linhas:** 32-34

**Problema:**
Os campos `is_checked_in`, `checked_in_at`, `checked_in_by` estão no `$fillable`, permitindo mass assignment.

**Solução:**
Remover os campos do `$fillable`.

**Critério de Aceitação:**
- [ ] `is_checked_in` não pode ser setado via `Guest::create()` ou `->fill()`
- [ ] Check-in continua funcionando via método dedicado

---

### 3.5 ISSUE-005: Validator GuestForm sem validação [P2]

**Severidade:** MÉDIO
**Arquivo:** `app/Filament/Validator/Resources/Guests/Schemas/GuestForm.php`

**Problema:**
O formulário do Validator não tem regras de validação completas.

**Solução:**
Adicionar `maxLength`, `placeholder`, `helperText` seguindo padrão PromoterGuestForm.

**Critério de Aceitação:**
- [ ] Todos campos com validação adequada
- [ ] Consistência com PromoterGuestForm

---

### 3.6 ISSUE-006: Unique constraint incompleto em monitores [P2]

**Severidade:** MÉDIO
**Arquivo:** `database/migrations/*update_monitores_add_document_fields.php`

**Problema:**
Constraint única em `[event_id, document_number]` mas deveria ser `[event_id, document_type, document_number]`.

**Solução:**
Criar nova migration para corrigir constraint.

**Critério de Aceitação:**
- [ ] Constraint única com 3 colunas
- [ ] Dados existentes migrados corretamente

---

## 4. Tasks para Execução

### TASK-001: [P0] Corrigir N+1 no AdminPromoterQuotaWidget
### TASK-002: [P1] Adicionar veiculo_id no VeiculosRelationManager repeater
### TASK-003: [P1] Adicionar authorization nos RelationManagers
### TASK-004: [P1] Corrigir mass assignment em Guest
### TASK-005: [P2] Adicionar validação no ValidatorGuestForm
### TASK-006: [P2] Corrigir unique constraint em monitores

---

## 5. Critérios de Go/No-Go para Deploy

- [ ] TASK-001: Widget carrega ≤5 queries
- [ ] TASK-002: Monitor com `veiculo_id` correto
- [ ] TASK-003: Authorization retorna 403 quando negado
- [ ] TASK-004: Mass assignment bloqueado
- [ ] TASK-005: Validação consistente
- [ ] TASK-006: Constraint única funcionando
- [ ] Todos E2E tests passando (20/20)
- [ ] Pint clean

---

## 6. Comandos de Referência

```bash
# Setup Sail (Docker)
alias sail='vendor/bin/sail'
sail artisan make:policy VeiculoPolicy --model=Veiculo
sail artisan make:policy MonitorPolicy --model=Monitor
sail artisan make:migration fix_monitores_unique_constraint --table=monitores

# Tests
sail artisan test
sail artisan pint

# Commit
git add . && git commit -m "feat(code-review): implement all P0/P1/P2 code review fixes"
```

---

**Documento criado para handoff. Aguardando implementação.**
