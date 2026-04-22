---
id: SPEC-0006-21-04-2026
title: Sistema de Estorno de Vendas
domain: feature
status: implemented
priority: high
author: Nando Dev
owner: team-core
created_at: 2026-04-21
updated_at: 2026-04-22
related_tasks:
  - ESTORNO-001
---

# SPEC-0006: Sistema de Estorno de Vendas

**Versão:** 1.0
**Data:** 2026-04-21
**Status:** Implementado
**Prioridade:** Alta

---

## 1. Objetivo

Permitir que operadores da bilheteria solicitem estorno de vendas incorretas, com workflow de aprovação pelo admin para fins de auditoria.

---

## 2. Fluxo de Trabalho

```
1. Bilheteria identifica venda incorreta
2. Bilheteria: Solicita estorno (motivo obrigatório)
3. Admin: Visualiza solicitação pendente
4. Admin: Aprova ou rejeita (ciência)
5. Se aprovado: Sale marcada como estornada
6. Bilheteria: Pode realizar nova venda se necessário
```

---

## 3. Arquitetura

### 3.1 Campos no TicketSale

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `is_refunded` | boolean | Se a venda foi estornada |
| `refunded_at` | datetime | Data/hora do estorno |
| `refunded_by` | FK (user) | Admin que aprovou |
| `refund_reason` | string | Motivo do estorno |

### 3.2 Tabela refund_requests

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `id` | bigint | PK |
| `ticket_sale_id` | FK | Venda solicitada |
| `requester_id` | FK | Quem solicitou |
| `reason` | text | Motivo (obrigatório) |
| `status` | enum | pending/approved/rejected |
| `reviewer_id` | FK | Admin que revisou |
| `reviewed_at` | datetime | Data da revisão |
| `review_notes` | text | Observações do admin |

### 3.3 Model TicketSale

```php
// Scopes
TicketSale::notRefunded()  // Exclui estornadas
TicketSale::refunded()      // Só estornadas

// Atributos
$-sale->is_refunded
$-sale->refunded_at
$-sale->refunded_by
$sale->refund_reason
```

### 3.4 Model RefundRequest

Similar ao ApprovalRequest:
- `status` enum: pending, approved, rejected
- Relacionamentos: ticketSale, requester, reviewer
- Service: RefundRequestService

---

## 4. Requisitos Funcionais

| Código | Descrição | Prioridade |
|---------|-----------|------------|
| RF01 | Bilheteria pode solicitar estorno com motivo obrigatório | Alta |
| RF02 | Admin vê lista de solicitações pendentes | Alta |
| RF03 | Admin pode aprovar/rejeitar com observações | Alta |
| RF04 | Venda estornada não aparece no fechamento de caixa | Alta |
| RF05 | Venda estornada fica visível com badge "Estornado" | Alta |
| RF06 | Motivo fica registrado para auditoria | Alta |

---

## 5. UI - CashClosing

### 5.1 Botão Solicitar Estorno

Na lista de vendas do CashClosing, cada venda (se não estornada) terá botão:
```
[Estornar] → Abre modal com campo reason (obrigatório)
```

### 5.2 Indicador Visual

Vendas estornadas:
- Badge vermelho "ESTORNADO"
- Linha riscada ou acinzentada
- Valor não conta no total

---

## 6. UI - Admin

### 6.1 RefundRequestResource

Listagem em `/admin/refund-requests`:
- Lista todas as solicitações pendentes
- Filtros por status, data, requester
- Ações: Aprovar, Rejeitar

### 6.2 Detalhes da Solicitação

```
Solicitante: João (Bilheteria)
Venda #: 123
Comprador: Maria Silva
Valor: R$ 150,00
Motivo: Forma de pagamento incorreta (cliente pagou PIX mas registrei como Dinheiro)

[Aprovar] [Rejeitar]
```

---

## 7. Service

### RefundRequestService

```php
// Criar solicitação de estorno
RefundRequestService::create(TicketSale $sale, User $requester, string $reason): RefundRequest

// Aprovar estorno
RefundRequestService::approve(RefundRequest $request, User $admin, ?string $notes): RefundRequest

// Rejeitar estorno
RefundRequestService::reject(RefundRequest $request, User $admin, ?string $notes): RefundRequest
```

**Approval:**
1. Marca RefundRequest como approved
2. Marca TicketSale como is_refunded=true
3. Preenche refunded_at, refunded_by, refund_reason

**Rejection:**
1. Marca RefundRequest como rejected
2. Sale permanece inalterada

---

## 8. Queries Afetadas

### CashClosing
```php
// Antes
TicketSale::query()

// Depois
TicketSale::notRefunded()->query()
```

### Widgets/Relatórios
Todos os relatórios de vendas devem usar `notRefunded()`.

---

## 9. Status de Implementação

| Fase | Status | Observação |
|------|--------|-------------|
| 1. Migration TicketSale | ✅ | Adicionados: is_refunded, refunded_at, refunded_by, refund_reason |
| 2. Migration RefundRequest | ✅ | Tabela com status enum (pending/approved/rejected) |
| 3. Model TicketSale | ✅ | Scopes: notRefunded(), refunded(), withRefundRequest() |
| 4. Model RefundRequest | ✅ | Relacionamentos: ticketSale, requester, reviewer |
| 5. RefundRequestService | ✅ | createRefundRequest, approve, reject |
| 6. RefundRequestResource | ✅ | Admin em /admin/refund-requests |
| 7. CashClosing updates | ✅ | Filtro notRefunded() aplicado |
| 8. Visual estornado | ✅ | Badge "Estornado" em mobile_card e desktop |

---

## 10. Pré-requisitos

- TicketSale existente
- User model com roles
- Admin panel existente

---

## 11. Riscos e Mitigações

| Risco | Probabilidade | Impacto | Mitigação |
|-------|---------------|---------|-----------|
| Venda estornada aparece em relatórios | Baixa | Alto | Verificar scopes em todas as queries |
| Admin esquece de aprovar | Baixa | Médio | Notificação push |
| Double refund | Baixa | Alto | Verificar se já existe request pendente |

---

## 12. Notas de Implementação

### 12.1 Decisões de Design

- **Guest não é removido após estorno**: O guest permanece no sistema para auditoria. Apenas a venda é marcada como estornada.
- **Notifications**: Notificação enviada ao admin quando nova solicitação é criada (NewRefundRequestNotification). Notificação enviada ao solicitante quando status muda (RefundRequestStatusNotification).
- **Filtro has_refund**: Adicionado filtro na lista de vendas para ver vendas estornadas ou com estorno pendente.

### 12.2 Issues Resolvidos

- **Login redirect**: Rota `/login` com middleware 'guest' não funcionava no Laravel 11+. Removido o middleware.
- **RefundStatus enum handling**: No mobile_card do Admin, `$getState()->status` é um enum `RefundStatus`, não string. Usar `$getState()->status->value` e `$getState()->status->getLabel()`.
- **recordActions visibility**: Actions definidas com `extraAttributes(['class' => 'hidden md:inline-flex'])` para aparecer só no desktop. Mobile usa botões manuais no mobile_card.blade.php.
