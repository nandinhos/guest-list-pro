# Gestão de Excursionistas — Design

**Objetivo:** Transformar a página de relatório nominal em uma central de gestão completa (CRUD) para excursões, veículos e monitores, tornando-a a ferramenta oficial de administração de excursionistas.

---

## Contexto

A página `/admin/reports/excursoes` existe como relatório de auditoria (somente leitura). O usuário quer que ela evolua para uma ferramenta de gestão, com criação, edição e exclusão de registros diretamente nessa tela, sem precisar navegar para os recursos individuais.

---

## Arquitetura

### Abordagem: Página única com aba ativa + HasTable dinâmico

Uma única página Filament (`ExcursoesGestao`) com:
- **Seletor de evento** no topo (filtro global, reativo)
- **3 abas** (Excursões, Veículos, Monitores) controladas por `$activeTab`
- **1 tabela Filament** (`HasTable`) que troca query, colunas e ações conforme a aba ativa
- **Modais de criar/editar** via `CreateAction` e `EditAction` do Filament (formulários inline no modal)
- **Exclusão com confirmação** via `DeleteAction`

Essa abordagem mantém tudo em um único componente Livewire, segue os padrões do projeto e evita complexidade de componentes aninhados.

### Página que será substituída

A página `ExcursoesReport` (`app/Filament/Admin/Pages/ExcursoesReport.php`) será renomeada/convertida para `ExcursoesGestao`, mantendo o mesmo slug `reports/excursoes` para não quebrar bookmarks.

---

## Componentes

### `app/Filament/Admin/Pages/ExcursoesGestao.php`

Propriedades:
- `$selectedEventId` — evento selecionado (persiste em sessão)
- `$activeTab` — aba ativa: `'excursoes'` | `'veiculos'` | `'monitores'`

Métodos principais:
- `getTableQuery()` — retorna query diferente por aba
- `getTableColumns()` — retorna colunas diferentes por aba
- `getTableHeaderActions()` — retorna `CreateAction` configurado por aba
- `switchTab($tab)` — troca a aba e chama `$this->resetTable()`

Traits necessários: `HasTable`, `InteractsWithTable`, `HasActions`, `InteractsWithActions`

### `resources/views/filament/admin/pages/excursoes-gestao.blade.php`

Layout:
1. Filtro de evento (Select reativo)
2. Cabeçalho das abas com contadores (Excursões N / Veículos N / Monitores N)
3. `{{ $this->table }}` — tabela Filament renderizada

---

## CRUD por entidade

### Excursões
- **Criar:** modal com campo `nome` (obrigatório)
- **Editar:** modal com campo `nome`
- **Excluir:** confirmação; cascade deleta veículos e monitores (já configurado na migration)
- **Colunas:** Nome · Responsável · Veículos (badge) · Monitores (badge) · Criado em

### Veículos
- **Criar:** modal com `tipo` (Select: Ônibus/Microônibus/Van) + `placa` (opcional) + `excursao_id` (Select filtrado pelo evento)
- **Editar:** mesmos campos
- **Excluir:** confirmação; cascade deleta monitores vinculados
- **Colunas:** Tipo · Placa · Excursão · Monitores (badge) · Criado em

### Monitores
- **Criar:** modal com `nome` + `document_type` (Select) + `document_number` + `veiculo_id` (opcional, Select filtrado pelo evento)
- **Editar:** mesmos campos
- **Excluir:** confirmação simples
- **Colunas:** Nome · Documento · Veículo · Excursão · Responsável · Criado em

---

## Campos auto-preenchidos (não aparecem no formulário)

- `event_id` → `$this->selectedEventId`
- `criado_por` → `Auth::id()`

---

## Navegação

- **Slug:** `reports/excursoes` (mantido)
- **Label no menu:** `Gestão de Excursionistas` (atualizado de "Relatório de Excursionistas")
- **Grupo:** `EXCURSIONISTAS` (movido de RELATÓRIOS para ficar com os outros recursos)

---

## Testes

- Cada aba renderiza corretamente (sem evento: estado vazio; com evento: dados)
- Create cria registro com `event_id` e `criado_por` corretos
- Edit atualiza campos sem mudar `event_id`
- Delete remove registro e exibe notificação
- Filtro de evento isola dados corretamente por aba
