---
id: SPEC-0005-19-04-2026
title: Ticket Pricing por Setor — Preços Variados por Setor
domain: feature
status: implemented
priority: high
author: Nando Dev
owner: team-core
source: SPEC-PERM-P3.1
created_at: 2026-04-19
updated_at: 2026-04-21
related_files:
  - app/Models/TicketType.php
  - app/Models/Sector.php
  - app/Models/TicketSale.php
  - app/Models/TicketTypeSector.php
  - app/Services/TicketSaleService.php
  - app/Filament/Bilheteria/Resources/TicketSales/Schemas/TicketSaleForm.php
  - app/Filament/Resources/TicketType/Schemas/TicketTypeForm.php
  - database/migrations/2026_04_19_041341_create_ticket_type_sector_table.php
  - database/migrations/2026_04_21_161945_update_ticket_types_remove_price_add_is_visible.php
related_tasks:
  - P3.1
---

# SPEC-0005: Ticket Pricing por Setor

**Versão:** 2.0
**Data:** 2026-04-19
**Data Atualização:** 2026-04-21
**Status:** Implementado
**Prioridade:** Alta

---

## 1. Objetivo

Permitir que o administrador defina **preços variados por setor** para cada tipo de ingresso em um evento.

**Arquitetura:**
- TicketType é apenas um "molde" (ex: "1º Lote", "2º Lote") **sem preço próprio**
- O preço só existe na combinação TicketType + Setor (`ticket_type_sector`)
- Admin configura visibilidade (`is_visible`) para ativar/desativar tipos na bilheteria

---

## 2. Arquitetura Atual

### 2.1 Modelos

```
TicketType
├── id
├── event_id
├── name
├── description
├── is_active (boolean)
├── is_visible (boolean) ← NOVO
└── timestamps

Sector
├── id
├── event_id
├── name
└── capacity

TicketTypeSector (pivot)
├── id
├── ticket_type_id (FK)
├── sector_id (FK)
├── price (preço para este tipo+setor)
└── timestamps

TicketSale
├── id
├── event_id
├── ticket_type_id (FK)
├── sector_id (FK)
├── guest_id (FK)
├── sold_by (FK)
├── value (preço vendido)
├── payment_method
├── buyer_name
├── buyer_document
└── ...
```

### 2.2 Lógica de Preço

```php
// TicketSaleService
public static function getPriceForSector(TicketType $ticketType, int $sectorId): float
{
    $sectorPrice = TicketTypeSector::where('ticket_type_id', $ticketType->id)
        ->where('sector_id', $sectorId)
        ->first();

    if (! $sectorPrice) {
        throw new \RuntimeException(
            "Preço não configurado para o tipo '{$ticketType->name}' no setor #{$sectorId}"
        );
    }

    return (float) $sectorPrice->price;
}
```

---

## 3. Fluxo na Bilheteria

```
1. Selecionar SETOR primeiro
2. Listar tipos que:
   - is_visible = true
   - Têm configuração de preço para o setor selecionado
3. Selecionar TIPO
4. Preço aparece automaticamente (ticket_type_sector.price)
```

---

## 4. Admin UI

### 4.1 TicketTypeForm

```
┌─────────────────────────────────────────────────────────────┐
│  Informações Básicas                                        │
├─────────────────────────────────────────────────────────────┤
│  Evento: [Festival 2026      ▼]                           │
│  Nome:   [1º Lote            ]                             │
│  Descrição: [Descrição...     ]                             │
│  Ativo:     [●]                    Visível: [●]           │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│  Preços por Setor (obrigatório)                             │
├─────────────────────────────────────────────────────────────┤
│  Setor      │ Preço                                        │
│  [Pista  ▼] │ [R$ 150,00    ]  [+ adicionar]             │
│  [VIP     ▼] │ [R$ 250,00    ]                             │
│  [Camarote▼] │ [R$ 400,00    ]                             │
│  [Backstage▼] │ [R$ 300,00    ]                             │
└─────────────────────────────────────────────────────────────┘
```

---

## 5. Requisitos Funcionais

| Código | Descrição | Prioridade |
|--------|-----------|------------|
| RF01 | TicketType não tem preço próprio | Alta |
| RF02 | Preço existe apenas em TicketTypeSector | Alta |
| RF03 | Admin configura preços por setor (obrigatório) | Alta |
| RF04 | Admin define visibilidade (is_visible) | Alta |
| RF05 | Bilheteria filtra tipos visíveis por setor | Alta |
| RF06 | Preço mostrado automaticamente após seleção | Alta |

---

## 6. Status de Implementação

| Fase | Status | Observação |
|------|--------|-------------|
| 1. Migration ticket_type_sector | ✅ | |
| 2. Migration update ticket_types | ✅ | Remove price, adiciona is_visible |
| 3. Model TicketTypeSector | ✅ | |
| 4. Model TicketType | ✅ | is_visible, scopeVisible() |
| 5. Admin UI | ✅ | Preços por setor obrigatório, is_visible toggle |
| 6. TicketSaleService | ✅ | Lança exceção se preço não encontrado |
| 7. Bilheteria UI | ✅ | Setor primeiro, filtra tipos por setor |

---

## 7. Notas de Implementação

### 2026-04-21: Refatoração Completa

Nova arquitetura implementada com as seguintes mudanças:

**Migration:**
- `2026_04_21_161945_update_ticket_types_remove_price_add_is_visible.php`
  - Remove coluna `price` da tabela `ticket_types`
  - Adiciona coluna `is_visible` (boolean, default true)

**Models:**
- `TicketType`: Remove `price`, adiciona `is_visible`, `scopeVisible()`
- `TicketTypeSector`: Mantém configuração de preço por setor

**Admin UI (TicketTypeForm):**
- Remove campo "Preço Padrão"
- "Preços por Setor" agora é obrigatório (minItems: 1)
- Adiciona toggle "Visível na Bilheteria"

**TicketSaleService:**
- Remove fallback `?? $ticketType->price`
- Lança `\RuntimeException` se preço não configurado

**Bilheteria UI (TicketSaleForm):**
- Inverte ordem: Setor PRIMEIRO, depois Tipo
- Filtra TicketTypes por `is_visible=true` E que tenham preço para setor
- Preço calculado via `getPriceForSector()`

---

## 8. Referências

- [SPEC-PERM - P3.1](./SPEC-PERM-17-04-2026-permissions-design.md)
- [TicketType model](./app/Models/TicketType.php)
- [TicketSaleForm](./app/Filament/Bilheteria/Resources/TicketSales/Schemas/TicketSaleForm.php)
- [TicketTypeForm](./app/Filament/Resources/TicketType/Schemas/TicketTypeForm.php)