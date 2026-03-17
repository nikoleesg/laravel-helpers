# Project Guidelines: `nikoleesg/laravel-helpers`

## Objective

This package is a collection of useful helpers for Laravel applications. It aims to provide utility traits, enums, collection macros, and other generic helpers that simplify everyday Laravel development.

## AI Assistant Instructions

When working on this project as the AI Assistant (Antigravity), please adhere to the following guidelines:

### 1. General Workflow

- **Plan First:** Ensure you understand the requirements provided by the user. If clarification is needed, always request it.
- **Implement Robustly:** Write clean, typed, and well-structured PHP code. Ensure full compatibility with the minimum supported PHP version defined in `composer.json` (currently PHP 8.3+).
- **Test Thoroughly:** EVERY new feature (macro, trait, enum, etc.) MUST be accompanied by comprehensive tests using Pest. Ensure edge cases (like division by zero) are covered.
- **Document Changes:** ALWAYS update the `README.md` file to document new features immediately after implementing them and before committing. Structure the documentation clearly with examples.
- **Commit Granularly:** Make clean, atomic commits with descriptive messages for each logical piece of work. Follow conventional commits (e.g., `feat:`, `fix:`, `docs:`). **CRITICAL: DO NOT commit changes without the user's explicit review and confirmation.**

### 2. Code Style

- Use strict typing where applicable (`declare(strict_types=1);` when creating new files where appropriate).
- Ensure method parameters and return types are explicitly defined.
- Format code using Laravel Pint before committing (`composer format`).
- Ensure no static analysis errors using PHPStan (`composer analyse`).

### 3. File Structure

- **Traits:** Place inside `src/Traits/`.
- **Enums:** Place inside `src/Enums/`.
- **Collection Macros:** Place inside `src/Macros/` and register them cleanly in the `LaravelHelpersServiceProvider::packageBooted` method using `Collection::mixin()`.
- **Tests:** Place in `tests/{Component}/` matching the source structure (e.g., `tests/Macros/`).

### 4. Communication

- When a functional task is completed, notify the user with a summary of changes and ask what to tackle next.

---

*These guidelines are a living document. The user may instruct you to add or modify rules as the package evolves.*
