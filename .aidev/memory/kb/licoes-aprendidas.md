# Lições Aprendidas — GuestListPro

> Base de conhecimento viva de padrões, correções e boas práticas do projeto.

---

## 🏗️ Arquitetura & Performance

### LL-001 — Overhead do `vendor/bin/sail` vs `docker compose exec`
**Contexto**: O wrapper Sail adiciona overhead de inicialização (~30s+).
**Sintoma**: Comandos rápidos como `pint` ou `artisan test` demoram excessivamente.
**Padrão**: Usar `docker compose exec -T laravel.test <comando>` para execução direta e rápida no container.
**Exemplo**: `docker compose exec -T laravel.test php artisan test --compact`

### LL-002 — Banco de Dados: Performance e Integridade
**Contexto**: Consultas lentas em tabelas de alto volume (Convidados, Vendas).
**Lição**: Adicionar índices específicos para campos de busca e filtros frequentes.
**Padrão**: Usar `document_normalized` para chaves únicas de documentos (evita duplicidade por formatação).

### LL-003 — Seeders Idempotentes
**Contexto**: Seeders que falham ao rodar mais de uma vez limpam ou duplicam dados.
**Padrão**: Sempre usar `Model::firstOrCreate([...])` para garantir que o seeder possa ser executado múltiplas vezes sem efeitos colaterais.

---

## 🎨 Frontend & UX

### LL-004 — Filament v4: Navegação SPA e Estabilidade
**Contexto**: O modo SPA do Filament (`->spa()`) é fundamental para fluidez, mas pode causar redeclaração de scripts e flashes brancos.
**Lição Histórica**: Antigamente desativávamos (`->spa(false)`), mas o padrão v4 deve ser `->spa(true)`.
**Padrão**: Manter `->spa(true)`. Se houver erros de JS (ex: `Identifier '...' has already been declared`), encapsular scripts com verificações de existência ou usar o ciclo de vida do Livewire (`livewire:init`).

### LL-005 — Mobile-First e Componentes de Tabela
**Contexto**: Tabelas horizontais em mobile degradam a experiência.
**CRÍTICO**: Nunca use `Filament\Tables\Columns\Layout\View` como coluna de topo; ele não suporta métodos de Resource (labels/hidden).
**Padrão**: Usar `Filament\Tables\Columns\ViewColumn` para renderizar cards mobile customizados e esconder colunas desktop via `visibleFrom('md')`.

### LL-006 — IDs de Âncoras e Smooth Scroll
**Contexto**: Links de navegação na landing page usando IDs com caracteres especiais (`#panéis`).
**Problema**: Scroll suave não funciona em alguns browsers/charsets.
**Padrão**: IDs de âncoras devem ser sempre **ASCII Puro** (ex: `id="paineis"`, `id="benefits"`). O texto visível pode ter acento, a âncora não.

---

## ⚙️ Backend & Lógica de Negócio

### LL-007 — Validação de Enums no Filament
**Contexto**: Ao usar `Get $get` em Selects com Enums, o Filament pode retornar a *instância* do Enum ou a *string*.
**Padrão**: Sempre validar o tipo antes de comparar ou operar:
```php
$type = $get('type');
$enum = $type instanceof MyEnum ? $type : MyEnum::tryFrom($type ?? '');
```

### LL-008 — Notificações de Banco de Dados (Database Notifications)
**Contexto**: Erros de serialização ao usar Actions em notificações persistentes no banco.
**CRÍTICO**: Notificações que vão para o banco (`toArray()`) NÃO suportam `Filament\Actions\Action`.
**Padrão**: Usar `getDatabaseMessage()` puro. Actions são permitidas apenas em notificações flash (`toFilament()`).

### LL-009 — Rota `password.request` do Laravel
**Contexto**: Erros de rota não encontrada ao apontar para esqueci senha sem Breeze/Jetstream.
**Padrão**: Verificar se as rotas de auth estão registradas antes de usar helpers de rota. Usar URLs diretas ou placeholders em layouts de auth customizados até que a feature seja implementada.

---

## 🧪 Testes & Qualidade

### LL-010 — TDD e Testes Travados (Docker)
**Contexto**: Testes com `RefreshDatabase` travando no container Docker sem output.
**Mitigação**: 
1. Rodar testes focados com `--filter`.
2. Usar `command_status` para monitorar processos demorados.
3. Se o teste envolver banco, prefira verificação manual com users do `UserSeeder` para validações rápidas de fluxo de tela.

### LL-011 — Pint e Formatação Automática
**Padrão**: Sempre rodar `./vendor/bin/pint --dirty` antes de cada commit para manter a consistência do código de acordo com o padrão Laravel.

---
*Atualizado em: 18 de fevereiro de 2026*
