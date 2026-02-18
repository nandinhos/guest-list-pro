# Laravel Stack Rules

## Architecture
- Use service classes for business logic
- Controllers should be thin
- Use form requests for validation
- Apply repository pattern for data access

## Eloquent
- Define relationships explicitly
- Use scopes for reusable queries
- Avoid N+1 queries (use eager loading)
- Use model factories for testing

## Migrations
- Never modify existing migrations in production
- Use descriptive migration names
- Include rollback logic
- Seed only development data

## Routes
- Use route model binding
- Group routes logically
- Apply middleware at group level
- Use named routes

## Configuration
- Use config files, not env() in code
- Cache configuration in production
- Document all environment variables
- Use sensible defaults

## Security
- Validate all user input
- Use policies for authorization
- Escape output properly
- Follow OWASP guidelines

## PHP Standards
- Follow PSR-12 coding style
- Use strict types: `declare(strict_types=1);`
- Type hint all parameters and return values

---

**Última atualização:** 2026-02-18
