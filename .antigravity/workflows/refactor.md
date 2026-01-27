# Refactor Workflow

## Purpose
Safe refactoring of existing code while maintaining functionality.

## Steps

### 1. Identify Scope
- Define what needs refactoring
- Understand current behavior
- Document existing tests

### 2. Ensure Test Coverage
- Run existing tests
- Add missing tests for current behavior
- All tests must pass before refactoring

### 3. Create Refactor Branch
```bash
git checkout -b refactor/<scope-description>
```

### 4. Refactor Incrementally
- Make small, focused changes
- Run tests after each change
- Commit frequently

### 5. Verify Behavior
- Ensure no behavioral changes
- Compare before/after outputs if applicable

### 6. Review Changes
- Check code quality improvements
- Verify performance implications
- Ensure readability improved

### 7. Update Documentation
- Update affected documentation
- Add ADR if architectural change

## Golden Rules
- Never refactor and add features simultaneously
- Tests must pass at every step
- If unsure, discuss first
