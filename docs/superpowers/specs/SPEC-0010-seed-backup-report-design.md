# SPEC-0010: Seed Lista XXXPERIENCE + Backup Sistema + Relatório Cortesias

## 1. Overview

Implementar três funcionalidades para suportar operação do evento **XXXPERIENCE 30 ANOS**:

1. **Seed Script**: Popular banco com lista de 418 convidados de cortesia
2. **Sistema de Backup/Restore**: Backup completo do banco para recovery
3. **Relatório Consolidado**: Relatório de cortesias por responsável com exportação PDF/Excel

---

## 2. Evento e Dados Base

```php
Event::create([
    'name' => 'XXXPERIENCE 30 ANOS',
    'date' => '2026-04-25',
    'start_time' => '14:00',
    'end_time' => '06:00',
    'location' => 'Fazenda Santa Rita - Itu/SP',
    'status' => EventStatus::ACTIVE,
    'ticket_price' => 0, // Cortesias
    'bilheteria_enabled' => false,
]);
```

### Setores

| Setor | Capacidade |
|-------|------------|
| PISTA | 221 |
| BACKSTAGE | 197 |

---

## 3. Seed Script - `ListaGeralSeeder`

### 3.1 Responsáveis (Users tipo PROMOTER)

| # | Nome | Setor(s) | Qtd Guests |
|---|------|----------|------------|
| 1 | da Britt do MKT (Ana) | PISTA | 2 |
| 2 | Erick | PISTA + BACKSTAGE | 9 + 52 = 61 |
| 3 | Miler | PISTA + BACKSTAGE | 61 + 20 = 81 |
| 4 | Imprensa | BACKSTAGE | 18 |
| 5 | Boka | BACKSTAGE | 3 |
| 6 | Houde Mag | BACKSTAGE | 2 |
| 7 | Angélica | BACKSTAGE | 3 |
| 8 | Eric Oliver | BACKSTAGE | 85 |
| 9 | Timelapse | PISTA + BACKSTAGE | 8 + 2 = 10 |
| 10 | Adriano | PISTA | 19 |
| 11 | Ardito | PISTA + BACKSTAGE | 35 + 4 = 39 |
| 12 | Sinisgali | PISTA + BACKSTAGE | 30 + 2 = 32 |
| 13 | MDAccula | PISTA + BACKSTAGE | 57 + 6 = 63 |

### 3.2 Estrutura de Dados

```
1 Event
├── 2 Sectors (PISTA, BACKSTAGE)
├── 13 Users (role: PROMOTER)
├── 13-26 EventAssignments (permissions)
└── 418 Guests (cortesia)
```

### 3.3 Regras de Negócio

- Todos são **cortesia** (sem valor, `ticket_price = 0`)
- Cada promoter terá `guest_limit` = quantidade de convidados dele
- Guests não têm email (nullable)
- Document type: inferido do formato
  - 11 dígitos numéricos → CPF
  - Começa com "Passaporte" → Passaporte
  - Começa com "RG" → RG

### 3.4 Parser de Documentos

```php
// Limpar documentos antes de salvar
function normalizeDocument(string $doc): string {
    // CPF com espaços: "300 386 398 31" → "30038639831"
    if (preg_match('/^\d{3}\s+\d{3}\s+\d{3}\s+\d{2}$/', $doc)) {
        return preg_replace('/\s+/', '', $doc);
    }
    // Passaporte: "Passaporte N07913844" → "Passaporte N07913844"
    // RG: "RG598787343" → "RG598787343"
    return trim($doc);
}
```

---

## 4. Sistema de Backup/Restore

### 4.1 Comandos Artisan

```bash
# Criar backup
php artisan backup:create

# Listar backups
php artisan backup:list

# Restaurar backup
php artisan backup:restore {filename}

# Deletar backup
php artisan backup:delete {filename}
```

### 4.2 Estrutura de Arquivos

```
storage/app/backups/
├── backup_2026_04_23_120000.sql
├── backup_2026_04_22_180000.sql
└── ...
```

### 4.3 Implementação

- Para **SQLite**: copiar arquivo `.sqlite`
- Para **MySQL**: usar `mysqldump`
- Salvar em `storage/app/backups/`
- Registrar no activity log
- Manter últimos 10 backups (cleanup automático)

### 4.4 Página UI Admin

- **Rota**: `/admin/backups`
- **Título**: "Gestão de Backups"
- **Conteúdo**:
  - Tabela com: Data/Hora, Tamanho, Ações
  - Botão "Criar Backup" (POST)
  - Ações: Download, Restaurar, Deletar

---

## 5. Relatório Consolidado de Cortesias

### 5.1 Objetivo

Fornecer relatório gerencial de todos os ingressos de cortesia distribuídos para o evento.

### 5.2 Nova Página

| Atributo | Valor |
|----------|-------|
| **Rota** | `/admin/reports/guests-summary` |
| **Título** | "Relatório de Cortesias" |
| **Menu** | Admin > Relatórios |
| **Ordem** | Após "Relatório Analítico" |

### 5.3 Filtros

| Filtro | Tipo | Default |
|--------|------|---------|
| Evento | Select | Último evento selecionado |

### 5.4 Tabela Consolidada

| Responsável | PISTA | BACKSTAGE | TOTAL | Entregues | Validados |
|-------------|-------|-----------|-------|-----------|-----------|
| da Britt do MKT (Ana) | 2 | 0 | 2 | 2 | 0 |
| Erick | 9 | 52 | 61 | 61 | 0 |
| Miler | 61 | 20 | 81 | 81 | 0 |
| ... | ... | ... | ... | ... | ... |
| **TOTAL** | **221** | **197** | **418** | **418** | **0** |

**Legenda:**
- **PISTA**: Cortesias de pista
- **BACKSTAGE**: Cortesias de backstage
- **TOTAL**: Soma PISTA + BACKSTAGE
- **Entregues**: Cadastrados no sistema
- **Validados**: Com check-in confirmado

### 5.5 Detalhamento

Ao clicar em uma linha, expande mostrando lista de nomes:

```
Responsável: Erick
Setor: BACKSTAGE
────────────────────────────────
1. Wellington Miranda - 27589191841
2. Priscilla Stocco - 22010456823
...
```

### 5.6 Exportação

**PDF:**
- Template: `resources/views/pdf/guests-report.blade.php`
- Estilo: Similar ao `cash-closing.blade.php`
- Cabeçalho: Evento, Data de geração, Usuário
- Tabela consolidada + breakdown

**Excel:**
- Aba "Consolidado": Tabela resumida
- Aba "Detalhado": Lista completa
- Colunas: Responsável | Setor | Nome Convidado | Documento

---

## 6. Estrutura de Arquivos a Criar

```
app/
├── Console/Commands/
│   ├── BackupCreateCommand.php      # backup:create
│   ├── BackupListCommand.php       # backup:list
│   ├── BackupRestoreCommand.php    # backup:restore {filename}
│   └── BackupDeleteCommand.php     # backup:delete {filename}
├── Filament/Admin/Resources/
│   └── BackupResource/             # (se necessário)
│       └── ...
database/seeders/
└── ListaGeralSeeder.php
resources/views/pdf/
└── guests-report.blade.php
```

### Arquivos a Modificar

```
app/Providers/Filament/AdminPanelProvider.php  # Registrar páginas de relatório
```

---

## 7. Dependências

Nenhuma - já temos:
- `barryvdh/laravel-dompdf` - PDF
- `maatwebsite/excel` - Excel

---

## 8. Ordem de Implementação

1. **ListaGeralSeeder** - Seed script com parser
2. **Backup Commands** - Sistema de backup/restore
3. **Backup UI** - Página admin de gestão
4. **Relatório Cortesias** - Página + Lógica
5. **Exportação PDF** - Template + Controller
6. **Exportação Excel** - Export class

---

## 9. Testes

### Seed Script
- [ ] Evento criado com dados corretos
- [ ] 13 users criados
- [ ] 418 guests criados (221 PISTA + 197 BACKSTAGE)
- [ ] Não há duplicados ao rodar duas vezes

### Backup
- [ ] Backup criado com sucesso
- [ ] Restore funciona corretamente
- [ ] Activity log registra operações

### Relatório
- [ ] Totais batem com quantidade de guests
- [ ] Filtro por evento funciona
- [ ] Expansão mostra lista correta
- [ ] PDF gerado corretamente
- [ ] Excel gerado corretamente

---

## 10. Rollback Plan

Se algo falhar:
```bash
# Limpar dados do seed
php artisan migrate:fresh --seed=DatabaseSeeder

# Não rodar ListaGeralSeeder novamente sem limpar
```
