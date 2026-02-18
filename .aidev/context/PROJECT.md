# Contexto do Projeto - Guest List Pro

## Visão Geral

**Nome**: guest-list-pro  
**Stack**: Laravel 12 + Filament v4 + Livewire v3 + Tailwind v4  
**Tipo**: Sistema de Gestão de Eventos  
**Maturidade**: Brownfield

---

## Funcionalidades Principais

### 1. Sistema de Gestão de Convidados
- Cadastro com validação de duplicatas por documento
- Importação em massa via Excel (GuestsImport)
- Check-in com busca por similaridade (GuestSearchService)
- Normalização de documentos para comparações

### 2. Sistema de Aprovações
- Solicitações de inclusão de convidados (GuestInclusion)
- Check-in emergencial (EmergencyCheckin)
- Fluxo: Pending → Approved/Rejected
- Notificações automáticas para requesters

### 3. Bilheteria
- Venda de ingressos (Convite Amigo)
- Fechamento de caixa
- Controle por horário e operador

### 4. Painéis Filament
- Admin: Gestão completa do sistema
- Promoter: Gestão de convidados próprios
- Validator: Check-in e validação
- Bilheteria: Vendas e fechamento de caixa

---

## Stack Tecnológica

| Camada | Tecnologia |
|--------|------------|
| Backend | Laravel 12, PHP 8.5 |
| Frontend | Filament v4, Livewire v3, Alpine.js |
| Database | SQLite (dev), MySQL (prod) |
| Infrastructure | Docker/Sail |
| CSS | Tailwind v4, Design System Premium |

---

## Estrutura do Repositório

```
app/
├── Enums/                     # UserRole, EventStatus, RequestStatus, etc.
├── Filament/                  # Painéis, Resources, Widgets
│   ├── Admin/                # Painel Admin (Indigo)
│   ├── Promoter/            # Painel Promoter (Purple)
│   ├── Validator/          # Painel Validator (Emerald)
│   └── Bilheteria/          # Painel Bilheteria (Orange)
├── Models/                   # 8 entidades principais
├── Services/                 # GuestService, ApprovalRequestService, etc.
├── Observers/                # Activity logging
├── Livewire/                 # Componentes Livewire
└── Notifications/            # Notificações
```

---

## Models Principais

| Model | Tabela | Descrição |
|-------|--------|-----------|
| User | users | Usuários do sistema (Admin, Promoter, Validator, Bilheteria) |
| Event | events | Eventos gerenciados |
| Guest | guests | Convidados com documento normalizado |
| Sector | sectors | Setores de eventos |
| ApprovalRequest | approval_requests | Solicitações de aprovação |
| TicketSale | ticket_sales | Vendas de convite |
| CheckinAttempt | checkin_attempts | Tentativas de check-in |
| EventAssignment | event_assignments | Permissões de promotores |

---

## Services Críticos

| Service | Responsabilidade | Linhas |
|---------|-----------------|--------|
| GuestService | Lógica de convidados | ~200 |
| GuestSearchService | Busca por similaridade | ~150 |
| ApprovalRequestService | Fluxo de aprovações | ~576 |
| DocumentValidationService | Validação CPF/RG | ~100 |

---

## Métricas

### Codebase
| Métrica | Valor |
|---------|-------|
| Models | 8 entidades |
| Filament Resources | 10 resources |
| Services | 4 críticos |
| Widgets | 12+ |
| Migrations | 22 |
| Arquivos PHP | ~112 |

### Testes
| Métrica | Valor |
|---------|-------|
| Arquivos de teste | 7 |
| Total de testes | 46 |
| Cobertura | ~32% |

---

## Gaps de Testes (CRÍTICO)

### Priority 1 - CRÍTICO
- GuestService (0 testes)
- GuestsImport (0 testes)
- CheckinAttempt (0 testes)

### Priority 2 - IMPORTANTE
- DocumentValidationService (0 testes)
- GuestSearchService (poucos testes)

---

## Princípios Fundamentais

> **Documentação vem antes do código.**
> Nenhuma camada deve ser implementada sem o documento correspondente existir.

> **Mobile-first:** Usar `ViewColumn` para cards mobile, esconder colunas via `visibleFrom('md')`.

> **SPA desabilitado:** Manter `->spa()` desabilitado para evitar erros de JS.

---

## Decisões Arquiturais Importantes

1. **SPA Desabilitado**: Chart.js e Alpine.js apresentam bugs com SPA habilitado
2. **Normalização de Documentos**: Campos `*_normalized` para busca e unicidade
3. **Activity Logging**: Usar Spatie Activity Log
4. **SQLite em Dev**: MySQL apenas em produção

---

## Referências

- **Índice de Documentação**: `docs/CONSOLIDATED/INDEX.md`
- **Arquitetura**: `docs/CONSOLIDATED/architecture/system.md`
- **Stack**: `docs/CONSOLIDATED/stack/`
- **Workflows**: `docs/CONSOLIDATED/processes/workflows.md`

---

**Última atualização:** 2026-02-18
