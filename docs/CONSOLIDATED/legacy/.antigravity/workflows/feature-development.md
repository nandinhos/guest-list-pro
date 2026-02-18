# Feature Development Workflow

## Purpose
Standard workflow for implementing new features.

## Steps

### 1. Create Feature Branch
```bash
git checkout -b feature/<feature-name>
```

### 2. Understand Requirements
- Review related documentation
- Identify affected components
- List acceptance criteria

### 3. Plan Implementation
- Define interfaces/contracts first
- Break down into small tasks
- Identify dependencies

### 4. Implement
- Write tests first (TDD when possible)
- Implement the feature
- Follow coding standards
- Keep commits atomic

### 5. Test
Run all tests to ensure nothing is broken.

### 6. Review
- Self-review code changes
- Ensure documentation is updated
- Verify all tests pass

### 7. Submit
```bash
git push origin feature/<feature-name>
```
- Create pull request
- Request code review

## Checklist
- [ ] Tests written and passing
- [ ] Documentation updated
- [ ] Coding standards followed
- [ ] No breaking changes (or documented)
