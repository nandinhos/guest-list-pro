# Guest List Pro: Padrões de Desenvolvimento & Lições Aprendidas

Este documento serve como guia obrigatório para o Agente de IA e desenvolvedores. Antes de iniciar qualquer feature, realize a **Verificação Inicial** baseada nos índices abaixo.

## 📌 Índice
1. [🚀 Verificação Inicial (Checklist)](#-verificação-inicial-checklist)
2. [🌐 Estabilidade JS & SPA](#-estabilidade-js--spa)
3. [📋 Filament & Enums](#-filament--enums)
4. [🛡️ Integridade de Dados & Duplicidade](#-integridade-de-dados--duplicidade)
5. [📱 Mobile UX & Responsividade](#-mobile-ux--responsividade)
6. [🏗️ Arquitetura & Camada de Serviço](#-arquitetura--camada-de-serviço)

---

## 🚀 Verificação Inicial (Checklist)
- [ ] O novo recurso afetará a navegação? (Se sim, verifique se o SPA está desabilitado para evitar erros de JS).
- [ ] A feature utiliza Enums em campos `Select`? (Aplique o protocolo de verificação de instância).
- [ ] Há inserção de dados sensíveis (Documentos/Nomes)? (Utilize o `ApprovalRequestService` para evitar duplicidade).
- [ ] A visualização é compatível com mobile? (Use o padrão `mobile_card.blade.php` sem scroll horizontal).

---

## 🌐 Estabilidade JS & SPA
**Lição**: O modo SPA do Filament (`->spa()`) causa redeclaração de scripts globais e falhas no ciclo de vida de plugins externos ao navegar entre componentes Livewire.
- **Protocolo**: Manter `->spa()` desabilitado em todos os `PanelProviders` (`Admin`, `Bilheteria`, `Validator`, `Promoter`).
- **Sintomas de erro**: 
    - `Identifier 'loadDarkMode' has already been declared`.
    - `Cannot read properties of null (reading 'destroy')` no Chart.js.
    - Componentes JS (como Masks ou Modais) parando de funcionar após navegação.

---

## 📋 Filament & Enums
**Lição**: Ao usar Enums em Selects, o Filament pode injetar a *instância* do Enum nos callbacks de `Get $get`.
- **Protocolo**: Sempre validar o tipo antes de operar.
- **Exemplo**:
```php
$type = $get('document_type');
$enum = $type instanceof DocumentType ? $type : DocumentType::tryFrom($type ?? '');
```

---

## 🛡️ Integridade de Dados & Duplicidade
**Lição**: Erros de banco de dados (`Unique Constraint`) degradam a experiência. A validação deve ser proativa e normalizada.
- **Protocolo**: 
    - Usar `ApprovalRequestService::checkForDuplicates`.
    - Sempre comparar documentos usando a versão normalizada (`document_normalized`).
    - Permitir exclusão do ID atual durante a edição (`$excludeGuestId`).

---

## 📱 Mobile UX & Responsividade
**Lição**: Tabelas horizontais em mobile são proibidas. O uso incorreto de componentes de layout em tabelas gera erros fatais.
- **Protocolo**:
    - Usar `Filament\Tables\Columns\ViewColumn` para renderizar cards mobile (`mobile_card.blade.php`).
    - **CRÍTICO**: Nunca use `Filament\Tables\Columns\Layout\View` como coluna de topo; ele não suporta métodos como `label()` ou `hiddenFrom()`.
    - **Sintaxe Correta**: `ViewColumn::make('mobile_card')->view('caminho.da.view')`.
    - Esconder colunas desktop via `visibleFrom('md')`.
    - Integrar botões de ação (Editar/Deletar) dentro do próprio card para economizar espaço via `mountTableAction`.

---

## 🏗️ Arquitetura & Camada de Serviço
**Lição**: Lógica de negócio não deve poluir Models ou Filament Pages.
- **Protocolo**:
    - Criar/Manter lógica no diretório `app/Services`.
    - Centralizar validações complexas em FormRequests ou Services.
    - Utilizar `GuestSearchService` para buscas performáticas e sanitizadas.

---
*Ultima atualização: Janeiro 2026*
