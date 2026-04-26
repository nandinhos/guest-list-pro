# HANDOFF — Import de Excursões

**Projeto:** guest-list-pro  
**Branch:** main  
**Stack:** Laravel 12 + Filament v4 + Livewire v3 + PHP 8.5 via Laravel Sail  
**Execução:** todos os comandos via `vendor/bin/sail artisan ...`

---

## Contexto

O arquivo `docs/lists/excursão.md` contém ~2027 linhas com dados semi-estruturados de excursões de um evento de música eletrônica. Foi implementado um parser e um artisan command para analisar e importar esses dados no banco.

---

## Arquivos implementados (prontos)

### `app/Services/ExcursoesListParser.php`
- Método `parse(string $raw): array` — divide em blocos por linhas em branco, identifica documento, tipo de veículo, código de veículo, nome do monitor e nome da excursão
- Método `report(array $entries): array` — gera relatório com contagens
- Heurística: linha imediatamente antes do documento = monitor; linha restante = excursão
- Dedup por `document_number` (cada CPF/RG/Passaporte aparece só uma vez)

### `app/Console/Commands/ExcursoesImport.php`
- Signature: `excursoes:import {file} {--event=} {--import} {--user=}`
- Sem `--import`: só análise/relatório
- Com `--import --event=ID`: importa no banco

---

## Resultado da análise do arquivo

```
Total de entradas (após dedup por documento): 226
Excursões distintas: 168
Veículos por tipo:
  Ônibus: 98
  Van: 123
  Sem tipo: 5
Total de monitores: 226
Entradas sem nome de excursão: 24
```

As **24 entradas sem excursão** são aceitáveis — são vans/ônibus que não têm excursão associada no arquivo. O usuário confirmou que isso é esperado: "há vans que não são excursões, então poderia ficar sem associação".

---

## O que falta fazer

### 1. Ajustar o import para aceitar entrada sem excursão

Atualmente o `importEntries` pula (`skippedNoExcursao`) entradas sem nome de excursão. Precisa ser ajustado para:
- Criar um Veiculo sem `excursao_id` (verificar se a FK permite null) OU
- Associar a uma excursão padrão tipo "Sem excursão" para o evento

**Verificar:** se `excursao_id` na tabela `veiculos` permite NULL:
```bash
vendor/bin/sail artisan db:show --table=veiculos
```

Se não permitir null, criar excursão padrão "Sem excursão" e associar a ela.

### 2. Testar o import real

Verificar qual é o ID do evento no banco:
```bash
vendor/bin/sail artisan tinker --no-interaction << 'EOF'
\App\Models\Event::all(['id','name']);
EOF
```

Depois rodar o import:
```bash
vendor/bin/sail artisan excursoes:import docs/lists/excursão.md --import --event=ID_DO_EVENTO
```

### 3. Rodar Pint antes de commitar

```bash
vendor/bin/sail bin pint --dirty
```

### 4. Commit

```
feat (excursoes): parser e command de import a partir de arquivo .md
```

---

## Estrutura do banco (models relevantes)

```
Excursao:  id, event_id, nome, criado_por
Veiculo:   id, excursao_id, tipo (TipoVeiculo enum: onibus/microonibus/van), placa
Monitor:   id, veiculo_id, event_id, nome, document_type, document_number, criado_por
```

Unique constraint em `monitores`: `[event_id, document_type, document_number]`

---

## Enums relevantes

- `App\Enums\TipoVeiculo` — `ONIBUS`, `MICROONIBUS`, `VAN`
- `App\Enums\DocumentType` — `CPF`, `RG`, `CNH`, `PASSPORT`  
  - `detectFromValue(string)` — detecta tipo pelo valor  
  - `normalizeValue(string, type)` — normaliza (remove formatação)

---

## Padrão de commits obrigatório

```
feat (escopo): descrição em português, sem emojis, sem Co-authored-by
```

---

## Como rodar testes

```bash
vendor/bin/sail artisan test --compact
```
