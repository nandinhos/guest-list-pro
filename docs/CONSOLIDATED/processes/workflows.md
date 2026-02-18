# Fluxos de Desenvolvimento

## Feature Development Workflow

### Purpose
Standard workflow for implementing new features.

### Steps

#### 1. Create Feature Branch
```bash
git checkout -b feature/<feature-name>
```

#### 2. Understand Requirements
- Review related documentation
- Identify affected components
- List acceptance criteria

#### 3. Plan Implementation
- Define interfaces/contracts first
- Break down into small tasks
- Identify dependencies

#### 4. Implement
- Write tests first (TDD when possible)
- Implement the feature
- Follow coding standards
- Keep commits atomic

#### 5. Test
Run all tests to ensure nothing is broken.

#### 6. Review
- Self-review code changes
- Ensure documentation is updated
- Verify all tests pass

#### 7. Submit
```bash
git push origin feature/<feature-name>
```
- Create pull request
- Request code review

### Checklist
- [ ] Tests written and passing
- [ ] Documentation updated
- [ ] Coding standards followed
- [ ] No breaking changes (or documented)

---

## TDD Cycle

### RED Phase
- Write a failing test first
- Run test - it MUST fail
- Verify failure is for the right reason (not syntax error)

### GREEN Phase
- Write MINIMAL code to make test pass
- Do NOT add extra features
- Do NOT optimize prematurely
- Run test - it MUST pass

### REFACTOR Phase
- Improve code quality WITHOUT changing behavior
- Run tests after EACH change
- If test fails, revert immediately

---

## Code Review Process

### 1. Contextualization
- Understand what is being reviewed
- Check git log and diff

### 2. Analysis
- Verify structure
- Analyze logic
- Check corresponding tests
- Identify code smells

### 3. Validation
```bash
# Run tests
php artisan test

# Run lint
vendor/bin/sail bin pint

# Run analysis
vendor/bin/s staticail bin phpstan
```

### 4. Documentation
- Document findings
- Provide constructive feedback

---

## Commit Rules

### Format
```
tipo(escopo): descrição em português

- Detalhe opcional
```

### Types
- `feat`: Nova funcionalidade
- `fix`: Correção de bug
- `refactor`: Mudança de código (sem nova funcionalidade)
- `test`: Adição de testes
- `docs`: Documentação
- `chore`: Manutenção

### Examples (CORRECT)
```
feat(auth): adiciona autenticacao JWT
fix(api): corrige validacao de email
refactor(utils): extrai funcao de formatacao
```

### Examples (INCORRECT)
```
# WRONG - emoji
feat(auth): :sparkles: adiciona autenticacao

# WRONG - english
feat(auth): add authentication

# WRONG - co-authorship
feat(auth): adiciona auth
Co-Authored-By: Claude <noreply@anthropic.com>
```

---

## Planning System

### Folder Structure
```
.aidev/plans/
├── backlog/      # Ideias não priorizadas
├── features/     # Planejadas com sprint
├── current/      # Em execução (sprint ativa)
├── history/      # Concluídas (arquivo por data)
└── archive/     # Documentação antiga
```

### Flow
```
backlog/ (ideia) 
    ↓ priorizada
features/ (planejada)
    ↓ sprint definida
current/ (executando)
    ↓ concluída
history/YYYY-MM/ (arquivado)
```

---

**Última atualização:** 2026-02-18
