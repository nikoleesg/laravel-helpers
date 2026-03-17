# Building Reusable Workflow Pattern

After reviewing the feedback, the goal is clear: create a firm, standardized design pattern for medium-to-complex workflows that explicitly uses an Eloquent "aggregation model" and `spatie/laravel-model-states` to manage the workflow lifecycle safely. Simultaneously, we must ensure the core design is flexible enough so that simpler workflows don't require an aggregation model if they don't need one.

## Core Design Architecture

We will implement a dual-layer approach in the `Nikoleesg\LaravelHelpers\Workflows` namespace.

### Layer 1: Flexible, Simple Idempotent Workflows

Not all workflows require a database record to track state. For simple cases (e.g., triggering a third-party API once, sending an email safely), we provide an abstract class that guarantees the 3-phase execution (`gate`, `sideEffect`, `commit`) without forcing an Eloquent model.

**`Nikoleesg\LaravelHelpers\Workflows\Activities\AbstractIdempotentActivity`**
*   **Purpose**: The absolute foundation. It enforces the `try/catch` block, idempotency gating, and side-effect isolation.
*   **Methods**:
    *   `abstract protected function gate(mixed ...$args): void;`
    *   `abstract protected function sideEffect(mixed ...$args): void;`
    *   `abstract protected function commit(mixed ...$args): void;`
*   **Use Case**: A simple workflow that accepts basic variables (e.g., `$userId`, `$emailString`) and manages its own straightforward idempotency checking (e.g., checking if a specific API has already been hit by querying a third-party endpoint, or using a simple cache lock).

### Layer 2: Complex, Model-Aggregated Workflows (The Standardized Pattern)

For medium-to-complex distributed workflows, managing state via a central Eloquent model (the "Aggregation Model") is the safest and most structured approach. This ensures the workflow is fully resumable, observable, and strictly adheres to domain rules.

**`Nikoleesg\LaravelHelpers\Workflows\Activities\AbstractModelActivity`**
*   *Extends `AbstractIdempotentActivity`*
*   **Purpose**: A firm, highly-opinionated base class for activities that rely on an Eloquent aggregation model using `spatie/laravel-model-states`.
*   **Features**:
    *   **Strict State Assumption**: It strictly assumes the use of `spatie/laravel-model-states` (e.g., invoking methods like `canTransitionTo()`, comparing `order()`).
    *   **Automatic DB Locking**: It automatically wraps `gate` and `commit` phases in a DB transaction with pessimistic locking (`lockForUpdate()`), preventing race conditions.
    *   **Automatic Idempotency Check**: Automatically throws `SkipActivityException` if the aggregation model has already reached or surpassed the target state order.
*   **Developer Experience**:
    To create a new activity for a complex workflow, a developer only needs to define standard settings. It’s highly structured and prevents them from messing up the lock/commit logic.
    ```php
    class MarkImportCompletedActivity extends AbstractModelActivity
    {
        // 1. Define the specific model being processed
        protected function getModelClass(): string
        {
            return SurveyResponseImport::class;
        }

        // 2. Define the exact state this activity moves the model towards
        protected function getTargetState(): ?string
        {
            return ImportStateCompleted::class;
        }

        // 3. Define the actual work (the side effect)
        protected function performAction(Model $model, mixed ...$args): void
        {
            // E.g., Notify completed to an external system.
        }
    }
    ```

### 3. Supporting Contracts and Traits

To enforce this firm architecture, we will provide additional tools:

*   **`Nikoleesg\LaravelHelpers\Workflows\Contracts\WorkflowAggregator`** (Interface): Any Eloquent model that acts as a workflow aggregator *must* implement this interface. It ensures the model has the necessary state properties to be managed by `AbstractModelActivity`.
*   **`Nikoleesg\LaravelHelpers\Workflows\Traits\MapsWorkflowExceptions`** (Trait): A clean way to map standard generic domain exceptions (e.g., `InvalidArgumentException`) across to the workflow engine's fatal `NonRetryableException`, simplifying error handling.
*   **`Nikoleesg\LaravelHelpers\Workflows\Exceptions\SkipActivityException`**: A generic exception to safely skip idempotent steps without failing the workflow.

## Summary of the Design

Is it better to have an Eloquent model as the aggregation model? For complex distributed workflows (like order processing, data ingestion, complex payments), **yes**. It massively improves observability, allows safe atomic state transitions, and serves as an anchor point for the durable execution engine. For simple tasks, it's overkill—which is why the `AbstractIdempotentActivity` exists natively below the Model Activity.

By building these into `nikoleesg/laravel-helpers`, any future workflow can predictably pull in these abstract base classes depending on its complexity, dramatically reducing boilerplate and preventing common concurrency/locking bugs.
