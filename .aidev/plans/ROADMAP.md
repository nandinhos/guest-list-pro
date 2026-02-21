# 🗺️ ROADMAP DE IMPLEMENTAÇÃO - laravel

> Documento mestre de planejamento de funcionalidades
> Formato: AI Dev Superpowers Sprint Planning
> Status: Ativo

---

## 📋 VISÃO GERAL

Este documento serve como **fonte única de verdade** para implementação de funcionalidades no projeto.
- ✅ Continuidade entre sessões de desenvolvimento
- ✅ Troca de LLM sem perda de contexto
- ✅ Implementação gradual por sprints
- ✅ Rastreabilidade de decisões

---

## 🎯 SPRINTS PLANEJADOS

### 📅 SPRINT 1: Lançamento & Identidade
**Objetivo:** Estabelecer a vitrine do produto e o fluxo unificado de acesso.
**Status:** ✅ Concluída (2026-02-18)

#### Funcionalidades:

##### 1.1 - Landing Page Profissional
**Prioridade:** 🔴 CRÍTICA
**Status:** ✅ Concluída

**Requisitos de Negócio:**
- Vitrine com design glassmorphism
- Apresentação de funcionalidades e benefícios
- Acesso rápido aos painéis via Role Cards

**Requisitos Técnicos:**
- [x] Componentes Livewire (Hero, Features, Benefits, RoleCards)
- [x] Layout responsivo e animado
- [x] Testes de renderização (Dívida Técnica Zerada)

##### 1.2 - Login Unificado & Estabilidade
**Prioridade:** 🔴 CRÍTICA
**Status:** ✅ Concluída

**Requisitos de Negócio:**
- Tela única de autenticação
- Redirecionamento automático por Role (Admin, Promoter, Validator, Bilheteria)
- Correção de erros JS de redeclaração (SPA Desabilitado)

**Requisitos Técnicos:**
- [x] AuthenticationService centralizado
- [x] Login Livewire com rate limiting
- [x] Testes de funcionalidade abrangentes
- [x] Desabilitação de SPA nos 4 painéis Filament

---

### 📅 SPRINT 2: Mobilidade & Check-in
**Objetivo:** Implementar o sistema de QR Code para agilizar a portaria.
**Status:** 🟡 Planejado

#### Funcionalidades:

##### 2.1 - QR Code Check-in
**Prioridade:** 🔴 CRÍTICA
**Status:** ✅ Em andamento

**Requisitos Técnicos:**
- [x] Geração de ULID automática no Guest (Fase 1)
- [x] Lógica de check-in por token no GuestService (Fase 2)
- [x] Scanner Modal Livewire (Fase 3)
- [x] Download de QR Code (Admin/Promoter) (Fase 4)
- [x] Mobile-First: Botão QR no Mobile Card (Fase 5)
- [x] Testes de Unidade e Feature (TDD) (Fase 6)

---

## 📊 RESUMO DE PRIORIDADES

| Sprint | Funcionalidade | Prioridade | Status |
|--------|----------------|------------|--------|
| 1 | Landing Page | 🔴 CRÍTICA | ✅ Concluída |
| 1 | Login Unificado | 🔴 CRÍTICA | ✅ Concluída |
| 2 | QR Code Check-in | 🔴 CRÍTICA | 🟡 Pendente |

---

## 🔄 FLUXO DE TRABALHO

1. **Antes de começar**: Use `aidev feature add "nome"` para criar o documento da feature.
2. **Durante**: Siga o checklist em `.aidev/plans/features/nome.md`.
3. **Ao finalizar**: Use `aidev feature finish "nome"` para mover para o histórico.

---

**Versão:** 1.0 (v3.7)
**Status:** Ativo