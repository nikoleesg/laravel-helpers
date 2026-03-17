# Project Guidelines: `nikoleesg/laravel-helpers`

## Objective

This package is a collection of useful helpers for Laravel applications. It aims to provide utility traits, enums, collection macros, and other generic helpers that simplify everyday Laravel development.

## AI Assistant Instructions

When working on this project as the AI Assistant (Antigravity), please adhere to the following guidelines:

### 1. General Workflow

- **Plan First:** Ensure you understand the requirements provided by the user. If clarification is needed, always request it. Write an implementation plan (`docs/model-traits-implementation-plan.md` or similar) to agree on behavior beforehand.
- **Save Implementation Plans:** When an implementation plan is agreed upon, save the `implementation_plan.md` explicitly under the `docs/` folder using an appropriate, descriptive file name. Force tracking (`git add -f`) if the directory is ignored by default.
- **Implement Robustly:** Write clean, typed, and well-structured PHP code. Ensure full compatibility with the minimum supported PHP version defined in `composer.json` (currently PHP 8.3+).
- **Integrating External Packages:** When asked to integrate 3rd party packages (e.g. `laravel-date-scopes`), directly clone the module to `/tmp/`, inspect its components, and selectively copy the necessary parts into this package's directory constraint while diligently adjusting all namespaces. Run the suite against it once transferred.
- **Test Thoroughly:** EVERY new feature (macro, trait, enum, etc.) MUST be accompanied by comprehensive tests using Pest.
  - When writing traits that target Eloquent models, ensure they are tested using dummy models located inside `tests/Models/` and `database/factories/`.
- **Document Changes:** ALWAYS update the `README.md` file to document new features immediately after implementing them and before committing. Structure the documentation clearly with examples.
- **Commit Granularly:** Make clean, atomic commits with descriptive messages for each logical piece of work. Follow conventional commits (e.g., `feat:`, `fix:`, `docs:`). **CRITICAL: DO NOT commit changes without the user's explicit review and confirmation.**

### 2. Code Style

- Use strict typing where applicable (`declare(strict_types=1);` when creating new files where appropriate).
- Ensure method parameters and return types are explicitly defined.
- Format code using Laravel Pint before committing (`composer format`).
- Ensure no static analysis errors using PHPStan (`composer analyse`).
- **Eloquent Traits:** New model traits should follow the Laravel standard naming convention (starting with `Has...`, e.g., `HasUuid`, `HasTablePrefix`).

### 3. File Structure

- **Traits:** Place inside `src/Traits/`. All Eloquent behavior modification traits belong here.
- **Enums:** Place inside `src/Enums/`.
- **Collection Macros:** Place inside `src/Macros/` and register them cleanly in the `LaravelHelpersServiceProvider::packageBooted` method using `Collection::mixin()`.
- **Tests:** Place in `tests/{Component}/` matching the source structure (e.g., `tests/Macros/`, `tests/Traits/`).

### 4. Communication

- When a functional task is completed, notify the user with a summary of changes and ask what to tackle next.

---

*These guidelines are a living document. The user may instruct you to add or modify rules as the package evolves.*
