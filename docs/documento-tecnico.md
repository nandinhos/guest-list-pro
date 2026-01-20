# Documento Técnico – Guestlist Pro

## 1. Visão Geral do Sistema

O **Guestlist Pro** é um sistema de gerenciamento de listas de convidados projetado para operar com **múltiplos eventos simultâneos**, garantindo isolamento lógico, integridade de dados e controle rigoroso de acesso por perfis (roles).

O sistema foi concebido para ambientes de alto fluxo, com necessidade de validação rápida, prevenção de fraudes, rastreabilidade de ações e geração de métricas em tempo real.

Tecnologias adotadas:

* **Laravel** (backend)
* **Livewire + Filament v4** (painéis administrativos e operacionais)
* **Tailwind CSS** (UI)
* **Alpine.js** (interações leves)
* **Docker** (ambiente isolado e reproduzível)

---

## 2. Arquitetura Conceitual

### 2.1 Entidades Principais

* **Evento**
* **Setor**
* **Lista de Convidados**
* **Convidado**
* **Usuário**
* **Check-in / Validação**
* **Bilheteria (Convite Amigo)**
* **Logs e Métricas**

### 2.2 Hierarquia de Dados

```
Evento
 └── Setores
     └── Listas de Convidados
         └── Convidados
```

Cada **Evento** é completamente isolado em termos de regras de negócio, validações e métricas.

---

## 3. Perfis de Usuário (Roles)

### 3.1 Administrador

**Permissões:**

* Acesso irrestrito ao sistema
* Cadastro e edição de eventos
* Definição de:

  * Quantidade total de convites do evento
  * Setores e limites por setor
  * Períodos (data/hora) de validade
  * Valor da bilheteria (convite amigo)
* Cadastro e vinculação de usuários aos eventos
* Definição de roles e permissões
* Visualização global de listas, check-ins e métricas
* Auditoria de duplicidades

---

### 3.2 Promoter

**Permissões:**

* Acesso restrito aos eventos e setores aos quais foi vinculado
* Gerenciamento apenas de suas próprias listas
* Cadastro de convidados por:

  * Importação via Excel
  * Campo de texto estruturado (parser por delimitadores)
* Visualização de dashboard com:

  * Total de convites disponíveis
  * Convites utilizados

**Restrições:**

* Não visualiza listas de outros promoters
* Não altera regras do evento

---

### 3.3 Validador

**Permissões:**

* Acesso apenas aos eventos designados
* Visualização completa das listas do evento
* Realização de check-in

**Funcionalidades críticas:**

* Busca por similaridade (nome e documento)
* Ignorar acentuação
* Filtros avançados
* Ações rápidas de validação

---

### 3.4 Bilheteria

**Permissões:**

* Atuação condicionada ao horário definido pelo administrador
* Emissão de **Convite Amigo**
* Registro obrigatório de cada emissão

**Objetivo:**

* Controle financeiro
* Rastreamento de entradas pagas

---

## 4. Regras de Negócio Críticas

### 4.1 Isolamento por Evento

* Nenhuma entidade pode atravessar eventos
* Todas as validações de duplicidade são feitas **dentro do escopo do evento**

---

### 4.2 Unicidade de Convidados

Regra obrigatória:

> **Não pode existir duplicidade de convidado dentro do mesmo evento**

Critérios de verificação:

* Nome (normalizado)
* Documento (somente números)

#### Normalização recomendada:

* Remoção de acentos
* Lowercase
* Trim
* Documento sem máscara

Essas validações devem ocorrer:

* Na importação
* No cadastro manual
* Antes do commit final (preview)

---

### 4.3 Períodos e Limites

* Setores podem ter:

  * Limite irrestrito
  * Limite por data/hora
* O sistema deve validar:

  * Data atual
  * Horário
  * Regra ativa do setor

---

### 4.4 Convite Amigo (Bilheteria)

* Ativado automaticamente após o horário limite
* Valor definido pelo administrador
* Cada emissão gera:

  * Registro financeiro
  * Log de operação
  * Associação ao evento

---

## 5. Fluxo de Utilização

### 5.1 Autenticação

* Login padrão
* Redirecionamento automático baseado na role

---

### 5.2 Landing Page Pós-Login

* Tela única de **seleção de evento**
* Nenhum menu lateral visível
* Apenas eventos ativos e permitidos

---

### 5.3 Pós-Seleção de Evento

* Menus e funcionalidades são carregados dinamicamente
* Baseados em:

  * Role
  * Vínculo com o evento

---

### 5.4 Fluxo do Promoter

* Seleciona evento
* Visualiza dashboard
* Gerencia listas
* Cadastra convidados
* Acompanha saldo de convites

---

### 5.5 Fluxo do Validador

* Seleciona evento
* Acessa tela de validação
* Busca por similaridade
* Realiza check-in

---

### 5.6 Fluxo do Administrador

* Visualiza eventos ativos
* Acessa métricas em tempo real
* Monitora:

  * Entradas por hora
  * Picos de acesso
  * Logs de validação

---

## 6. Métricas e Auditoria

### 6.1 Logs

* Check-ins
* Emissões de bilheteria
* Tentativas de duplicidade

### 6.2 Métricas

* Entradas por hora
* Total por setor
* Comparativo convidados x bilheteria

---

## 7. Pontos de Atenção para Auditoria por IA

### 7.1 Gargalos Potenciais

* Busca por similaridade sem indexação adequada
* Importações grandes sem processamento em fila
* Validação síncrona excessiva

---

### 7.2 Vulnerabilidades

* Falta de constraint única no banco
* Ausência de rate limit na bilheteria
* Falta de lock transacional em check-ins simultâneos

---

### 7.3 Riscos de Regra de Negócio

* Convidado cadastrado em múltiplos setores
* Check-in duplicado
* Emissão de convite amigo fora do horário

---

## 8. Recomendações Técnicas

* Uso de **UUIDs** para entidades críticas
* Índices compostos (evento_id + hash_documento)
* Jobs em fila para importações
* Locks otimistas/pessimistas no check-in
* Observers para logs automáticos

---

## 9. Objetivo do Documento

Este documento serve como:

* Base oficial de entendimento do sistema
* Entrada para agentes de IA realizarem:

  * Varredura lógica
  * Análise de segurança
  * Validação de regras de negócio
  * Identificação de inconsistências

---

**Documento preparado para análise técnica automatizada e auditoria contínua.**
