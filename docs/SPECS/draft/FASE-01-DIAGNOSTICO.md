# FASE-01: Diagnóstico do Sistema — guest-list-pro

**Data:** 2026-04-17
**Analista:** Nando Dev (via OpenCode + DEVORQ)
**Projeto:** guest-list-pro

---

## 1. STACK TECNOLÓGICA

| Componente | Versão |
|------------|--------|
| PHP | 8.4.1 |
| Laravel | 12.47.0 |
| Filament | 4.5.3 |
| MySQL | 8.x |
| PHP Magic: | 12.5 |

### Infraestrutura

- Docker/Sail para desenvolvimento
- Laravel Sail para migrations
- Spatie ActivityLog para auditoria
- Laravel Sanctum para API tokens

---

## 2. MAPEAMENTO DE DOMÍNIO

### 2.1 Entidades Principais

```
┌─────────────────────────────────────────────────────────────────┐
│                            USER                                  │
│  Roles: Admin, Promoter, Validator, Bilheteria                  │
│  Relacionamentos:                                                │
│    - eventAssignments (EventAssignment[])                        │
│    - permissions (PromoterPermission[])                          │
│    - guests (Guest[])                                           │
│    - guestsValidated (Guest[])                                  │
└─────────────────────────────────────────────────────────────────┘
                              │
         ┌────────────────────┼────────────────────┐
         │                    │                    │
         ▼                    ▼                    ▼
┌─────────────┐      ┌─────────────┐      ┌─────────────┐
│    EVENT   │      │   GUEST     │      │   SECTOR   │
├─────────────┤      ├─────────────┤      ├─────────────┤
│ name       │      │ event_id   │      │ event_id    │
│ date       │      │ sector_id   │      │ name        │
│ location   │      │ promoter_id│      │ capacity    │
│ status     │      │ name       │      └─────────────┘
│ ticket_price│     │ document   │                │
└─────────────┘      │ email      │                │
                     │ is_checked │                │
                     │ checked_in │                │
                     └─────────────┘                │
                              │                    │
                              ▼                    │
                     ┌─────────────────┐          │
                     │  CHECKIN_ATTEMPT│          │
                     │  guest_id       │◄─────────┘
                     │  validator_id   │
                     │  success         │
                     └─────────────────┘
```

### 2.2 Fluxo de Dados

```
                    ┌──────────────┐
                    │   ADMIN      │
                    │ (criação)    │
                    └──────┬───────┘
                           │
                           ▼
              ┌────────────────────────┐
              │ EVENT + SECTORS       │
              │ (configuração)       │
              └──────────┬───────────┘
                         │
                         ▼
              ┌────────────────────────┐
              │ PROMOTER              │
              │ (cadastro guests)     │
              │ Limite por setor      │
              │ Janela de horário     │
              └──────────┬───────────┘
                         │
                         ▼
              ┌────────────────────────┐
              │ VALIDATOR             │
              │ (check-in)            │
              │ Modal/Slide-over      │
              └──────────┬───────────┘
                         │
                         ▼
              ┌────────────────────────┐
              │ BILHETERIA            │
              │ (vendas)             │
              │ TicketSales           │
              └──────────────────────┘
```

---

## 3. REGRAS DE NEGÓCIO IDENTIFICADAS

### 3.1 Regras por Role

| Role | Permissão |
|------|-----------|
| **ADMIN** | Acesso total a todos os recursos |
| **PROMOTER** | Cadastrar guests dentro do limite de setor |
| **VALIDATOR** | Realizar check-in de guests |
| **BILHETERIA** | Vender ingressos |

### 3.2 Regras de Check-in

```php
// GuestService.checkinByQrToken()
1. Validator deve ter role ADMIN ou VALIDATOR
2. Guest deve existir (busca por qr_token)
3. Guest não pode estar já checkado (is_checked_in = false)
```

### 3.3 Regras de Cadastro de Guest (Promoter)

```php
// GuestService.canRegisterGuest()
1. User deve ser PROMOTER ativo
2. Deve ter EventAssignment para o setor/evento
3. Horário deve estar dentro da janela (start_time <= now <= end_time)
4. Limite de guests não pode ser excedido (count < guest_limit)
```

### 3.4 Regras de Approval Request

```php
// ApprovalRequestService.approve()
1. Admin apenas pode aprovar
2. Admin não pode aprovar própria solicitação
3. Não pode existir guest no mesmo setor com mesmo documento
```

---

## 4. PROBLEMAS ESTRUTURAIS IDENTIFICADOS

### 4.1 Alta Prioridade

| # | Problema | Local | Impacto |
|---|----------|-------|---------|
| P1 | **Duplicação de validação de limite** | GuestService vs ApprovalRequestService | Manutenção difícil |
| P2 | **Validações em múltiplos lugares** | GuestService, GuestPolicy, Controller | Risco de inconsistência |
| P3 | **Guest sem +1 (acompanhante)** | Model Guest não suporta parent_id | Feature incompleta |
| P4 | **Modal quebrado no validador** | ValidatorResource | UX crítica |

### 4.2 Média Prioridade

| # | Problema | Local | Impacto |
|---|----------|-------|---------|
| P5 | **Pagamentos sem tipos flexíveis** | TicketSale table | Rigidez para novos tipos |
| P6 | **Pagamentos múltiplos não suportados** | TicketSale | Só 1 pagamento por venda |
| P7 | **Sem métricas por setor em relatórios** | BilheteriaResource | Visibilidade limitada |
| P8 | **Check-in não automático** | Bilheteria → Validator | Fluxo manual |

### 4.3 Baixa Prioridade

| # | Problema | Local | Impacto |
|---|----------|-------|---------|
| P9 | **Dados pessoais exigidos na bilheteria** | TicketSale | LGPD compliance |
| P10 | **Relatórios sem filtros avançados** | AdminDashboard | Análise limitada |

---

## 5. ANÁLISE DE ACOPLAMENTO

### 5.1 Serviços (app/Services/)

| Serviço | Responsabilidade | Avaliação |
|---------|-----------------|-----------|
| GuestService | Check-in, canRegister, getAuthorized | ✅ Coeso |
| GuestSearchService | Busca normalizada/fuzzy | ✅ Coeso |
| DuplicateGuestValidator | Validação de duplicatas | ✅ Coeso |
| ApprovalRequestService | Lifecycle de aprovações | ⚠️ Acoplado com Guest |
| DocumentValidationService | Validação de documentos | ✅ Reutilizável |
| AuthenticationService | Auth logic | ✅ Isolado |

### 5.2 Camada de Validação

**Problema:** Validações de limite e horário estão em `GuestService` mas também existem regras similares em `ApprovalRequestService`.

**Solução Proposta:** Criar `Rules/GuestLimitRule.php` e `Rules/TimeWindowRule.php` centralizados.

---

## 6. RISOS DE ALTERAÇÃO

| Refatoração | Risco | Impacto | Mitigação |
|-------------|-------|---------|------------|
| Adicionar +1 a Guest | Alto | Breaking se mudar schema | Migration separada |
| Mover validações para Rules | Médio | Pode quebrar fluxos | Tests primeiro |
| Pagamentos múltiplos | Alto | Mudança em TicketSale | Spec separada |

---

## 7. SUGESTÕES PRÉ-FEATURES

### 7.1 Antes de Implementar FASE-03 (Admin)

- [ ] Criar `Rules/GuestLimitRule.php` centralizando `canRegisterGuest`
- [ ] Criar `Rules/TimeWindowRule.php` para validação de horário
- [ ] Migrar validações existentes para usar as Rules

### 7.2 Antes de Implementar FASE-06 (Bilheteria)

- [ ] Definir schema de `ticket_types`
- [ ] Criar `PaymentSplit` model se pagamentos múltiplos
- [ ] Spec separada para pagamentos

### 7.3 Antes de Implementar +1

- [ ] Adicionar `parent_id` em guests table
- [ ] Criar `GuestPlusOneService`

---

## 8. RESUMO

| Métrica | Valor |
|---------|-------|
| Models | 9 |
| Services | 6 |
| Policies | 2 |
| Filament Resources | 8 |
| Enums | 6 |

### Pontos Fortes
- Service Layer bem definido
- Policies implementadas (SPEC-0001)
- ActivityLog para auditoria

### Pontos Fracos
- Duplicação de validação de limite
- Schema rígido para pagamentos
- Sem suporte a +1 (acompanhantes)

---

## 9. PRÓXIMOS PASSOS

1. **FASE-02 (Refatoração):** Criar camada de Rules centralizadas
2. **FASE-03 (Admin):** Implementar configurações dinâmicas
3. **FASE-05 (Validator):** Corrigir modal → slide-over
4. **FASE-06 (Bilheteria):** Spec separada para pagamentos

---

**Documento gerado por:** OpenCode + DEVORQ Context-Mode
**Data:** 2026-04-17
