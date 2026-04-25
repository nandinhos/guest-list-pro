# HANDOFF — Correção do Algoritmo de Importação de Convidados
**Data:** 2026-04-25  
**Sessão:** Claude Sonnet 4.6 → MiniMax  
**Branch:** main  

---

## Contexto do Projeto

**guest-list-pro** — Sistema de gestão de convidados para eventos com controle de acesso por setores (BACKSTAGE / PISTA), aprovações de listas e check-in.

Stack: Laravel 12 · Filament 4 · Livewire 3 · MySQL · PHP 8.4 · Sail (Docker).

O sistema possui dois fluxos de importação:
- **Importação simples** (Excel/CSV) — `app/Imports/GuestsImport.php`
- **Importação por arquivo Markdown com auto-criação de evento** — `app/Services/GuestImportService.php` + `app/Filament/Admin/Pages/ImportGuestsPage.php`

---

## O Que Foi Feito Nesta Sessão

### 1. Diagnóstico dos bugs no algoritmo de detecção

O `GuestImportService::importGuest()` tinha lógica inline que:
- Removia letras do documento **antes** de classificar → destruía passaportes (`N07913844` virava `07913844`)
- Só retornava `CPF` ou `PASSPORT` → tipo `RG` nunca era detectado

Confirmado com dados reais do arquivo `docs/lists/listageral.md`:
```
Omi Israel Burgos Puente, Passaporte N07913844   → era salvo como CPF 07913844
Giovani gidel messias da s, RG598787343           → era salvo como CPF 598787343
```

### 2. Correções aplicadas

#### `app/Enums/DocumentType.php`
- **`detectFromValue()`** reescrito com reconhecimento de prefixos:
  - `Passaporte N...` → PASSPORT
  - `RG:`, `Rg `, `RG ` → RG
  - Letras não-X → PASSPORT
  - 11 dígitos → CPF
  - Demais numéricos → RG (texto livre)
- **`normalizeValue()`** adicionado — normaliza por tipo:
  - PASSPORT: strip `Passaporte`, mantém alfanumérico uppercase → `N07913844`
  - RG: strip `RG:?`, mantém dígitos + `X` → `40886039X`
  - CPF/CNH: só dígitos

#### `app/Services/GuestImportService.php`
- **`importGuest()`** — substituídas linhas 202-203 pela dupla `detectFromValue + normalizeValue`
- Nomes com `+N` (acompanhantes) → `document = "+N acompanhante: name-slug"`, `document_type = null`
- Linhas sem documento → `document = "sem documento: name-slug"`, `document_type = null`, aviso no log
- **`parseFile()`** — regex `.+` → `.*` para capturar linhas com documento vazio
- Mensagem de duplicata: "CPF já cadastrado" → "Documento já cadastrado"

#### `app/Observers/GuestObserver.php`
**Causa raiz do último erro** — `normalizeDocument()` fazia `preg_replace('/\D/', '')` em TODOS os documentos, destruindo passaportes e placeholders.

Corrigido para respeitar o tipo:
```php
// Passaporte e document_type null (acompanhante/sem doc) → preserva como está
if ($guest->document_type === null || $guest->document_type === DocumentType::PASSPORT) {
    return;
}
$guest->document = preg_replace('/\D/', '', $guest->document);
```

`fillNormalizedColumns()` também atualizado: passport → uppercase alfanumérico; null → string vazia; demais → só dígitos.

#### `resources/views/filament/admin/pages/import-guests.blade.php`
- Botão **"Copiar Log"** adicionado no cabeçalho do card de resultado
- Alpine.js `navigator.clipboard.writeText()` copia log formatado com todas as linhas de warning e erros
- Botão vira verde com "Copiado!" por 2 segundos após clicar

### 3. Consolidação da lista de convidados

- `docs/lists/listagem_externa.md` (951 convidados, incluía Joãozinho) confrontada com `docs/lists/listageral.md`
- Deduplição: documento primeiro, nome normalizado como fallback
- Resultado: 940 convidados únicos, `listageral.md` sobrescrito e padronizado
- Cabeçalhos corrigidos: `## Convidados X ##` (H2) para promotor, `### SETOR ###` (H3) para setor

---

## Estado Atual dos Arquivos Modificados (não commitados)

```
M app/Enums/DocumentType.php
M app/Observers/GuestObserver.php
M app/Services/GuestImportService.php
M docs/lists/listageral.md
M resources/views/filament/admin/pages/import-guests.blade.php
M app/Filament/Admin/Pages/BackupManagement.php  ← pré-existente, não relacionado
```

**Nenhum teste foi criado** — não existem testes para esses componentes no projeto.

---

## Próximos Passos Sugeridos

### Prioridade alta

1. **Commit das correções** — as mudanças estão todas validadas via Tinker mas não commitadas.  
   Prefixo sugerido: `fix(import): corrige detecção de tipo de documento e observer de normalização`

2. **Testar importação completa** com o arquivo `docs/lists/listageral.md` (940 convidados) via interface em `/admin/import-guests` e verificar:
   - Passaportes salvos como `passport` com número correto
   - RGs com prefixo (`RG:`, `Rg `) salvos como `rg`
   - Acompanhantes (`+1`) salvos com `document = "+1 acompanhante: name-slug"`
   - Convidados sem doc salvos com `document = "sem documento: name-slug"`

3. **Criar testes PHPUnit** para os novos métodos:
   - `DocumentType::detectFromValue()` — cobrir todos os formatos do arquivo real
   - `DocumentType::normalizeValue()` — cobrir passport, RG com X, CPF
   - `GuestImportService::parseFile()` — verificar que linhas sem doc são capturadas

### Prioridade média

4. **Spec SPEC-0017** — formalizar as regras de importação de documento como spec oficial, pois o comportamento evoluiu muito além do SPEC-0013 e SPEC-0014 originais.

5. **Relatório de importação** — após importar a lista consolidada, comparar com o `docs/lists/relatorio-xxxperience-30anos.md` (documento de referência) para identificar discrepâncias de 11 convidados que ainda faltam.

---

## Mapa de Arquivos Chave

| Arquivo | Responsabilidade |
|---|---|
| `app/Services/GuestImportService.php` | Parse de .md + criação de evento/setor/promotor/guest |
| `app/Enums/DocumentType.php` | Detecção e normalização de tipo de documento |
| `app/Observers/GuestObserver.php` | Normalização antes de salvar no banco |
| `app/Imports/GuestsImport.php` | Importação via Excel/CSV (fluxo alternativo) |
| `app/Filament/Admin/Pages/ImportGuestsPage.php` | Página Filament de upload |
| `resources/views/filament/admin/pages/import-guests.blade.php` | View com preview, resultado e botão copiar log |
| `docs/lists/listageral.md` | Lista consolidada XXXPERIENCE 30 ANOS (940 convidados) |

---

## Regras de Formato do Arquivo de Importação

```
# Dados do Evento #
**Evento:** Nome do Evento
**Data:** 25/04/2026
**Local:** Local
**Horário:** 14:00 - 06:00

# Dados das Listas de Convidados #

## Convidados [NOME DO RESPONSÁVEL] ##

### BACKSTAGE ###

Nome Completo, 27589191841          ← CPF: 11 dígitos
Nome Completo, Passaporte N07913844 ← Passaporte: texto livre
Nome Completo, RG: 40886039X        ← RG: texto livre, pode ter X
Nome Com Acompanhante +1,           ← Acompanhante: sem documento

### PISTA ###

...
```

---

## Algoritmo de Detecção (ordem de precedência)

1. Nome contém `+N` → acompanhante, `document = "+N acompanhante: name-slug"`
2. Prefixo `Passaporte ` → PASSPORT
3. Prefixo `RG:?` (case insensitive) → RG
4. Letras não-X no valor → PASSPORT
5. 11 dígitos → CPF
6. Qualquer outro numérico → RG (texto livre)
7. Vazio → `"sem documento: name-slug"`, aviso no log
