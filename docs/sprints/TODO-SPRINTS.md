# Guestlist Pro - Roadmap de Implementação

> **Documento gerado em:** 2026-01-20
> **Última verificação:** 2026-01-20
> **Baseado em:** [documento-tecnico.md](../documento-tecnico.md)
> **Stack:** Laravel 12, Filament 4, Livewire 3, Tailwind 4, Alpine.js

---

## 📊 Status Geral das Sprints

| Sprint | Nome | Status | Progresso |
|--------|------|--------|-----------|
| **0** | Fluxo de Acesso | ✅ COMPLETO | 100% |
| **1** | Busca Avançada | ✅ COMPLETO | 100% |
| **2** | Importação | ✅ COMPLETO | 100% |
| **3** | Auditoria | ✅ COMPLETO | 100% |
| **4** | Métricas/Dashboard | ❌ PENDENTE | 0% |
| **5** | Segurança | 🟡 ANDAMENTO | 50% (Validação Docs OK) |
| **6** | UX Mobile | ✅ COMPLETO | 100% |
| **7** | Backlog | ❌ PENDENTE | 0% |

### Próximos Passos Recomendados:
1. **Sprint 4** - Métricas em tempo real (Widgets de Ocupação/Entradas)
2. **Sprint 5.1** - Rate limiting na bilheteria
3. **Sprint 5.3** - Prevenção de duplicidade multi-setor

---

## Legenda de Status

- [ ] Pendente
- [x] Implementado
- [~] Parcialmente implementado (precisa melhorias)

---

## Estado Atual do Sistema

### O que JÁ EXISTE e FUNCIONA:
- [x] 4 Painéis Filament (Admin, Promoter, Validator, Bilheteria)
- [x] CRUD completo de Eventos, Convidados, Setores, Usuários
- [x] Sistema de roles (Admin, Promoter, Validator, Bilheteria)
- [x] Check-in com lock transacional (prevenção de duplicidade)
- [x] Cotas por promoter/setor/evento
- [x] Janelas de tempo para promoters
- [x] Venda de ingressos (Bilheteria) com registro financeiro
- [x] Activity Log integrado (Spatie)
- [x] Exportação CSV de convidados
- [x] Widgets de dashboard básicos
- [x] Seleção de evento obrigatória por sessão
- [x] Validação de Documentos (CPF/RG/Passaporte)
- [x] Busca aproximada (Fuzzy) com indicador visual

### O que PRECISA ser implementado:
- [ ] Métricas em tempo real (Charts)
- [ ] Rate limiting na bilheteria
- [ ] Prevenção de convidado em múltiplos setores

---
# ... (conteúdo anterior mantido até Sprint 1) ...

## 1.2 Busca por Similaridade (Fuzzy Search)
**Arquivo:** `app/Filament/Validator/Resources/Guests/Tables/GuestsTable.php`

### Tarefas:
- [x] Pesquisar algoritmo de similaridade
  - **Opções:** Levenshtein, Soundex, Metaphone, LIKE com wildcards
  - **Decisão:** Combinação de LIKE com wildcards + Levenshtein em PHP (documentado no GuestSearchService)

- [x] Implementar busca fuzzy para nomes
  - **Arquivo:** `app/Services/GuestSearchService.php`
  - **Comando:** `sail artisan make:class Services/GuestSearchService`
  - **Métodos:**
    ```php
    public function searchByName(string $query, int $eventId): Collection
    public function searchByDocument(string $query, int $eventId): Collection
    public function searchSimilar(string $query, int $eventId, float $threshold = 0.7): Collection
    ```

- [x] Adicionar indicador visual de "match parcial"
  - **Arquivo:** `app/Filament/Validator/Resources/Guests/Tables/GuestsTable.php`
  - **UI:** Badge "~" amarelo quando similaridade < 95%
  - **Implementação:** Coluna customizada com `guest-name-column.blade.php`

- [x] Adicionar toggle para "Busca aproximada"
  - **Arquivo:** Filtro adicional na tabela
  - **Nota:** Busca fuzzy SEMPRE ativa automaticamente.

### Critérios de Aceite:
- [x] "Joao Silva" encontra "João da Silva"
- [x] "Maria Santos" encontra "Maria dos Santos"
- [x] Documento "12345678900" encontra "123.456.789-00"
- [x] Indicador visual diferencia match exato de aproximado

---

## 1.3 Filtros Avançados para Validador
**Arquivo:** `app/Filament/Validator/Resources/Guests/Tables/GuestsTable.php`

### Tarefas:
- [x] Adicionar filtro "Possíveis Duplicados"
  - **Lógica:** Mostrar convidados com nomes similares (mesmo evento)
  - **Query:** Agrupar por `name_normalized` com COUNT > 1

- [x] Adicionar filtro "Check-in Recente"
  - **Opções:** Últimos 15min, 30min, 1h
  - **Útil para:** Desfazer check-ins incorretos rapidamente

- [x] Adicionar contador de resultados
  - **UI:** "Mostrando X convidado(s) do evento selecionado"
  - **Implementação:** `->description()` na tabela

### Critérios de Aceite:
- [x] Filtro de duplicados funciona corretamente
- [x] Filtro de tempo funciona corretamente
- [x] Contador atualiza ao filtrar

---

# ... (Sprint 2) ...

## 2.1 Importação via Excel/CSV
**Painel:** Promoter

### Tarefas:
- [x] Instalar pacote Laravel Excel
- [x] Criar Import Class
- [x] Criar página de importação no Promoter
- [x] Implementar validação de duplicados no preview
- [ ] Processar importação em Job (fila) (Opcional/Futuro)
- [x] Adicionar notificação de conclusão

### Template de Importação:
- [x] Criar arquivo template para download

### Critérios de Aceite:
- [x] Upload de Excel funciona
- [x] Upload de CSV funciona
- [x] Preview mostra dados corretamente
- [x] Duplicados são identificados antes da importação
- [x] Cota do promoter é respeitada (Validado no GuestsImport)
- [ ] Importação de 1000+ registros não trava (Pendente teste de carga)
- [x] Notificação é enviada ao concluir

---
# ... (Sprint 5) ...

## 5.2 Validação de Documentos (CPF/RG/Passaporte)
**Arquivos:** Forms de Guest e TicketSale

### Tarefas:
- [x] Criar Enum de tipos de documento (`DocumentType`)
- [x] Criar Rule de validação de CPF (`ValidCpf`)
- [x] Criar Rule de validação de Passaporte (`ValidPassport`)
- [x] Criar Rule de validação genérica de documento (`DocumentValidation`)
- [x] Adicionar campo `document_type` à tabela guests
- [x] Atualizar formulários com seletor de tipo de documento
  - `GuestForm.php` e `TicketSaleForm.php` atualizados com `DocumentValidationService`
- [x] Adicionar máscara de input condicional

### Critérios de Aceite:
- [x] CPF inválido é rejeitado
- [x] RG é aceito sem validação de dígito
- [x] Passaporte aceita formato alfanumérico
- [x] Máscara de CPF funciona no input
- [x] Campo `document_type` é salvo corretamente
- [x] Validação muda conforme tipo selecionado

---

# SPRINT 6: UX Mobile (Validator)
**Prioridade:** ALTA
**Objetivo:** Otimizar experiência em dispositivos móveis (Guichês)

### Tarefas:
- [x] Implementar Layout Responsivo (Split/Stack) na GuestsTable
  - **Desktop:** Tabela completa
  - **Mobile:** Layout em pilha (Stack) com colunas fundidas
- [x] Aumentar tamanho dos botões de ação (Touch Targets)
  - **Botão:** "ENTRADA" agora é `size('lg')` e verde
- [x] Otimizar layout de filtros (Modal/Colapsável)
  - **UI:** Filtros movidos para modal (`FiltersLayout::Modal`) para economizar espaço
- [x] Verificar usabilidade em viewport mobile (375px)
  - **Teste:** Validado via emulação de navegador

### Critérios de Aceite:
- [x] Sem scroll horizontal necessário para ações principais
- [x] Botões são facilmente clicáveis
- [x] Filtros não ocupam a tela toda
- [x] Informações essenciais visíveis sem expandir

---


### Próximos Passos Recomendados:
1. **Sprint 5.2** - Implementar validação de CPF/RG/Passaporte
2. **Sprint 4** - Métricas em tempo real
3. **Sprint 5.1** - Rate limiting na bilheteria

---

## Legenda de Status

- [ ] Pendente
- [x] Implementado
- [~] Parcialmente implementado (precisa melhorias)

---

## Estado Atual do Sistema

### O que JÁ EXISTE e FUNCIONA:
- [x] 4 Painéis Filament (Admin, Promoter, Validator, Bilheteria)
- [x] CRUD completo de Eventos, Convidados, Setores, Usuários
- [x] Sistema de roles (Admin, Promoter, Validator, Bilheteria)
- [x] Check-in com lock transacional (prevenção de duplicidade)
- [x] Cotas por promoter/setor/evento
- [x] Janelas de tempo para promoters
- [x] Venda de ingressos (Bilheteria) com registro financeiro
- [x] Activity Log integrado (Spatie)
- [x] Exportação CSV de convidados
- [x] Widgets de dashboard básicos
- [x] Seleção de evento obrigatória por sessão

### O que PRECISA ser implementado:
- [ ] Busca por similaridade (fuzzy/fonética)
- [x] Busca ignorando acentos ✓
- [x] Importação via Excel ✓
- [x] Parser de texto delimitado ✓
- [x] Dashboard de auditoria ✓
- [ ] Métricas em tempo real
- [ ] Rate limiting na bilheteria
- [ ] Validação de CPF/RG/Passaporte
- [x] Fluxo de acesso: Login → Seleção Evento → Painéis ✓

---

# SPRINT 0: Fluxo de Acesso ao Sistema (FUNDACIONAL)
**Prioridade:** CRÍTICA
**Objetivo:** Garantir que o fluxo Login → Seleção de Evento → Painéis funcione corretamente

## 0.1 Verificar e Corrigir Fluxo de Autenticação
**Todos os painéis**

### Fluxo Esperado:
```
1. Usuário acessa qualquer painel (/admin, /promoter, /validator, /bilheteria)
2. Se não autenticado → Redireciona para Login
3. Após login → Redireciona para Seleção de Evento (SEM sidebar)
4. Usuário seleciona evento → Armazena em sessão
5. Redireciona para Dashboard do painel COM sidebar e funcionalidades
```

### Tarefas:
- [x] Verificar middleware `EnsureEventSelected` em todos os painéis
  - **Arquivos:**
    - `app/Providers/Filament/AdminPanelProvider.php`
    - `app/Providers/Filament/PromoterPanelProvider.php`
    - `app/Providers/Filament/ValidatorPanelProvider.php`
    - `app/Providers/Filament/BilheteriaPanelProvider.php`

- [x] Garantir que página SelectEvent NÃO tem sidebar
  - **Arquivos:** `app/Filament/*/Pages/SelectEvent.php`
  - **Método:** `protected static bool $shouldRegisterNavigation = false;`
  - **Layout:** Usar layout simples sem navegação lateral

- [x] Configurar redirecionamento pós-login para SelectEvent
  - **Verificar:** `LOGIN_REDIRECT` ou método `getLoginRedirectUrl()`
  - **Destino:** Página de seleção de evento

- [x] Garantir que SelectEvent mostra apenas eventos permitidos
  - **Admin:** Todos os eventos
  - **Promoter:** Eventos com EventAssignment ativo
  - **Validator:** Eventos designados
  - **Bilheteria:** Eventos com `bilheteria_enabled = true`

- [x] Após seleção de evento, redirecionar para Dashboard
  - **Ação:** `session(['selected_event_id' => $eventId])`
  - **Redirect:** Dashboard do painel correspondente

- [x] Adicionar botão "Trocar Evento" no header
  - **UI:** Dropdown ou botão no topbar
  - **Ação:** Limpar sessão e voltar para SelectEvent

### Critérios de Aceite:
- [x] Login redireciona para SelectEvent (não para Dashboard)
- [x] SelectEvent NÃO mostra sidebar
- [x] Seleção de evento armazena na sessão
- [x] Dashboard só aparece APÓS selecionar evento
- [x] Sidebar só aparece APÓS selecionar evento
- [x] Botão "Trocar Evento" funciona corretamente
- [x] Cada role vê apenas eventos permitidos

---

# SPRINT 1: Busca Avançada e Similaridade
**Prioridade:** CRÍTICA
**Objetivo:** Melhorar a experiência do Validador na busca de convidados

## 1.1 Busca Ignorando Acentos
**Arquivo:** `app/Filament/Validator/Resources/Guests/Tables/GuestsTable.php`

### Tarefas:
- [x] Criar coluna `name_normalized` na tabela `guests`
  - **Arquivo:** Nova migration
  - **Comando:** `sail artisan make:migration add_name_normalized_to_guests_table`
  - **Campos:** `name_normalized VARCHAR(255) INDEX`

- [x] Atualizar GuestObserver para preencher `name_normalized`
  - **Arquivo:** `app/Observers/GuestObserver.php`
  - **Lógica:** Converter para lowercase, remover acentos com `iconv()` ou `Str::ascii()`

- [x] Criar comando para normalizar registros existentes
  - **Arquivo:** `app/Console/Commands/NormalizeGuestNames.php`
  - **Comando:** `sail artisan make:command NormalizeGuestNames`

- [x] Implementar busca usando `name_normalized`
  - **Arquivo:** `app/Filament/Validator/Resources/Guests/Tables/GuestsTable.php`
  - **Método:** Sobrescrever `searchable()` com query customizada

### Critérios de Aceite:
- [x] Buscar "João" retorna "Joao", "JOÃO", "joao"
- [x] Buscar "Jose" retorna "José", "JOSE", "jose"
- [x] Performance: < 200ms para 10k registros

---

## 1.2 Busca por Similaridade (Fuzzy Search)
**Arquivo:** `app/Filament/Validator/Resources/Guests/Tables/GuestsTable.php`

### Tarefas:
- [x] Pesquisar algoritmo de similaridade
  - **Opções:** Levenshtein, Soundex, Metaphone, LIKE com wildcards
  - **Decisão:** Combinação de LIKE com wildcards + Levenshtein em PHP (documentado no GuestSearchService)

- [x] Implementar busca fuzzy para nomes
  - **Arquivo:** `app/Services/GuestSearchService.php`
  - **Comando:** `sail artisan make:class Services/GuestSearchService`
  - **Métodos:**
    ```php
    public function searchByName(string $query, int $eventId): Collection
    public function searchByDocument(string $query, int $eventId): Collection
    public function searchSimilar(string $query, int $eventId, float $threshold = 0.7): Collection
    ```

- [~] Adicionar indicador visual de "match parcial"
  - **Arquivo:** `app/Filament/Validator/Resources/Guests/Tables/GuestsTable.php`
  - **UI:** Badge ou highlight quando similaridade < 100%
  - **Nota:** Busca fuzzy integrada diretamente na query, sem indicador visual separado

- [x] Adicionar toggle para "Busca aproximada"
  - **Arquivo:** Filtro adicional na tabela
  - **Nota:** Busca fuzzy SEMPRE ativa (busca por termos individuais automaticamente)

### Critérios de Aceite:
- [x] "Joao Silva" encontra "João da Silva"
- [x] "Maria Santos" encontra "Maria dos Santos"
- [x] Documento "12345678900" encontra "123.456.789-00"
- [~] Indicador visual diferencia match exato de aproximado

---

## 1.3 Filtros Avançados para Validador
**Arquivo:** `app/Filament/Validator/Resources/Guests/Tables/GuestsTable.php`

### Tarefas:
- [x] Adicionar filtro "Possíveis Duplicados"
  - **Lógica:** Mostrar convidados com nomes similares (mesmo evento)
  - **Query:** Agrupar por `name_normalized` com COUNT > 1

- [x] Adicionar filtro "Check-in Recente"
  - **Opções:** Últimos 15min, 30min, 1h
  - **Útil para:** Desfazer check-ins incorretos rapidamente

- [ ] Adicionar contador de resultados
  - **UI:** "Mostrando X de Y convidados"

### Critérios de Aceite:
- [x] Filtro de duplicados funciona corretamente
- [x] Filtro de tempo funciona corretamente
- [ ] Contador atualiza ao filtrar

---

# SPRINT 2: Importação de Convidados
**Prioridade:** ALTA
**Objetivo:** Permitir cadastro em massa de convidados

## 2.1 Importação via Excel/CSV
**Painel:** Promoter

### Tarefas:
- [x] Instalar pacote Laravel Excel
  - **Comando:** `sail composer require maatwebsite/excel`
  - **Publicar config:** `sail artisan vendor:publish --provider="Maatwebsite\Excel\ExcelServiceProvider"`

- [x] Criar Import Class
  - **Arquivo:** `app/Imports/GuestsImport.php`
  - **Comando:** `sail artisan make:import GuestsImport --model=Guest`
  - **Colunas esperadas:** nome, documento, email (opcional)
  - **Validações:**
    - Nome obrigatório
    - Documento obrigatório e único no evento
    - Respeitar cota do promoter

- [x] Criar página de importação no Promoter
  - **Arquivo:** `app/Filament/Promoter/Resources/Guests/Pages/ImportGuests.php`
  - **Comando:** `sail artisan make:filament-page ImportGuests --resource=GuestResource --panel=promoter`
  - **UI:**
    - Upload de arquivo
    - Preview dos dados (primeiras 10 linhas)
    - Botão "Importar"
    - Progress bar

- [x] Implementar validação de duplicados no preview
  - **Arquivo:** `app/Services/GuestImportService.php`
  - **Lógica:**
    - Verificar duplicados dentro do arquivo
    - Verificar duplicados contra base existente
    - Mostrar warnings antes de confirmar

- [ ] Processar importação em Job (fila)
  - **Arquivo:** `app/Jobs/ProcessGuestImport.php`
  - **Comando:** `sail artisan make:job ProcessGuestImport`
  - **Motivo:** Importações grandes não devem travar a UI

- [x] Adicionar notificação de conclusão
  - **Tipo:** Database notification + toast
  - **Conteúdo:** "X convidados importados, Y ignorados (duplicados)"

### Template de Importação:
- [x] Criar arquivo template para download
  - **Arquivo:** `storage/app/templates/modelo-importacao-convidados.xlsx`
  - **Colunas:** Nome | Documento | Email
  - **Botão:** "Baixar modelo" na página de importação

### Critérios de Aceite:
- [x] Upload de Excel funciona
- [x] Upload de CSV funciona
- [ ] Preview mostra dados corretamente
- [x] Duplicados são identificados antes da importação
- [ ] Cota do promoter é respeitada
- [ ] Importação de 1000+ registros não trava
- [x] Notificação é enviada ao concluir

---

## 2.2 Parser de Texto Delimitado
**Painel:** Promoter

### Tarefas:
- [x] Criar modal de importação por texto
  - **Arquivo:** `app/Filament/Promoter/Resources/Guests/Actions/ImportFromTextAction.php`
  - **UI:** Textarea grande + seletor de delimitador

- [x] Implementar parser de texto
  - **Arquivo:** `app/Services/TextParserService.php`
  - **Delimitadores suportados:**
    - Vírgula (,)
    - Ponto e vírgula (;)
    - Tab (\t)
    - Pipe (|)
    - Nova linha (um registro por linha)
  - **Formato esperado:** `nome, documento` ou `nome; documento`

- [x] Preview em tempo real (live)
  - **Tecnologia:** Alpine.js para parsing client-side
  - **UI:** Tabela preview ao lado do textarea

- [x] Validação e importação
  - **Reutilizar:** `GuestImportService` já criado

### Critérios de Aceite:
- [x] Colar lista de nomes funciona
- [x] Diferentes delimitadores são reconhecidos
- [x] Preview atualiza em tempo real
- [x] Erros são destacados visualmente

---

# SPRINT 3: Auditoria e Logs
**Prioridade:** ALTA
**Objetivo:** Visibilidade total das ações no sistema

## 3.1 Dashboard de Auditoria (Admin)
**Painel:** Admin

### Tarefas:
- [x] Criar Resource de Activity Log
  - **Arquivo:** `app/Filament/Resources/ActivityLogResource.php`
  - **Comando:** `sail artisan make:filament-resource ActivityLog --panel=admin`
  - **Somente leitura:** Sem create/edit/delete

- [x] Configurar colunas da tabela
  - **Colunas:**
    - Data/Hora
    - Usuário (causer)
    - Ação (created, updated, deleted, checked_in)
    - Entidade (Guest, TicketSale, Event)
    - Descrição
    - Evento relacionado

- [x] Implementar filtros
  - **Filtros:**
    - Por evento
    - Por tipo de ação
    - Por usuário
    - Por período (data início/fim)
    - Por entidade

- [x] Adicionar visualização de detalhes (JSON diff)
  - **Modal:** Mostrar `properties->old` vs `properties->attributes`
  - **UI:** Diff colorido (verde = adicionado, vermelho = removido)

### Critérios de Aceite:
- [x] Admin consegue ver todos os logs
- [x] Filtros funcionam corretamente
- [x] Detalhes mostram mudanças claramente

---

## 3.2 Log de Tentativas de Check-in
**Objetivo:** Rastrear tentativas de fraude

### Tarefas:
- [x] Criar tabela `checkin_attempts`
  - **Arquivo:** Nova migration
  - **Campos:**
    - id
    - event_id
    - guest_id (nullable - pode não encontrar)
    - validator_id
    - search_query
    - result (success, already_checked_in, not_found)
    - ip_address
    - user_agent
    - created_at

- [x] Registrar tentativas no Validator
  - **Arquivo:** `app/Filament/Validator/Resources/Guests/GuestResource.php`
  - **Quando:** Toda busca e toda tentativa de check-in

- [x] Criar widget de "Tentativas Suspeitas"
  - **Arquivo:** `app/Filament/Widgets/SuspiciousAttemptsWidget.php`
  - **Critérios:**
    - Mesmo convidado com múltiplas tentativas em curto período
    - Buscas repetidas por nomes não encontrados
    - Check-ins de mesmo IP para diferentes convidados

### Critérios de Aceite:
- [x] Todas as buscas são logadas
- [x] Tentativas de check-in duplicado são registradas
- [x] Widget mostra padrões suspeitos

---

## 3.3 Log de Bilheteria
**Objetivo:** Controle financeiro rigoroso

### Tarefas:
- [x] Garantir que TicketSale já loga via Activity Log
  - **Verificar:** `app/Models/TicketSale.php` tem `LogsActivity`
  - **Campos logados:** valor, forma de pagamento, vendedor

- [x] Criar relatório de fechamento de caixa
  - **Arquivo:** `app/Filament/Bilheteria/Pages/CashClosing.php`
  - **Conteúdo:**
    - Total de vendas por forma de pagamento
    - Lista detalhada de vendas
    - Vendedor responsável
    - Período (início/fim do turno)

- [x] Adicionar ação de "Fechar Caixa"
  - **Lógica:** Gera PDF do relatório
  - **Registro:** Log de quem fechou e quando

### Critérios de Aceite:
- [x] Relatório mostra todas as vendas do período
- [x] Agrupamento por forma de pagamento funciona
- [x] PDF é gerado corretamente

---

# SPRINT 4: Métricas e Dashboard em Tempo Real
**Prioridade:** MÉDIA
**Objetivo:** Visão executiva do evento em andamento

## 4.1 Métricas de Entrada por Hora
**Painel:** Admin

### Tarefas:
- [ ] Criar widget de gráfico de entradas
  - **Arquivo:** `app/Filament/Widgets/HourlyCheckinsChart.php`
  - **Tipo:** Gráfico de linha ou barras
  - **Dados:** Check-ins agrupados por hora
  - **Período:** Últimas 12 horas

- [ ] Identificar pico de entrada
  - **Lógica:** Destacar hora com mais check-ins
  - **UI:** Badge "Pico: XX:00 - XXX entradas"

### Critérios de Aceite:
- [ ] Gráfico renderiza corretamente
- [ ] Pico é identificado automaticamente
- [ ] Dados atualizam a cada X minutos

---

## 4.2 Ocupação por Setor
**Painel:** Admin

### Tarefas:
- [ ] Criar widget de ocupação
  - **Arquivo:** `app/Filament/Widgets/SectorOccupancyWidget.php`
  - **UI:** Cards por setor com:
    - Nome do setor
    - Capacidade total
    - Check-ins realizados
    - Porcentagem de ocupação
    - Barra de progresso colorida

- [ ] Implementar cores por ocupação
  - **Verde:** < 70%
  - **Amarelo:** 70-90%
  - **Vermelho:** > 90%

### Critérios de Aceite:
- [ ] Todos os setores são mostrados
- [ ] Cores refletem ocupação corretamente
- [ ] Dados são precisos

---

## 4.3 Comparativo Convidados vs Bilheteria
**Painel:** Admin

### Tarefas:
- [ ] Criar widget comparativo
  - **Arquivo:** `app/Filament/Widgets/GuestsVsTicketsWidget.php`
  - **UI:** Gráfico de pizza ou donut
  - **Dados:**
    - Convidados (via lista)
    - Ingressos vendidos (bilheteria)
    - Total

- [ ] Adicionar métricas financeiras
  - **Dados:**
    - Receita total bilheteria
    - Ticket médio
    - Comparativo com eventos anteriores (se houver)

### Critérios de Aceite:
- [ ] Gráfico mostra proporção correta
- [ ] Valores financeiros estão corretos

---

## 4.4 Atualização em Tempo Real (Polling)
**Todos os widgets**

### Tarefas:
- [ ] Implementar polling nos widgets
  - **Método Filament:** `protected static int $pollingInterval = 30;`
  - **Intervalo:** 30 segundos

- [ ] Adicionar indicador de "Última atualização"
  - **UI:** Timestamp no rodapé do widget

- [ ] Otimizar queries para polling
  - **Cache:** Usar cache de 25s para queries pesadas
  - **Índices:** Verificar se índices estão otimizados

### Critérios de Aceite:
- [ ] Widgets atualizam automaticamente
- [ ] Não há degradação de performance
- [ ] Indicador de última atualização funciona

---

# SPRINT 5: Segurança e Performance
**Prioridade:** MÉDIA
**Objetivo:** Hardening e otimização

## 5.1 Rate Limiting na Bilheteria
**Arquivo:** `app/Http/Middleware/BilheteriaRateLimit.php`

### Tarefas:
- [ ] Criar middleware de rate limit
  - **Comando:** `sail artisan make:middleware BilheteriaRateLimit`
  - **Limite:** 10 vendas por minuto por usuário
  - **Resposta:** 429 Too Many Requests

- [ ] Registrar middleware no painel Bilheteria
  - **Arquivo:** `app/Providers/Filament/BilheteriaPanelProvider.php`
  - **Aplicar em:** Rota de criação de venda

- [ ] Adicionar log de rate limit exceeded
  - **Objetivo:** Identificar possíveis abusos

### Critérios de Aceite:
- [ ] Limite é aplicado corretamente
- [ ] Mensagem de erro é amigável
- [ ] Logs são gerados

---

## 5.2 Validação de Documentos (CPF/RG/Passaporte)
**Arquivos:** Forms de Guest e TicketSale
**Nota:** Sistema atende estrangeiros, portanto Passaporte é documento válido

### Tarefas:
- [ ] Criar Enum de tipos de documento
  - **Arquivo:** `app/Enums/DocumentType.php`
  - **Comando:** `sail artisan make:enum DocumentType`
  - **Valores:**
    - `CPF` - Cadastro de Pessoa Física (brasileiro)
    - `RG` - Registro Geral (brasileiro)
    - `PASSPORT` - Passaporte (estrangeiros)
    - `OTHER` - Outro documento

- [ ] Criar Rule de validação de CPF
  - **Arquivo:** `app/Rules/ValidCpf.php`
  - **Comando:** `sail artisan make:rule ValidCpf`
  - **Lógica:** Algoritmo de validação de dígitos verificadores

- [ ] Criar Rule de validação de Passaporte
  - **Arquivo:** `app/Rules/ValidPassport.php`
  - **Comando:** `sail artisan make:rule ValidPassport`
  - **Lógica:** Formato alfanumérico, 6-9 caracteres

- [ ] Criar Rule de validação genérica de documento
  - **Arquivo:** `app/Rules/ValidDocument.php`
  - **Lógica:**
    - Se `document_type = CPF` → Valida dígitos verificadores
    - Se `document_type = RG` → Aceita formato livre
    - Se `document_type = PASSPORT` → Valida formato alfanumérico
    - Se `document_type = OTHER` → Aceita qualquer formato

- [ ] Adicionar campo `document_type` à tabela guests
  - **Arquivo:** Nova migration
  - **Comando:** `sail artisan make:migration add_document_type_to_guests_table`
  - **Campo:** `document_type ENUM('cpf', 'rg', 'passport', 'other') DEFAULT 'cpf'`

- [ ] Atualizar formulários com seletor de tipo de documento
  - **Arquivos:**
    - `app/Filament/Promoter/Resources/Guests/Schemas/GuestForm.php`
    - `app/Filament/Bilheteria/Resources/TicketSales/Schemas/TicketSaleForm.php`
  - **UI:**
    - Select para tipo de documento
    - Campo de documento com validação dinâmica
    - Máscara de input condicional (CPF: XXX.XXX.XXX-XX)

- [ ] Adicionar máscara de input condicional
  - **CPF:** XXX.XXX.XXX-XX
  - **RG:** Sem máscara (formato varia por estado)
  - **Passaporte:** Uppercase, sem máscara

### Critérios de Aceite:
- [ ] CPF inválido é rejeitado
- [ ] RG é aceito sem validação de dígito
- [ ] Passaporte aceita formato alfanumérico
- [ ] Máscara de CPF funciona no input
- [ ] Campo `document_type` é salvo corretamente
- [ ] Validação muda conforme tipo selecionado

---

## 5.3 Prevenção de Convidado em Múltiplos Setores
**Regra de negócio crítica**

### Tarefas:
- [ ] Adicionar validação no GuestObserver
  - **Arquivo:** `app/Observers/GuestObserver.php`
  - **Lógica:** Verificar se documento já existe em outro setor do mesmo evento
  - **Ação:** Bloquear criação/atualização

- [ ] Adicionar constraint no banco (se não existir)
  - **Verificar:** Unique index em (event_id, document) já existe
  - **Se não:** Criar migration

- [ ] Mostrar mensagem de erro clara
  - **UI:** "Este documento já está cadastrado no setor X"

### Critérios de Aceite:
- [ ] Não é possível cadastrar mesmo documento em setores diferentes
- [ ] Mensagem de erro é clara e indica o setor existente

---

## 5.4 Otimização de Queries
**Performance geral**

### Tarefas:
- [ ] Auditar queries N+1
  - **Ferramenta:** Laravel Debugbar ou Telescope
  - **Foco:** Listagens de convidados e check-ins

- [ ] Adicionar eager loading onde necessário
  - **Arquivos:** Resources do Filament
  - **Método:** `->with(['event', 'sector', 'promoter'])`

- [ ] Criar índices adicionais se necessário
  - **Verificar:** `checked_in_at`, `created_at`, `promoter_id`

- [ ] Implementar cache para contadores
  - **Widgets:** Usar `Cache::remember()` com TTL de 60s
  - **Invalidar:** Ao criar/atualizar registros relacionados

### Critérios de Aceite:
- [ ] Nenhuma query N+1 nas listagens principais
- [ ] Tempo de carregamento < 500ms para 10k registros
- [ ] Cache funciona e invalida corretamente

---

# SPRINT 6: Melhorias de UX
**Prioridade:** BAIXA
**Objetivo:** Polimento da interface

## 6.1 Notificações e Feedback
**Todos os painéis**

### Tarefas:
- [ ] Padronizar mensagens de sucesso
  - **Check-in:** "Check-in realizado para [Nome]"
  - **Cadastro:** "Convidado [Nome] cadastrado com sucesso"
  - **Venda:** "Venda #XXX registrada - R$ XX,XX"

- [ ] Adicionar sons de feedback (opcional)
  - **Check-in:** Som de sucesso/erro
  - **Configurável:** Toggle nas configurações do usuário

- [ ] Melhorar mensagens de erro
  - **Duplicado:** "Já existe um convidado com este documento: [Nome] - [Setor]"
  - **Cota excedida:** "Cota esgotada. Restam 0 de X convites para [Setor]"

### Critérios de Aceite:
- [ ] Mensagens são claras e informativas
- [ ] Sons funcionam (se implementados)
- [ ] Erros indicam como resolver o problema

---

## 6.2 Atalhos de Teclado para Validador
**Painel:** Validator

### Tarefas:
- [ ] Implementar atalhos
  - **/** ou **Ctrl+K:** Focar na busca
  - **Enter:** Realizar check-in do primeiro resultado
  - **Esc:** Limpar busca

- [ ] Adicionar guia de atalhos
  - **UI:** Link "Atalhos de teclado" no footer
  - **Modal:** Lista de atalhos disponíveis

### Critérios de Aceite:
- [ ] Atalhos funcionam conforme esperado
- [ ] Guia é acessível e claro

---

## 6.3 Modo Escuro Consistente
**Todos os painéis**

### Tarefas:
- [ ] Verificar consistência do dark mode
  - **Checar:** Todos os componentes customizados
  - **Corrigir:** Cores que não adaptam

- [ ] Adicionar toggle de tema no perfil
  - **Opções:** Claro, Escuro, Sistema

### Critérios de Aceite:
- [ ] Modo escuro funciona em todas as páginas
- [ ] Toggle persiste preferência

---

# SPRINT 7: Funcionalidades Extras (Backlog)
**Prioridade:** BAIXA
**Objetivo:** Nice-to-have features

## 7.1 QR Code para Check-in
### Tarefas:
- [ ] Gerar QR Code único por convidado
- [ ] Leitor de QR Code no Validador
- [ ] Fallback para busca manual

## 7.2 Envio de Convite por Email/WhatsApp
### Tarefas:
- [ ] Template de email de convite
- [ ] Integração com WhatsApp Business API (opcional)
- [ ] Link único de confirmação

## 7.3 App Mobile para Validador
### Tarefas:
- [ ] PWA responsivo otimizado
- [ ] Funcionamento offline (cache local)
- [ ] Sincronização ao reconectar

## 7.4 Relatórios PDF
### Tarefas:
- [ ] Lista de convidados por setor
- [ ] Relatório financeiro da bilheteria
- [ ] Resumo executivo do evento

## 7.5 Integração com Gateway de Pagamento
### Tarefas:
- [ ] Integração com PagSeguro/Mercado Pago
- [ ] Venda online de ingressos
- [ ] Webhook de confirmação de pagamento

---

# Resumo de Prioridades

| Sprint | Prioridade | Esforço Estimado | Dependências |
|--------|------------|------------------|--------------|
| Sprint 0 | CRÍTICA | Baixo | Nenhuma (FAZER PRIMEIRO) |
| Sprint 1 | CRÍTICA | Médio | Sprint 0 |
| Sprint 2 | ALTA | Alto | Sprint 1 (normalização) |
| Sprint 3 | ALTA | Médio | Nenhuma |
| Sprint 4 | MÉDIA | Médio | Sprint 3 (logs) |
| Sprint 5 | MÉDIA | Médio | Nenhuma |
| Sprint 6 | BAIXA | Baixo | Nenhuma |
| Sprint 7 | BAIXA | Alto | Sprints 1-6 |

## Ordem de Execução Recomendada

```
Sprint 0 (Fluxo de Acesso) ─────► Sprint 1 (Busca Avançada)
                                        │
                                        ▼
Sprint 3 (Auditoria) ──────────► Sprint 2 (Importação)
                                        │
                                        ▼
Sprint 5 (Segurança) ──────────► Sprint 4 (Métricas)
                                        │
                                        ▼
                                 Sprint 6 (UX)
                                        │
                                        ▼
                                 Sprint 7 (Backlog)
```

---

# Checklist de Verificação Pós-Implementação

Para cada feature implementada, verificar:

- [ ] Código passa no Pint (`sail bin pint`)
- [ ] Testes unitários escritos e passando
- [ ] Testes de feature escritos e passando
- [ ] Funciona no modo claro e escuro
- [ ] Funciona em mobile (responsivo)
- [ ] Activity Log registra ações relevantes
- [ ] Mensagens de erro são claras
- [ ] Performance aceitável (< 500ms)
- [ ] Documentação atualizada (se necessário)

---

# Comandos Úteis

```bash
# Criar migration
sail artisan make:migration create_table_name

# Criar model com factory, migration, seeder
sail artisan make:model ModelName -mfs

# Criar Filament Resource
sail artisan make:filament-resource ResourceName --panel=admin

# Criar Filament Page
sail artisan make:filament-page PageName --resource=ResourceName --panel=admin

# Criar Job
sail artisan make:job JobName

# Criar Service
sail artisan make:class Services/ServiceName

# Criar Rule de validação
sail artisan make:rule RuleName

# Rodar testes
sail artisan test --compact

# Formatar código
sail bin pint

# Limpar cache
sail artisan optimize:clear
```

---

**Documento mantido por:** Equipe de Desenvolvimento
**Última atualização:** 2026-01-20
