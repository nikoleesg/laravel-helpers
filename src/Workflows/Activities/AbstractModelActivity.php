<?php

namespace Nikoleesg\LaravelHelpers\Workflows\Activities;

use DomainException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Nikoleesg\LaravelHelpers\Workflows\Contracts\WorkflowAggregator;
use Nikoleesg\LaravelHelpers\Workflows\Exceptions\SkipActivityException;
use Spatie\ModelStates\State;
use Throwable;

abstract class AbstractModelActivity extends AbstractIdempotentActivity
{
    /**
     * Get the Fully Qualified Class Name of the WorkflowAggregator Model.
     *
     * @return class-string<Model&WorkflowAggregator>
     */
    abstract protected function getModelClass(): string;

    /**
     * Get the state class string that this activity aims to achieve.
     * Return null if this activity doesn't move state, but still leverages the model.
     *
     * @return class-string<State>|null
     */
    abstract protected function getTargetState(): ?string;

    /**
     * Execute the side effects.
     *
     * @param Model&WorkflowAggregator $model Evaluated unlocked model
     * @param mixed ...$args Additional arguments passed into the activity
     */
    abstract protected function performAction(Model $model, mixed ...$args): void;

    /**
     * Assert any additional preconditions in a locked context before gating.
     * Useful for checking auxiliary fields.
     */
    protected function assertPreconditions(Model $model): void
    {
        // no-op
    }

    /**
     * Internal implementation of gate with Model Row Locking and Model-States validation.
     *
     * @throws Throwable
     */
    protected function gate(mixed ...$args): void
    {
        $id = $this->extractId($args);

        DB::transaction(function () use ($id) {
            $model = $this->getModelInstance($id, true);

            if ($this->getTargetState() === null) {
                $this->assertPreconditions($model);
                return;
            }

            $stateColumn = $model->getWorkflowStateColumn();
            $currentState = $model->{$stateColumn};
            $targetStateStr = $this->getTargetState();

            // Idempotency guard: If the current state order is >= to target, we skip.
            // Spatie model-states support 'order()' if we use a base state class that defines it,
            // or we must verify it. Let's assume order() exists (we can fallback if not).
            if (method_exists($currentState, 'order') && method_exists($targetStateStr, 'order')) {
                if ($currentState::order() >= $targetStateStr::order()) {
                    throw new SkipActivityException();
                }
            }

            // Ensure the state transition is valid
            if (! $currentState->canTransitionTo($targetStateStr)) {
                throw new DomainException(sprintf(
                    'Invalid workflow state transition from [%s] to [%s] for model [%s].',
                    get_class($currentState),
                    $targetStateStr,
                    get_class($model)
                ));
            }

            $this->assertPreconditions($model);
        });
    }

    /**
     * Process Side effect without database lock.
     */
    protected function sideEffect(mixed ...$args): void
    {
        $id = $this->extractId($args);
        $model = $this->getModelInstance($id, false);

        // Map arguments to pass to performAction.
        // E.g., remove the ID from the front and pass the rest
        $actionArgs = $args;
        array_shift($actionArgs);

        $this->performAction($model, ...$actionArgs);
    }

    /**
     * Commit the state transition under lock.
     */
    protected function commit(mixed ...$args): void
    {
        if ($this->getTargetState() === null) {
            return; // NO state transaction needed
        }

        $id = $this->extractId($args);

        DB::transaction(function () use ($id) {
            $model = $this->getModelInstance($id, true);
            
            $stateColumn = $model->getWorkflowStateColumn();
            $model->{$stateColumn}->transitionTo($this->getTargetState());
        });
    }

    /**
     * Grab the Model Instance.
     */
    protected function getModelInstance(int|string $id, bool $lock = false): Model
    {
        $modelClass = $this->getModelClass();
        $query = $modelClass::query();

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->findOrFail($id);
    }

    /**
     * Extract the primary key ID. We assume the first argument is always the ID for Model Activities.
     *
     * @param array $args
     * @return int|string
     */
    private function extractId(array $args): int|string
    {
        if (empty($args) || (!is_int($args[0]) && !is_string($args[0]))) {
            throw new DomainException('First argument to an AbstractModelActivity must be the primary key ID of the WorkflowAggregator Model.');
        }

        return $args[0];
    }
}
