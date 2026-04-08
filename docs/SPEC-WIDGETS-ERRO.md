# SPEC: Widgets com Erro no Dashboard Admin

## Contexto
O dashboard do painel Admin está apresentando erros 500 (Livewire update) ao carregar widgets específicos. O problema ocorre quando o usuário admin acessa o dashboard - os widgets são carregados mas geram erro de execução.

## Widgets Funcionando ✅
1. **AdminOverview** - StatsOverviewWidget
2. **PendingApprovalsWidget** - StatsOverviewWidget  
3. **ApprovalMetricsChart** - ChartWidget
4. **GuestsVsTicketsChart** - ChartWidget

## Widgets com Erro ❌

### 1. CheckinFlowChart
- **Arquivo**: `app/Filament/Widgets/CheckinFlowChart.php`
- **Classe**: `extends ChartWidget`
- **Heading**: "Fluxo de Check-in por Hora"
- **Lógica atual**: Usa `session('selected_event_id')` para filtrar por evento. Se não tiver evento, pode causar erro.
- **Possível causa**: Campo `checked_in_at` pode não existir na tabela `guests`

### 2. PromoterPerformanceChart
- **Arquivo**: `app/Filament/Widgets/PromoterPerformanceChart.php`
- **Classe**: `extends ChartWidget`
- **Heading**: "Performance por Promoter"
- **Lógica atual**: Usa relationship `guests` em User com filtros por event_id
- **Possível causa**: Query com `withCount` e subqueries pode falhar em produção

### 3. SectorOccupancyChart
- **Arquivo**: `app/Filament/Widgets/SectorOccupancyChart.php`
- **Classe**: `extends ChartWidget`
- **Heading**: "Ocupação por Setor"
- **Possível causa**: Integração com modelo `Sector` pode estar com problemas

### 4. SuspiciousCheckins
- **Arquivo**: `app/Filament/Widgets/SuspiciousCheckins.php`
- **Classe**: `extends TableWidget` (ou similar)
- **Possível causa**: Pode estar usando recursos deprecated do Filament v4

### 5. RequestsTimelineChart
- **Arquivo**: `app/Filament/Widgets/RequestsTimelineChart.php`
- **Classe**: `extends ChartWidget`
- **Heading**: "Solicitações por Dia"
- **Possível causa**: Queries complexas com datas podem falhar

## Padrão Observado

Todos os widgets com erro:
- Estendem `ChartWidget` (exceto SuspiciousCheckins)
- Usam `session('selected_event_id')` para filtrar dados
- Têm queries que filtram por `event_id`
- Usam `now()->today()` ou `Carbon` para datas

## Hipótese Principal

O problema é que **os widgets tentam usar `session('selected_event_id')` que não existe no painel admin**, causando comportamento inesperado nas queries. Além disso, pode haver:
1. Campos faltando no banco de produção
2. Queries mal otimizadas que timeout em produção
3. Incompatibilidade com Filament v4

## Ação Recomendada

1. Verificar se as migrations incluem todos os campos necessários (`checked_in_at`, etc.)
2. Adicionar lógica para admin ver dados globais (sem filtrar por evento)
3. Adicionar try/catch nos widgets para debug
4. Verificar logs em produção (storage/logs/laravel.log)

---

**Atualizado**: 2026-04-07
**Status**: Em investigação