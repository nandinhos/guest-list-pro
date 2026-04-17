# SPEC: Tradução e Formatação Regional pt-BR

**Projeto:** guest-list-pro
**Data:** 2026-04-17
**Status:** draft
**Versão:** 1.0

---

## 1. Visão Geral

Este documento especifica o plano de tradução completa da aplicação Guest List Pro para Português do Brasil (pt-BR), incluindo formatação regional de datas, moeda e números.

**Objetivo:** Garantir que toda a interface, mensagens e formatações sigam os padrões brasileiros, proporcionando uma experiência nativa para usuários do Brasil.

---

## 2. Inventário de Traduções

### 2.1 Status Atual

| Área | Status | Observações |
|------|--------|-------------|
| Laravel Core (`lang/pt_BR/`) | ✅ Completo | 6 arquivos (auth, validation, pagination, etc) |
| Enums Customizados | ✅ Completo | UserRole, PaymentMethod, DocumentType, EventStatus, RequestStatus, RequestType |
| Filament Framework | ✅ Pt-BR | Traduções nativas do Filament (painéis, tabelas, formulários) |
| Labels Customizados | ✅ Em português | 249 labels verificados em Forms/Tables |
| Notifications | ✅ Em português | 66 notifications verificadas |
| Mensagens de Erro | ✅ Em português | Validation messages, auth failures |

### 2.2 Formatação Regional

| Aspecto | Status | Configuração Atual |
|---------|--------|-------------------|
| Timezone | ✅ Configurado | `America/Sao_Paulo` |
| Locale | ✅ Configurado | `pt_BR` |
| Formatação de Moeda | ⚠️ Parcial | Apenas timezone, não há formatadores específicos |
| Formatação de Datas | ⚠️ Parcial | Não há Carbon localization configurada |
| Formatação de Números | ❌ Não configurado | Separadores padrão do PHP (não brasileiros) |

---

## 3. Lacunas Identificadas

### 3.1 Formatação de Moeda

**Problema:** O sistema usa `number_format()` manual em vez de formatadores localization-aware.

**Solução Proposta:** Criar helper/service para formatação brasileira:
```php
// Atualmente (manual)
number_format($value, 2, ',', '.')

// Proposto (via helper)
format_money($value) // R$ 1.234,56
format_currency($value, 'BRL') // R$ 1.234,56
```

**Arquivos a modificar:**
- `app/helpers.php` (criar)
- `app/Services/FormatService.php` (criar)

### 3.2 Formatação de Datas

**Problema:** Não há padronização de formato de datas (dd/MM/yyyy HH:mm).

**Solução Proposta:** Configurar Carbon localization:
```php
// Em ServiceProvider
use Carbon\Carbon;
Carbon::setLocale('pt_BR');

// Uso em views
{{ $guest->created_at->format('d/m/Y H:i') }}
{{ $guest->checked_in_at?->format('d/m/Y H:i') ?? '-' }}
```

**Locais a verificar:**
- Todos os `->dateTime()` em Tables
- Todos os `DateTimePicker` em Forms
- Todos os `->format()` em código

### 3.3 Formatação de Números

**Problema:** Separadores padrão americano (1,000.00) ao invés de brasileiro (1.000,00).

**Solução Proposta:** Usar `NumberFormatter` do PHP ou helper Laravel:
```php
// Via Laravel helper
Number::format(1234.56, 2); // Localized
```

---

## 4. Plano de Implementação

### Fase 1: Service de Formatação

**Arquivos a criar:**
```
app/Services/
  └── FormatService.php         # Centraliza formatação
app/helpers.php                # Helpers globais
```

**Métodos do FormatService:**
```php
class FormatService
{
    public static function money(float $value): string;      // R$ 1.234,56
    public static function date(DateTime $date): string;    // 17/04/2026
    public static function datetime(DateTime $date): string; // 17/04/2026 14:30
    public static function number(float $value, int $decimals = 2): string; // 1.234,56
    public static function percent(float $value): string;    // 85,5%
}
```

### Fase 2: Configuração de Locale

**Arquivo a criar/modificar:**
```
bootstrap/app.php               # Adicionar localization config
config/formatting.php          # Configurações de formatação
```

**config/formatting.php:**
```php
return [
    'locale' => 'pt_BR',
    'timezone' => 'America/Sao_Paulo',
    'currency' => 'BRL',
    'currency_symbol' => 'R$',
    'date_format' => 'd/m/Y',
    'datetime_format' => 'd/m/Y H:i',
    'number_decimal_separator' => ',',
    'number_thousands_separator' => '.',
];
```

### Fase 3: Aplicação nos Widgets

**Widgets a modificar:**
- `SalesTimelineChart` - valores de receita
- `SectorMetricsTable` - valores monetários
- `TicketTypeReportTable` - preços e valores
- `BilheteriaOverview` - stats de vendas
- `AdminOverview` - stats gerais

### Fase 4: Aplicação nas Tables

**Tables a modificar:**
- `GuestsTable` - datas de check-in, cadastro
- `TicketSalesTable` - valores, datas
- `ApprovalRequestsTable` - datas
- `PromoterPermissionsTable` - datas

---

## 5. Requisitos Funcionais

| Código | Descrição | Prioridade |
|--------|-----------|------------|
| RF01 | Todos os valores monetários devem usar formato brasileiro (R$ 1.234,56) | Alta |
| RF02 | Todas as datas devem usar formato dd/MM/yyyy HH:mm | Alta |
| RF03 | Números devem usar separador decimal vírgula e separador de milhar ponto | Média |
| RF04 | Percentuais devem usar formato brasileiro (85,5%) | Média |

---

## 6. Requisitos Não Funcionais

| Código | Descrição | Prioridade |
|--------|-----------|------------|
| RNF01 | Helper deve ser stateless e thread-safe | Alta |
| RNF02 | Não deve impactar performance (cache de config) | Média |
| RNF03 | Deve funcionar com valores null | Alta |

---

## 7. Casos de Borda

| Caso | Tratamento |
|------|-----------|
| Valor null em money | Retornar "R$ 0,00" ou "-" |
| Data null em datetime | Retornar "-" |
| Valor 0 em percent | Retornar "0%" |
| Número inteiro (sem decimal) | formatter com 0 decimais |

---

## 8. Estimativa de Esforço

| Fase | Descrição | Tempo Estimado |
|------|-----------|----------------|
| 1 | FormatService + helpers | 2h |
| 2 | Configuração locale | 1h |
| 3 | Aplicar nos Widgets | 2h |
| 4 | Aplicar nas Tables | 3h |
| **Total** | | **8h** |

---

## 9. Riscos

| Risco | Probabilidade | Impacto | Mitigação |
|-------|--------------|---------|-----------|
| Quebrar formatação existente em lugares inesperados | Média | Alto | Criar testes de formatação |
| Conflito com localize do Carbon/Laravel | Baixa | Médio | Documentar configuração |

---

## 10. Assumptions

1. O sistema já possui `lang/pt_BR/` configurado
2. Carbon localization está disponível no projeto
3. O timezone `America/Sao_Paulo` é correto para todos os usuários
4. Não há necessidade de suporte a outros locales (pt-BR only)

---

## 11. Missing Information

1. **Precisaríamos saber:** Existe algum padrão de formatação já usado em outros projetos da empresa?
2. **Precisaríamos saber:** Os testes devem cobrir formatação? (specs não mencionam testes para isso)

---

## 12. Recomendações

1. **Criar trait `FormatsMoney`** para Models que precisam de formatação
2. **Adicionar testes** para FormatService
3. **Documentar uso** no README do projeto
4. **Considerar** usar `league/commonmark` com tradução pt-BR para markdown renderizado

---

## 13. Arquivos a Criar/Modificar

### Novos Arquivos:
```
app/Services/FormatService.php
app/helpers.php
config/formatting.php
tests/Unit/FormatServiceTest.php
```

### Arquivos a Modificar:
```
bootstrap/app.php              # Adicionar config
app/Filament/Widgets/*.php     # Usar FormatService
app/Filament/**/Tables/*.php  # Usar formatação
config/app.php                 # Verificar locale
```

---

## 14. Validação

Para validar que está funcionando:

```bash
# No tinker
php artisan tinker
>>> format_money(1234.56)
=> "R$ 1.234,56"

>>> format_datetime(now())
=> "17/04/2026 14:30"

>>> format_number(1234.567, 2)
=> "1.234,57"
```
