# SPEC — Correção de Débitos Técnicos

**Versão**: 1.0  
**Data**: 2026-04-08  
**Projeto**: guest-list-pro  
**Stack**: Laravel 12 + Filament v4 + Livewire v3

---

## 1. Resumo Executivo

O sistema guest-list-pro apresenta uma estrutura geral sólida, porém foram identificados **8 débitos técnicos** que precisam ser corrigidos:

| Categoria | Qtd | Severidade |
|-----------|-----|------------|
| Arquitetura | 3 | 2 Alto, 1 Médio |
| Segurança | 2 | 1 Alto, 1 Médio |
| Performance | 2 | 2 Médio |
| Qualidade | 1 | Baixo |

O maior risco atual é a **validação de duplicatas duplicada** em múltiplos lugares, que pode causar inconsistência de dados.

---

## 2. Débitos Técnicos Identificados

### Débito 1: Validação de Duplicatas Duplicada

```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📍 LOCALIZAÇÃO : app/Services/ApprovalRequestService.php:23-120
                app/Models/ApprovalRequest.php:223-256
🏷️  CATEGORIA   : Arquitetura
⚠️  SEVERIDADE  : 🟠 ALTO
🔍 PROBLEMA    : Verificação de duplicatas de documento/nome 
                 existe em 3 lugares diferentes no código.
💥 IMPACTO     : Manutenção difícil. Se a regra mudar, precisa 
                 alterar em 3 locais. Risco de inconsistência.
✅ SOLUÇÃO     : Extrair para um serviço único: 
                 DuplicateGuestValidator
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

### Débito 2: Lógica de Negócio no Model

```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📍 LOCALIZAÇÃO : app/Models/ApprovalRequest.php:136-218
🏷️  CATEGORIA   : Arquitetura
⚠️  SEVERIDADE  : 🟠 ALTO
🔍 PROBLEMA    : Model ApprovalRequest contém métodos de 
                 validação de estado (canBeReviewed, 
                 canBeCancelled, etc) que deveriam estar 
                 em um Service ou Policy.
💥 IMPACTO     : Dificuldade de teste, acoplamento, Violação 
                 do princípio SRP.
✅ SOLUÇÃO     : Mover métodos de validação para ApprovalRequestService
                 ou criar ApprovalRequestValidator dedicado
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

### Débito 3: Acoplamento — Service Injetado Dinamicamente

```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📍 LOCALIZAÇÃO : app/Services/ApprovalRequestService.php:29
🏷️  CATEGORIA   : Arquitetura
⚠️  SEVERIDADE  : 🟡 MÉDIO
🔍 PROBLEMA    : GuestSearchService é instanciado dentro do 
                 método com app(): $searchService = app(GuestSearchService::class)
💥 IMPACTO     : Dificuldade de mock em testes, acoplamento 
                implícito.
✅ SOLUÇÃO     : Injetar via construtor:
                 public function __construct(
                     private GuestSearchService $searchService,
                 ) {}
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

### Débito 4: Service ApprovalRequestService Grande

```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📍 LOCALIZAÇÃO : app/Services/ApprovalRequestService.php (611 linhas)
🏷️  CATEGORIA   : Arquitetura
⚠️  SEVERIDADE  : 🟡 MÉDIO
🔍 PROBLEMA    : Service tem 15 métodos públicos, faz muitas 
                 coisas (validação duplicata + workflow).
                 File com 611 linhas é difícil de manter.
💥 IMPACTO     : Baixa testabilidade, dificuldade de 
                 manutenção, risco de bugs.
✅ SOLUÇÃO     : Separar em serviços menores:
                 - DuplicateCheckService
                 - ApprovalWorkflowService
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

### Débito 5: Políticas Filament Não Configuradas

```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📍 LOCALIZAÇÃO : app/Filament/Resources/* (todos)
🏷️  CATEGORIA   : Segurança
⚠️  SEVERIDADE  : 🟠 ALTO
🔍 PROBLEMA    : Recursos Filament não registram policies 
                 explicitamente via ->canCreate(), ->canEdit(),
                 ->canDelete()
💥 IMPACTO     : Segurança dependente apenas do middleware 
                 global. Não há granularidade finer.
✅ SOLUÇÃO     : Adicionar métodos de policy nos Resources:
                 protected static function canCreate(): bool
                 protected static function canEdit(): bool
                 protected static function canDelete(): bool
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

### Débito 6: Foreach com Queries em GuestSearchService

```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📍 LOCALIZAÇÃO : app/Services/GuestSearchService.php:217-241
🏷️  CATEGORIA   : Performance
⚠️  SEVERIDADE  : 🟡 MÉDIO
🔍 PROBLEMA    : Loop foreach itera sobre resultados de query
                 e faz queries adicionais dentro do loop
                 (padrão N+1)
💥 IMPACTO     : Performance ruim com muitos resultados
✅ SOLUÇÃO     : Usar Eager Loading ou queries com JOIN
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

### Débito 7: Falta de Índice em Colunas de Busca

```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📍 LOCALIZAÇÃO : database/migrations/
🏷️  CATEGORIA   : Performance
⚠️  SEVERIDADE  : 🟡 MÉDIO
🔍 PROBLEMA    : Verificar se colunas como 
                 name_normalized, document_normalized têm índices
💥 IMPACTO     : Queries lentas em tabelas grandes
✅ SOLUÇÃO     : Criar migration para adicionar índices
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

### Débito 8: Métodos Sem Documentação

```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📍 LOCALIZAÇÃO : app/Services/ (múltiplos)
🏷️  CATEGORIA   : Qualidade
⚠️  SEVERIDADE  : 🟢 BAIXO
🔍 PROBLEMA    : Alguns métodos públicos não têm docblocks
💥 IMPACTO     : Dificuldade de onboarding, manutenção
✅ SOLUÇÃO     : Adicionar phpDoc em métodos públicos
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

---

## 3. Plano de Implementação

### Fase 1: Correções Críticas (Sprint 1)

| # | Débito | Ação | Esforço |
|---|--------|------|---------|
| 1 | Duplicação de validação | Criar DuplicateGuestValidator | 2h |
| 2 | Lógica no Model | Mover métodos para Service | 1h |
| 3 | Injeção dinâmica | Refatorar construtor | 30min |
| 5 | Políticas Filament | Adicionar ->canCreate() etc | 2h |

### Fase 2: Refatoração (Sprint 2)

| # | Débito | Ação | Esforço |
|---|--------|------|---------|
| 4 | Service grande | Separar em serviços menores | 4h |
| 6 | N+1 Queries | Refatorar com eager loading | 2h |
| 7 | Índices | Criar migration | 1h |

### Fase 3: Melhorias (Sprint 3)

| # | Débito | Ação | Esforço |
|---|--------|------|---------|
| 8 | Documentação | Adicionar phpDoc | 2h |

---

## 4. Critérios de Aceitação

Após correção, o sistema deve atender:

- [ ] Validação de duplicatas em um único lugar
- [ ] Models sem lógica de negócio (apenas dados)
- [ ] Services com injeção via construtor
- [ ] Resources Filament com políticas explícitas
- [ ] Sem queries N+1 em loops
- [ ] Índices em colunas de busca
- [ ] Código documentado

---

## 5. Definição de Pronto

Cada debito será considerado resolvido quando:

1. **Testes passando** — PHPUnit/Pest passa
2. **Código revisado** — Code review aprovado
3. **Feature funcionando** — Teste manual confirmado
4. **Documentado** — phpDoc atualizado (se aplicável)

---

*SPEC criada com base na análise da skill code-review (devorq)*
*Última atualização: 2026-04-08*
