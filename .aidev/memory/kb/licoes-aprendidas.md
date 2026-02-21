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

### LL-004 — Filament v4: Navegação SPA e Estabilidade (SUPERADA)
**Contexto**: O modo SPA do Filament (`->spa()`) foi testado para melhorar a fluidez.
**Lição Histórica**: Antigamente recomendava-se `->spa(true)`.
**Padrão**: **LIÇÃO SUBSTITUÍDA PELA LL-015.** Use `->spa(false)` para evitar conflitos JS.

### LL-005 — Mobile-First e Componentes de Tabela
**Contexto**: Tabelas horizontais em mobile degradam a experiência.
**CRÍTICO**: Nunca use `Filament\Tables\Columns\Layout\View` como coluna de topo; ele não suporta métodos de Resource (labels/hidden).
**Padrão**: Usar `Filament\Tables\Columns\ViewColumn` para renderizar cards mobile customizados e esconder colunas desktop via `visibleFrom('md')`.

### LL-006 — IDs de Âncoras e Smooth Scroll
**Contexto**: Links de navegação na landing page usando IDs com caracteres especiais (`#panéis`).
**Problema**: Scroll suave não funciona em alguns browsers/charsets.
**Padrão**: IDs de âncoras devem ser sempre **ASCII Puro** (ex: `id="paineis"`, `id="benefits"`). O texto visível pode ter acento, a âncora não.

### LL-012 — Glassmorphism Premium: Container e Vazamento de Layout
**Contexto**: Elementos com `absolute inset-0` dentro de containers sem `relative` vazam para o container pai mais próximo, quebrando o layout.
**Lição**: Ao criar efeitos de *glass* ou *glossy* (overlay de brilho), garantir que o elemento pai tenha `relative` e `overflow-hidden`.
**Dark Mode**: Backgrounds de página no modo escuro devem usar tokens de `surface` redefinidos na classe `.dark` (ex: `--color-surface-base`), caso contrário, mantêm a cor clara do `:root`.

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
**Padrão**: Sempre rodar `./vendor/bin/sail bin pint --dirty` antes de cada commit para manter a consistência do código de acordo com o padrão Laravel.

### LL-013 — ULID para QR Codes: Performance de Leitura
**Contexto**: Uso de QR Codes para check-in em ambientes de eventos (baixa luz, câmeras variadas).
**Lição**: Identificadores longos (UUID) aumentam a densidade de pontos do QR Code, dificultando a leitura.
**Padrão**: Usar **ULID (26 caracteres)** para tokens de QR Code. Menos caracteres geram blocos maiores e leitura 30-50% mais rápida.
**Exemplo**: `$guest->qr_token = (string) Str::ulid();`

### LL-014 — Injeção de Bibliotecas JS em Painéis Filament
**Contexto**: Carregar bibliotecas externas (ex: `html5-qrcode`) apenas onde necessário.
**Problema**: Injetar scripts dentro de componentes Livewire em modais causa erros de `undefined` no Alpine.js (corrida de carregamento).
**Padrão**: Registrar scripts essenciais no `HEAD` do painel via `renderHook` no `PanelProvider`.
**Exemplo**: `->renderHook(PanelsRenderHook::HEAD_END, fn() => '<script src="..."></script>')`

### LL-015 — Veredito SPA: Estabilidade sobre Fluidez
**Contexto**: Revisitando a lição LL-004 após bugs de redeclaração JS (`loadDarkMode`).
**Decisão**: O uso de `->spa(true)` provou-se instável para o volume de scripts customizados do projeto.
**Padrão Atual**: Manter **`->spa(false)`** em todos os `PanelProviders` para garantir limpeza total da memória JS a cada navegação e evitar erros de sintaxe por redeclaração.

### LL-016 — Sincronização de Temas Tailwind no Filament
**Contexto**: Estilos de componentes customizados sumindo após o `build` de produção.
**Causa**: O Tailwind v4 ignora arquivos fora dos diretórios padrão se não forem explicitamente mapeados.
**Padrão**: Sempre adicionar a diretiva `@source` apontando para `resources/views/components/**/*.blade.php` nos arquivos `theme.css` de cada painel.

### LL-017 — Integridade de Variáveis em Componentes Blade (@props)
**Contexto**: Erro de "Undefined variable" ao passar dados para componentes anônimos.
**Padrão**: Nunca criar componentes sem declarar `@props(['var' => default])`. Isso garante o contrato entre pai e filho e evita quebras na renderização.

### LL-018 — UX Mobile: Navegação por Página vs Modal na Edição
**Contexto**: Edição de registros complexos em modais mobile prejudica a visibilidade e uso.
**Padrão**: Para refinar a qualidade visual, usar `getUrl('edit')` para navegar para uma página dedicada em vez de `mountTableAction` (modal). 
**Dica**: Desabilitar `recordUrl(null)` na tabela se houver botões de ação explícitos no card.

### LL-019 — Centralização de Cards Customizados no Mobile
**Contexto**: Cards parecendo "deslocados" para a direita devido ao padding interno da tabela Filament.
**Solução**: Usar margem negativa e cálculo de largura compensatória: `-ml-3 w-[calc(100%+0.75rem)]`. Isso faz o card "pular" o padding e ficar centralizado na tela.

### LL-020 — Alinhamento de Topbar e Truncagem Flexbox
**Contexto**: Nome da marca e nome do usuário grudados em telas pequenas no layout fullscreen.
**Padrão**: Usar `justify-between` no container pai e aplicar `min-w-0` no bloco de texto com `truncate`. Isso força o Flexbox a calcular o espaço correto antes de aplicar o corte de texto.

---
*Atualizado em: 21 de fevereiro de 2026*
