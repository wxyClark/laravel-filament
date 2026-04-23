---
name: php-development
description: "Apply this skill whenever writing, reviewing, or refactoring PHP code. Covers PHP 8.2+ features, coding standards, type safety, error handling, and best practices for modern PHP development."
license: MIT
metadata:
  author: laravel
---

# PHP Development Best Practices

Best practices for PHP 8.2+ development.

## Type Safety

- Use strict typing with `declare(strict_types=1);`
- Define return types for all methods
- Use union types and intersection types when appropriate
- Use `mixed` type sparingly, prefer specific types
- Use nullable types with `?` prefix

## Modern PHP Features

### Enums
- Use backed enums for status fields
- Use enums over constants
- Define methods on enums for behavior

### Properties
- Use readonly properties for immutable data
- Use constructor property promotion
- Avoid public mutable properties

### Match Expression
- Use `match` over `switch` for simple value mapping
- Use match expressions with multiple conditions

### Nullsafe Operator
- Use `?->` instead of null checks
- Chain nullsafe operators for clean null handling

### Named Arguments
- Use named arguments for boolean parameters
- Improve readability with named arguments

### First-Class Callables
- Use `fn() =>` syntax for anonymous functions
- Pass functions as arguments with first-class callables

## Error Handling

- Use exceptions for error handling
- Create custom exception classes
- Use try-catch blocks appropriately
- Log errors with proper context
- Never expose sensitive information in error messages

## Code Organization

- Follow PSR-12 coding standard
- Use meaningful variable and method names
- Keep methods small and focused
- Use dependency injection
- Follow single responsibility principle

## Performance

- Use `readonly` properties for memory efficiency
- Use `match` over array lookups for performance
- Avoid unnecessary object creation
- Use generators for large datasets