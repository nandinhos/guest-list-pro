# 📐 Plano Diretor — Aplicação de Gestão de Convidados por Evento

## 1. Objetivo da Aplicação

Criar uma aplicação web para **gestão de eventos e convidados**, com:

* Controle de acesso por perfil
* Limitação de ações por usuário (promoter)
* Check-in único por convidado
* Auditoria completa para administração
* Relatórios consolidados por evento/setor/usuário

**Princípio-chave:** simples, funcional, rastreável e confiável.

---

## 2. Perfis de Usuário (RBAC)

### 2.1 Admin

* Acesso irrestrito ao sistema
* CRUD completo de:

  * Eventos
  * Setores
  * Usuários
* Define permissões dos promoters
* Acesso à auditoria e relatórios

### 2.2 Promoter

* Acesso restrito aos eventos/setores concedidos
* Pode cadastrar convidados **respeitando limites**
* Restrições:

  * Quantidade de convites por setor
  * Janela de horário (início/fim)

### 2.3 Validador

* Acesso apenas à tela de check-in
* Busca convidados
* Executa check-in único
* Não edita dados cadastrais

---

## 3. Modelo Conceitual (Domínio)

```
Evento
 ├── Setores
 │    ├── Convidados
 │
 ├── Promoters (com limites)
 └── Validadores
```

Relacionamento resumido:

```
Evento → Setor → Convidado
Evento → Usuário (Admin / Promoter / Validador)
```

---

## 4. Entidades Principais (Modelo de Dados)

### 4.1 Usuários

* id
* nome
* email (login)
* senha_hash
* perfil (ADMIN | PROMOTER | VALIDADOR)
* status (ativo/inativo)
* created_at

---

### 4.2 Eventos

* id
* nome
* foto
* data_evento
* horario_inicio
* horario_fim
* status
* created_at

---

### 4.3 Setores

* id
* evento_id
* nome
* capacidade (opcional)
* created_at

---

### 4.4 Convidados

* id
* evento_id
* setor_id
* promoter_id
* nome
* documento (CPF/RG/passaporte)
* email (opcional)
* checked_in (bool)
* checkin_at (datetime)
* checkin_by (validador_id)
* created_at

**Regra:**
📌 *documento + evento* deve ser único

---

### 4.5 Permissões de Promoter

Tabela de concessão:

* id
* promoter_id
* evento_id
* setor_id
* limite_convites
* horario_inicio
* horario_fim
* created_at

---

### 4.6 Auditoria de Check-in

(Pode ser tabela ou derivado do convidado)

* convidado_id
* validador_id
* horario
* evento_id
* setor_id

---

## 5. Regras de Negócio (o agente NÃO pode violar)

### Cadastro de Convidados

* ❌ Não permitir duplicidade por documento no mesmo evento
* ❌ Não permitir cadastro fora do horário permitido ao promoter
* ❌ Não permitir exceder limite por setor
* ✔ Mensagem clara de erro ao usuário

---

### Check-in

* ✔ Busca por:

  * Nome (LIKE / similaridade)
  * Documento
* ❌ Apenas **um único check-in**
* ✔ Registrar:

  * Horário
  * Validador responsável

---

## 6. Telas Essenciais

### 6.1 Login

* Autenticação por email/senha
* Sessão segura

---

### 6.2 Admin

* Dashboard do evento
* Gestão de:

  * Eventos
  * Setores
  * Usuários
  * Permissões dos promoters
* Tela de auditoria:

  * Quem validou
  * Horário
  * Setor
* Relatórios exportáveis (CSV/PDF)

---

### 6.3 Promoter

* Lista de eventos/setores autorizados
* Contador de convites restantes
* Cadastro de convidados
* Feedback visual de limites

---

### 6.4 Validador

* Tela simples de busca
* Resultado rápido
* Botão de **CHECK-IN**
* Feedback imediato (verde/vermelho)

---

## 7. Relatórios (Obrigatório)

### Relatório Geral por Evento

* Evento
* Setor
* Promoter
* Total de convidados
* Total de check-ins
* Horários de pico
* Validador por check-in

📌 Deve permitir **exportação**.

---

## 8. Arquitetura Sugerida (Simples e Limpa)

### Backend

* MVC clássico
* Controllers finos
* Services para regras
* Repositórios para acesso a dados

```
app/
 ├── Controllers/
 ├── Services/
 ├── Repositories/
 ├── Models/
 ├── Middleware/
```

---

### Frontend

* Server-side render (sem SPA)
* Bootstrap ou similar
* JS apenas para UX básico

---

## 9. Fluxo Operacional Resumido

1. Admin cria evento e setores
2. Admin cria usuários
3. Admin concede permissões aos promoters
4. Promoter cadastra convidados
5. Evento acontece
6. Validador faz check-in
7. Admin acompanha métricas em tempo real
8. Pós-evento: relatório consolidado

---

## 10. Instruções Diretas para o Agente de IA

> **Diretiva principal:**
> Gere uma aplicação funcional, sem complexidade excessiva, priorizando:
>
> * integridade dos dados
> * regras de negócio claras
> * auditoria completa
> * código organizado e legível

> ❌ Não usar microserviços
> ❌ Não usar arquitetura complexa
> ✔ Priorizar clareza e manutenção

---