---
id: SPEC-0002-17-04-2026
title: Agente de Refatoração e Evolução — guest-list-pro
domain: arquitetura
status: in_progress
priority: high
author: Nando Dev
owner: team-core
source: prompt
created_at: 2026-04-17
updated_at: 2026-04-17
related_files: []
related_tasks: []
---

# SPEC-0002: Agente de Refatoração e Evolução — guest-list-pro

**Versão:** 1.1
**Data:** 2026-04-17
**Status:** Draft
**Prioridade:** Alta

---

## 1. Objetivo

Transformar o prompt de Agente de Refatoração em uma spec formal para guiar o desenvolvimento de melhorias no guest-list-pro, seguindo processo estruturado DEVORQ.

---

## 2. Escopo

### 2.1 Escopo Incluído

| ID | Área | Feature | Prioridade |
|----|------|---------|------------|
| FASE-01 | Análise | Diagnóstico do sistema atual | ✅ Concluído |
| FASE-02 | Refatoração | Centralização de regras de negócio | ✅ Concluído |
| FASE-03 | Admin | Configuração de validações dinâmicas | ✅ Concluído |
| FASE-04 | Admin | Check-in direto na lista | ✅ Concluído |
| FASE-05 | Admin | Configuração de promoter | ✅ Concluído |
| FASE-06 | Promoter | Convidado +1 (acompanhante) | ✅ Concluído |
| FASE-07 | Promoter | Revisão de validações reutilizáveis | ✅ Concluído |
| FASE-08 | Promoter | Reorganização UX/UI | ✅ Concluído |
| FASE-09 | Validador | Substituir modal por slide-over | ✅ Concluído |
| FASE-10 | Bilheteria | Check-in automático (venda→valida) | ✅ Concluído |
| FASE-11 | Bilheteria | Tipos de ingressos flexíveis | ✅ Concluído |
| FASE-12 | Bilheteria | Remover dados pessoais comprador | ✅ Concluído |
| FASE-13 | Bilheteria | Pagamentos múltiplos (dinheiro+cartão+pix) | ✅ Concluído |
| FASE-14 | Bilheteria | Listagem com tipo pagamento e setor | ✅ Concluído |
| FASE-15 | Bilheteria | Relatórios melhorados | ✅ Concluído |
| FASE-16 | Bilheteria | Métricas por setor | ✅ Concluído |
| FASE-17 | Dashboard | Gráfico de vendas por horário | ✅ Concluído |
| FASE-18 | Relatórios | Filtros por setor/promoter | ✅ Concluído |
| FASE-19 | Relatórios | Relatório individual por tipo | ✅ Concluído |

### 2.2 Escopo Excluído

- Migração para microservices
- Reescrita completa da stack
- Implementação de testes E2E automatizados

---

## 3. FASE-01: DIAGNÓSTICO DO SISTEMA

### 3.1 Stack Tecnológica

| Componente | Versão |
|------------|--------|
| PHP | 8.4.1 |
| Laravel | 12.47.0 |
| Filament | 4.5.3 |
| MySQL | 8.x |

### 3.2 Entidades Principais

```
User (roles: Admin, Promoter, Validator, Bilheteria)
  ├── EventAssignment[]
  ├── PromoterPermission[]
  ├── Guest[] (criados)
  └── Guest[] (validados)

Event
  ├── Sector[]
  └── Guest[]

Guest
  ├── Sector
  ├── Promoter (creator)
  ├── Validator (check-in)
  └── CheckinAttempt[]

TicketSale
  └── (tabela única, sem suporte a múltiplos pagamentos)
```

### 3.3 Regras de Negócio Identificadas

| Regra | Local | Descrição |
|-------|-------|-----------|
| Limite de guests por setor | GuestService::canRegisterGuest() | Promoter tem limite configurado |
| Janela de horário | GuestService::canRegisterGuest() | start_time <= now <= end_time |
| Check-in via QR | GuestService::checkinByQrToken() | Validador ou Admin |
| Aprovação por Admin | ApprovalRequestService::approve() | Admin não pode aprovar própria request |

### 3.4 Problemas Estruturais

| # | Problema | Prioridade | Solução Proposta |
|---|----------|------------|------------------|
| P1 | Duplicação de validação de limite | Alta | Rules/GuestLimitRule.php |
| P2 | Validações em múltiplos lugares | Alta | Centralizar em Rules/ |
| P3 | Guest sem +1 | Alta | Adicionar parent_id |
| P4 | Modal quebrado no validador | Alta | Slide-over |
| P5 | Schema rígido para pagamentos | Alta | TicketType flexível |
| P6 | Pagamentos múltiplos não suportados | Alta | PaymentSplit |
| P7 | Sem métricas por setor | Média | Queries agregadas |
| P8 | Check-in manual na bilheteria | Alta | Automação via Observer |

### 3.5 Pré-requisitos para Próximas Fases

- [x] FASE-01: Diagnóstico (COMPLETO)
- [ ] FASE-02: Criar Rules/ centralizadas
- [ ] FASE-09: Corrigir modal → slide-over
- [ ] Spec separada para FASE-06 (pagamentos)

---

## 4. Fases de Implementação

### FASE-02: Refatoração (Pré-features)

**Objetivo:** Criar camada de regras centralizada.

#### 4.2.1 Estrutura Proposta

```
app/
├── Rules/
│   ├── GuestLimitRule.php        # NOVO
│   ├── TimeWindowRule.php        # NOVO
│   ├── CheckinRule.php          # NOVO
│   └── PlusOneRule.php         # NOVO (para FASE-06)
├── Services/                     # EXISTS
├── Validators/                  # AVALIAR
└── Policies/                   # EXISTS (SPEC-0001)
```

#### 4.2.2 Regras a Centralizar

| Regra | Localização Atual | Localização Proposta |
|-------|------------------|---------------------|
| Limite de convidados | GuestService::canRegisterGuest | Rules/GuestLimitRule |
| Validação de horário | GuestService::canRegisterGuest | Rules/TimeWindowRule |
| Regras de check-in | GuestService::checkinByQrToken | Rules/CheckinRule |

---

## 5. Estimativas

| Fase | Estimativa | Complexidade |
|------|------------|--------------|
| FASE-01 (Diagnóstico) | ✅ Concluído | - |
| FASE-02 (Refatoração) | ✅ Concluído (8h) | Alta |
| FASE-03 (Admin) | 6h | Média |
| FASE-04 (Promoter) | 8h | Alta |
| FASE-05 (Validador) | 2h | Baixa |
| FASE-06 (Bilheteria) | 16h | Alta |
| FASE-07 (Dashboard) | 4h | Média |
| FASE-08 (Relatórios) | 6h | Média |
| **TOTAL** | **50h** | - |

---

## 6. Riscos e Mitigações

| Risco | Probabilidade | Impacto | Mitigação |
|-------|-------------|---------|-----------|
| Duplicação de regras Admin/Promoter | Alta | Alto | Centralizar em Rules/ antes de features |
| Breaking changes em validation | Alta | Alto | Tests antes de cada feature |
| Complexidade de pagamentos múltiplos | Média | Alto | Spec detalhada de Payment antes de implementar |
| Performance com grandes volumes | Média | Médio | Lazy loading + pagination |

---

## 7. Critérios de Aceite Gerais

### Definition of Done (DoD)

- [x] FASE-01 (Diagnóstico) completo com diagnóstico ✅
- [x] FASE-02 (Refatoração) validada
- [ ] Todas as features com tests
- [ ] No regression em fluxos existentes
- [ ] Code review aprovado

### Definition of Ready (DoR)

- [x] Domínio mapeado ✅
- [x] Regras de negócio documentadas ✅
- [ ] Estrutura de pastas definida
- [ ] Rules centralizadas implementadas

---

## 8. Entregáveis

1. ✅ Diagnóstico do sistema atual (FASE-01)
2. ✅ Plano de refatoração (FASE-02)
   - app/Rules/GuestLimitRule.php
   - app/Rules/TimeWindowRule.php
   - app/Rules/CheckinRule.php
   - GuestService.php refatorado
3. [ ] Estrutura proposta (pastas/camadas)
4. [ ] Implementação das features (por fase)
5. [ ] Migrations necessárias
6. [ ] Sugestões de melhorias futuras

---

## 9. Referências

- [SPEC-0001 — Code Review Fixes](./implemented/SPEC-0001-07-04-2026-code-review-fixes.md)
- [Diagnóstico Completo](./FASE-01-DIAGNOSTICO.md)
