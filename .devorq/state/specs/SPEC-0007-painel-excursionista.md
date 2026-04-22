# SPEC-0007 — Painel Excursionista

**Status:** DRAFT — Aguardando GATE-1 (aprovação do Nando)
**Criada em:** 2026-04-22
**Branch:** `feat/SPEC-0007-excursionista`

---

## 1. Contexto e Motivação

O sistema atual possui painéis para Admin, Promoter, Validator e Bilheteria. Organizadores de excursões (caravanas de ônibus/vans) precisam de um painel dedicado para:

- Cadastrar suas excursões vinculadas a eventos
- Registrar os veículos de cada excursão (ônibus, vans)
- Vincular um monitor responsável a cada veículo

O Admin cria um usuário com role `EXCURSIONISTA` e o atribui a um evento via `EventAssignment`. O excursionista então acessa o painel, seleciona o evento e gerencia seus monitores.

---

## 2. Requisitos Funcionais

### RF-01 — Novo Role
- Adicionar `EXCURSIONISTA` ao enum `UserRole`
- Excursionista acessa apenas o painel `/excursionista`
- Admin pode criar usuários excursionistas e atribuí-los a eventos

### RF-02 — Entidades Novas

#### Excursão (`excursoes`)
| Campo | Tipo | Regras |
|-------|------|--------|
| id | bigint PK | auto |
| event_id | FK events | obrigatório |
| nome | string(150) | obrigatório |
| criado_por | FK users | obrigatório (excursionista) |
| created_at / updated_at | timestamps | auto |

#### Veículo (`veiculos`)
| Campo | Tipo | Regras |
|-------|------|--------|
| id | bigint PK | auto |
| excursao_id | FK excursoes | obrigatório |
| tipo | enum(ONIBUS, VAN) | obrigatório |
| placa | string(10) | opcional |
| created_at / updated_at | timestamps | auto |

#### Monitor (`monitores`)
| Campo | Tipo | Regras |
|-------|------|--------|
| id | bigint PK | auto |
| veiculo_id | FK veiculos | obrigatório |
| event_id | FK events | obrigatório (denormalizado para queries) |
| nome | string(150) | obrigatório |
| cpf | string(14) | obrigatório, formato 000.000.000-00 |
| criado_por | FK users | obrigatório |
| created_at / updated_at | timestamps | auto |

### RF-03 — Painel Excursionista (`/excursionista`)
- Seleção de evento obrigatória (mesmo fluxo dos outros painéis via `EnsureEventSelected`)
- **Dashboard** com contadores: excursões, veículos, monitores cadastrados
- **Resource: Monitores** — operações: listar, criar, editar, deletar
  - Filtros: por excursão, por tipo de veículo
  - Formulário de criação:
    - Nome completo do monitor
    - CPF (com máscara)
    - Select de Excursão (dropdown das excursões do evento) + botão inline "Nova Excursão" → abre modal
    - Select de Veículo (dropdown dos veículos da excursão selecionada, reativo)
- **Resource: Excursões** — operações: listar, criar, editar, deletar
  - Veículos como sub-resource (Repeater ou relação HasMany)

### RF-04 — Painel Admin (extensão)
- `ExcursionastaResource` → gerenciar usuários excursionistas (role filter)
- `EventAssignment` já suporta roles dinâmicas — adicionar `excursionista` como opção válida
- Resource de Monitores e Excursões visível para Admin (read-only ou gerencial)

### RF-05 — Fluxo de Criação de Excursão Inline
- No formulário de Monitor, se a excursão desejada não existe na lista:
  - Botão "+" abre modal Filament via `CreateAction` em `Select` field
  - Usuário preenche nome da excursão + veículos
  - Após criar, o Select volta com a nova excursão selecionada automaticamente

---

## 3. Requisitos Não-Funcionais

- Seguir os padrões dos painéis existentes (Promoter como referência principal)
- CPF armazenado sem formatação no banco, exibido com máscara no frontend
- `EnsureEventSelected` middleware aplicado ao painel excursionista
- Activity logging nas entidades novas (Spatie)
- Factories e seeder para ambiente de desenvolvimento

---

## 4. Fora do Escopo (SPEC-0007)

- Check-in de monitores
- Relatórios de excursões para Admin
- Integração com sistema de ingressos (TicketSales)
- Aprovação de monitores pelo Admin

---

## 5. Estrutura de Arquivos

```
app/
├── Enums/
│   └── UserRole.php                          # MODIFICAR — add EXCURSIONISTA
│   └── TipoVeiculo.php                       # CRIAR
├── Models/
│   ├── Excursao.php                           # CRIAR
│   ├── Veiculo.php                            # CRIAR
│   └── Monitor.php                            # CRIAR
├── Providers/Filament/
│   └── ExcursionistaPanelProvider.php        # CRIAR
├── Filament/Excursionista/
│   ├── Pages/
│   │   ├── Dashboard.php                      # CRIAR
│   │   └── SelectEvent.php                    # CRIAR (extends SelectEventBase)
│   ├── Resources/
│   │   ├── MonitorResource/
│   │   │   ├── MonitorResource.php            # CRIAR
│   │   │   ├── Pages/
│   │   │   │   ├── ListMonitores.php          # CRIAR
│   │   │   │   ├── CreateMonitor.php          # CRIAR
│   │   │   │   └── EditMonitor.php            # CRIAR
│   │   │   ├── Schemas/
│   │   │   │   └── MonitorForm.php            # CRIAR
│   │   │   └── Tables/
│   │   │       └── MonitoresTable.php         # CRIAR
│   │   └── ExcursaoResource/
│   │       ├── ExcursaoResource.php           # CRIAR
│   │       └── Pages/
│   │           ├── ListExcursoes.php          # CRIAR
│   │           ├── CreateExcursao.php         # CRIAR
│   │           └── EditExcursao.php           # CRIAR
│   └── Widgets/
│       └── ExcursaoStatsWidget.php            # CRIAR
database/
├── migrations/
│   ├── YYYY_create_excursoes_table.php        # CRIAR
│   ├── YYYY_create_veiculos_table.php         # CRIAR
│   └── YYYY_create_monitores_table.php        # CRIAR
└── seeders/
    └── ExcursionistaSeeder.php                # CRIAR
resources/css/filament/excursionista/
└── theme.css                                  # CRIAR
bootstrap/
└── providers.php                              # MODIFICAR — add ExcursionistaPanelProvider
```

---

## 6. Gates de Qualidade

| Gate | Critério |
|------|----------|
| GATE-1 | SPEC aprovada pelo Nando ← **VOCÊ ESTÁ AQUI** |
| GATE-2 | Pre-Flight: `sail artisan test --compact` verde, migrations OK |
| GATE-3 | Quality Gate: Pint limpo, E2E smoke tests passando |

---

## 7. Critérios de Aceite

- [ ] Usuário com role `EXCURSIONISTA` faz login e acessa `/excursionista`
- [ ] Seleciona evento e vê dashboard com contadores zerados
- [ ] Cria excursão com veículo(s) e retorna à lista
- [ ] Cria monitor vinculado a veículo existente
- [ ] Cria monitor com criação inline de excursão via modal
- [ ] Admin vê excursionistas na tela de `EventAssignment`
- [ ] Todos os testes unitários passam
- [ ] E2E smoke tests passam (27+)
