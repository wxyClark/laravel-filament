---
name: pint-code-style
description: "Apply this skill for code style checking and formatting using Laravel Pint. Covers PSR-12 coding standards, Laravel conventions, and automatic code formatting."
license: MIT
metadata:
  author: laravel
---

# Laravel Pint - Code Style

Laravel Pint is an opinionated PHP code style fixer built on top of PHP-CS-Fixer.

## Quick Reference

Run Pint before committing:
```bash
./vendor/bin/pint
```

Check for violations without fixing:
```bash
./vendor/bin/pint --test
```

## Coding Standards

### PSR-12
- Follow PSR-12 coding standard
- 4 spaces for indentation
- Line length: 120 characters max
- One use statement per line
- Braces on new lines for classes/functions

### Laravel Conventions

#### Naming Conventions
- **Classes**: PascalCase (e.g., `UserController`)
- **Methods**: camelCase (e.g., `getUser`)
- **Variables**: camelCase (e.g., `$userName`)
- **Constants**: SCREAMING_SNAKE_CASE (e.g., `MAX_RETRY_COUNT`)
- **Database columns**: snake_case (e.g., `created_at`)
- **Tables**: snake_case plural (e.g., `users`)

#### File Organization
- Controller: `app/Http/Controllers/`
- Model: `app/Models/`
- Migration: `database/migrations/`
- Service: `app/Services/`
- Repository: `app/Repositories/`
- Request: `app/Http/Requests/`
- Resource: `app/Http/Resources/`

### Auto-formatting Rules

#### Imports
- Sort alphabetically
- Group by: Laravel, Packages, Custom
- Remove unused imports

#### Whitespace
- No trailing whitespace
- Single blank line between methods
- No extra blank lines at end of file

#### Type Declarations
- Use strict types
- Declare return types
- Use nullable types with `?`
- Use union types where appropriate