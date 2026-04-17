# SPEC-001: Correções de Code Review — guest-list-pro

**Versão:** 1.1  
**Data:** 2026-04-07  
**Data Atualização:** 2026-04-17  
**Status:** Implementada ✅  
**Prioridade:** Alta  
**Responsável:** Backend Team  

---

## 1. Objetivo

Implementar as correções críticas e de alta prioridade identificadas no Code Review para elevar o projeto a um nível production-ready, resolvendo gaps de segurança, performance e qualidade.

---

## 2. Escopo

### 2.1 Escopo Incluído

| ID | Item | Categoria | Prioridade |
|----|------|-----------|------------|
| T-001 | Criar GuestPolicy com regras de acesso | Segurança | 🔴 Crítica |
| T-002 | Criar ApprovalRequestPolicy com regras de acesso | Segurança | 🔴 Crítica |
| T-003 | Configurar TrustProxies para logs de auditoria | Segurança | 🟡 Média |
| T-004 | Implementar batch duplicate check em GuestSearchService | Performance | 🟠 Alta |
| T-005 | Corrigir N+1 em findPossibleDuplicates() | Performance | 🟠 Alta |
| T-006 | Criar testes unitários para GuestService | Qualidade | 🟡 Média |
| T-007 | Refatorar ApprovalRequestService::approve() (SRP) | Qualidade | 🟡 Média |
| T-008 | Remover qr_token do $fillable em Guest | Segurança | 🟢 Baixa |

### 2.2 Escopo Excluído

- Migração de busca de similaridade para MySQL/Elasticsearch (Sprint 3)
- Implementação de testes de integração com Policies (Sprint 3)
- Documentação de ADRs (fora do escopo desta spec)

---

## 3. Pré-requisitos

- [ ] Ambiente local configurado com Sail/Docker
- [ ] branch `feature/code-review-fixes` criada a partir de `main`
- [ ] Code Review completo analisado (11 itens)
- [ ] Estabelecido Definition of Done (DoD)

---

## 4. Detalhamento das Tarefas

### T-001: GuestPolicy — Regras de Acesso a Convidados

**Dependência:** Nenhuma

#### Descrição

Criar Laravel Policy para o model Guest com as seguintes regras:

| Método | Regra |
|--------|-------|
| `viewAny` | Admin, Validator, Promoter (se tiver permissão no evento) |
| `view` | Admin, Validator, Promoter (se convidado for do seu evento/setor) |
| `create` | Admin, Promoter (se tiver quota disponível no setor) |
| `update` | Admin, Promoter (se convidado for do seu setor) |
| `delete` | Admin apenas |
| `restore` | Admin apenas |
| `forceDelete` | Admin apenas |

#### Critérios de Aceitação

- [ ] Policy registrada em `AuthServiceProvider`
- [ ] Todos os métodos implementados com verificação de role e setor
- [ ] Testes unitários cobrindo 100% dos métodos
- [ ] Implementado em: `app/Policies/GuestPolicy.php`

#### Risks

- **Risco:** Lógica de permissão de promoter basada em PromoterPermission pode ser complexa
- **Mitigação:** Criar método helper `canAccessSector()` no User model

---

### T-002: ApprovalRequestPolicy — Regras de Acesso a Solicitações

**Dependência:** T-001

#### Descrição

Criar Laravel Policy para o model ApprovalRequest com as seguintes regras:

| Método | Regra |
|--------|-------|
| `viewAny` | Admin, Validator, Promoter |
| `view` | Admin, Solicitante da request |
| `create` | Admin, Validator, Promoter |
| `approve` | Admin apenas (não pode ser o solicitante) |
| `reject` | Admin apenas (não pode ser o solicitante) |
| `reconsider` | Admin apenas |
| `revert` | Admin apenas |
| `cancel` | Apenas o solicitante |

#### Critérios de Aceitação

- [ ] Policy registrada em `AuthServiceProvider`
- [ ] Regra "não pode aprovar própria solicitação" implementada no método `approve`
- [ ] Testes unitários cobrindo cenários de permissão negada
- [ ] Implementado em: `app/Policies/ApprovalRequestPolicy.php`

---

### T-003: TrustProxies Configuration

**Dependência:** Nenhuma

#### Descrição

Configurar middleware de trust proxies para logs de auditoria confiáveis, permitindo que o Laravel reconheça headers de proxy (X-Forwarded-For, X-Forwarded-Proto, etc).

#### Implementação

```php
// Em bootstrap/app.php ouMiddleware class
Request::setTrustedProxies(
    ['*'], // ou IPs específicos do proxy/load balancer
    Request::HEADER_X_FORWARDED_FOR |
    Request::HEADER_X_FORWARDED_HOST |
    Request::HEADER_X_FORWARDED_PORT |
    Request::HEADER_X_FORWARDED_PROTO |
    Request::HEADER_X_FORWARDED_AWS_ELB
);
```

#### Critérios de Aceitação

- [ ] Configuração aplicada no bootstrap/app.php
- [ ] Teste verificando que `request()->ip()` retorna IP real (não 127.0.0.1)
- [ ] Documentação no .env.example sobre confiança de proxies

---

### T-004: Batch Duplicate Check

**Dependência:** Nenhuma

#### Descrição

Implementar método `checkForDuplicatesBatch()` em GuestSearchService que verifica duplicatas para múltiplos guests em uma única query, evitando N+1 em importações massivas.

#### Interface

```php
/**
 * @param  array<int, array{name: string, document?: string}>  $guests
 * @return array<int, array{index: int, type: string, level: string, message: string, existing: Guest|null}>
 */
public function checkForDuplicatesBatch(int $eventId, array $guests): array
```

#### Critérios de Aceitação

- [ ] Executa no máximo 4 queries para qualquer número de guests (batch de documentos + batch de nomes)
- [ ] Retorna array com índice do guest original + informações de duplicata
- [ ] Compatível com o existing `checkForDuplicates()` ( mesmo formato de retorno)
- [ ] Usado por GuestsImport::process() no lugar de chamada em loop

---

### T-005: Fix N+1 em findPossibleDuplicates()

**Dependência:** Nenhuma

#### Descrição

Corrigir o problema de N+1 onde `findPossibleDuplicates()` executa uma query adicional para cada grupo de nomes duplicados.

#### Solução

Usar eager loading no primeiro query, evitando query por grupo:

```php
// ANTES (N+1)
$guests = Guest::query()
    ->where('event_id', $eventId)
    ->whereNotNull('name_normalized')
    ->get()
    ->map(function ($item) use ($eventId) {
        $guests = Guest::query()
            ->where('event_id', $eventId)
            ->where('name_normalized', $item->name_normalized)
            ->with(['sector']) // <-- N+1 aqui
            ->get();
    });

// DEPOIS (1 query)
$groups = Guest::query()
    ->where('event_id', $eventId)
    ->whereNotNull('name_normalized')
    ->with(['sector', 'promoter']) // eager load
    ->get()
    ->groupBy('name_normalized')
    ->filter(fn ($group) => $group->count() > 1);
```

#### Critérios de Aceitação

- [ ] Apenas 1-2 queries executadas independent do número de duplicatas
- [ ] Mesma estrutura de retorno

---

### T-006: Testes Unitários para GuestService

**Dependência:** Nenhuma

#### Descrição

Criar testes unitários cobrindo os métodos principais de GuestService:

| Método | Cenários de Teste |
|--------|-------------------|
| `checkinByQrToken` | Sucesso, Token não encontrado, Já checkado, Usuário sem permissão |
| `canRegisterGuest` | Promoter ativo com permissão, Promoter inativo, Sem permissão de setor, Fora do horário, Limite atingido |
| `getAuthorizedEvents` | Retorna eventos corretos |
| `getAuthorizedSectors` | Retorna setores corretos para evento |

#### Critérios de Aceitação

- [ ] Arquivo: `tests/Unit/GuestServiceTest.php`
- [ ] Coverage: 100% dos métodos públicos
- [ ] Usa mock de User, PromoterPermission, Guest
- [ ] Segue padrão Pest/PHPUnit do projeto

---

### T-007: Refatorar ApprovalRequestService::approve()

**Dependência:** T-002 (Policy criada)

#### Descrição

Extrair validações e lógicas do método `approve()` em métodos menores para seguir SRP:

```php
// Estrutura desejada
public function approve(ApprovalRequest $request, User $admin, ?string $notes = null): ApprovalRequest
{
    $this->validateCanApprove($request, $admin);
    $this->validateNoExistingGuest($request);
    
    return DB::transaction(function () use ($request, $admin, $notes) {
        $guest = $this->createGuestFromRequest($request);
        $this->updateRequestAsApproved($request, $admin, $notes, $guest);
        $this->notifyRequester($request);
        
        return $request->fresh();
    });
}

private function validateCanApprove(ApprovalRequest $request, User $admin): void { ... }
private function validateNoExistingGuest(ApprovalRequest $request): void { ... }
private function createGuestFromRequest(ApprovalRequest $request): Guest { ... }
private function updateRequestAsApproved(ApprovalRequest $request, User $admin, ?string $notes, Guest $guest): void { ... }
```

#### Critérios de Aceitação

- [ ] Método principal com max 20 linhas
- [ ] Cada método privado faz apenas uma coisa
- [ ] Testes existentes continuam passando
- [ ] Sem mudança de comportamento

---

### T-008: Remover qr_token do $fillable

**Dependência:** Nenhuma

#### Descrição

Remover `qr_token` do array `$fillable` em Guest.php, pois o token deve ser gerado automaticamente via Observer (GuestObserver) e não ser editável via mass assignment.

#### Critérios de Aceitação

- [ ] Campo removido do $fillable
- [ ] Verificar que GuestObserver ainda gera o token na criação
- [ ] Testes existentes passam

---

## 5. Critérios de Aceitação Gerais

### Definition of Done (DoD)

- [x] Todas as 8 tarefas concluídas
- [x] `php artisan test` passando 100%
- [x] `composer pint --test` sem erros de style
- [x] `php artisan optimize:clear` executado
- [x] PR criado e code review aprovado
- [x] Merge para branch main

### Checklist de Implementação

| Tarefa | Implementada | Data |
|--------|-------------|------|
| T-001: GuestPolicy | ✅ | 07/04/2026 |
| T-002: ApprovalRequestPolicy | ✅ | 07/04/2026 |
| T-003: TrustProxies | ✅ | 07/04/2026 |
| T-004: Batch Duplicate Check | ✅ | 07/04/2026 |
| T-005: Fix N+1 | ✅ | 07/04/2026 |
| T-006: GuestServiceTest | ✅ | 07/04/2026 |
| T-007: Refatorar approve() | ✅ | 07/04/2026 |
| T-008: Remover qr_token | ✅ | 07/04/2026 |

### Definition of Ready (DoR)

- [ ] Ambiente configurado
- [ ] Tarefas priorizadas no board
- [ ] Critérios de aceitação claros

---

## 6. Riscos e Mitigações

| Risco | Probabilidade | Impacto | Mitigação |
|-------|---------------|----------|-----------|
| Conflito com código existente (Policies) | Alta | Médio | Criar branch isolada, revisar diff antes de merge |
| Quebra de testes existentes | Média | Alto | Rodar full test suite após cada tarefa |
| Complexidade de permissão de promoter | Média | Médio | Criar método helper no User model antes |
| Escopo creep (adicionar features) | Alta | Alto | Manter foco estrito nas 8 tarefas |

---

## 7. Estimativas

| Tarefa | Estimativa | Complexidade |
|--------|------------|--------------|
| T-001 | 2h | Média |
| T-002 | 2h | Média |
| T-003 | 30min | Baixa |
| T-004 | 4h | Alta |
| T-005 | 1h | Média |
| T-006 | 3h | Média |
| T-007 | 2h | Média |
| T-008 | 30min | Baixa |
| **TOTAL** | **15h** | - |

---

## 8. Timeline Sugerido

```
Semana 1 (seg-qui): T-001, T-002, T-003
Semana 1 (sex):     T-006 (iniciar)
Semana 2 (seg-ter): T-006 (continuar), T-004
Semana 2 (qua-qui): T-005, T-007
Semana 2 (sex):     T-008, testes finais, PR
```

---

## 9. Critérios de Validação Final

- [ ] Code review original não tem mais items críticos ou altos
- [ ] Nenhuma regresão em testes existentes
- [ ] Policies aplicadas em todos os recursos Filament relevantes
- [ ] Performance de importações melhorada (sem N+1)
- [ ] Logs de auditoria com IPs confiáveis

---

## 10. Referências

- [Code Review Original](link-para-o-code-review)
- [GuestService.php](app/Services/GuestService.php)
- [ApprovalRequestService.php](app/Services/ApprovalRequestService.php)
- [GuestSearchService.php](app/Services/GuestSearchService.php)
- [Guest.php](app/Models/Guest.php)
- [GuestObserver.php](app/Observers/GuestObserver.php)
- [Stack Laravel Reference](file:///home/nandodev/.claude/skills/codereview_devorq/stack-laravel.md)