# SPEC-0013: Importação de Lista de Convidados

## Objetivo
Criar página de importação para upload de arquivo texto (.md/.txt) com lista de convidados organizada por responsável/setor.

## Formato do Arquivo

```
### Convidados Erick ###
# BACKSTAGE #
Wellington Miranda, 27589191841
Priscilla Stocco, 22010456823

# PISTA #
LUCAS SÓGLIA LAROTONDA, 41813773858
```

### Estrutura:
- `### Convidados [NOME] ###` → Responsável/Promotor
- `# BACKSTAGE #` ou `# PISTA #` → Setor
- `Nome Completo, Documento` → Guest (CPF ou Passaporte)

## Regras de Negócio

| Regra | Comportamento |
|-------|---------------|
| Evento | Selecionável na tela de importação |
| Promotor | Identificado pelo nome. Criado automaticamente se não existir |
| Status Guest | Default `approved` |
| Duplicados | Se CPF já existe → warning, não salva |
| Documento | CPF (11 dígitos) ou Passaporte (texto livre) |
| Acesso | Apenas Admin |

## Arquitetura

```
app/
├── Filament/Admin/Pages/
│   └── ImportGuestsPage.php          # Página de importação
├── Services/
│   └── GuestImportService.php         # Parser + lógica
```

## Fluxo de Importação

1. Admin acessa `/admin/import-guests`
2. Seleciona evento
3. Upload arquivo (.md/.txt)
4. Sistema parseia e mostra PREVIEW
5. Admin clica "Importar"
6. Sistema processa e mostra RELATÓRIO
7. Admin vê: importados, duplicados, erros

## Gates
- [x] Gate 1: SPEC
- [ ] Gate 2: Pre-Flight
- [ ] Gate 3: Quality
- [ ] Gate 4: Code Review
- [ ] Gate 5: Lesson Learned
- [ ] Gate 6: Handoff
- [ ] Gate 7: Deploy
