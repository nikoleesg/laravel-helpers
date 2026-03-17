<?php

namespace Nikoleesg\LaravelHelpers\Workflows\Activities;

use Nikoleesg\LaravelHelpers\Workflows\Exceptions\SkipActivityException;
use Nikoleesg\LaravelHelpers\Workflows\Traits\MapsWorkflowExceptions;
use Throwable;
use Workflow\Activity;
use Workflow\Exceptions\NonRetryableException;

abstract class AbstractIdempotentActivity extends Activity
{
    use MapsWorkflowExceptions;

    /**
     * Default queue for activities.
     */
    public $queue = 'workflow';

    /**
     * Default tries for activities.
     */
    public $tries = 0;

    /**
     * Default timeout in seconds (override if necessary).
     */
    public $timeout = 0;

    /**
     * Define the backoff strategy for this activity.
     */
    public function backoff(): array
    {
        return [10, 60, 300, 600, 900, 1800, 3600];
    }

    /**
     * Phase 1: Pre-condition validation and gating.
     * Check idempotency. If already processed, throw SkipActivityException.
     * Use database row-locking here if necessary.
     */
    abstract protected function gate(mixed ...$args): void;

    /**
     * Phase 2: Side effects (e.g., triggering API, sending email).
     * This phase MUST be idempotent. No database locking should happen here.
     */
    abstract protected function sideEffect(mixed ...$args): void;

    /**
     * Phase 3: Committing the action (e.g., updating database record state).
     * This runs only after side effects succeed.
     */
    abstract protected function commit(mixed ...$args): void;

    /**
     * Optional post-commit hook.
     * Executed only after successful commit.
     */
    protected function afterCommit(mixed ...$args): void
    {
        // default: no-op
    }

    /**
     * The core loop that implements the 3-phase idiosyncratic retryable pattern.
     * 
     * @throws NonRetryableException|Throwable
     */
    final public function execute(mixed ...$args): mixed
    {
        try {
            // Phase 1: Pre-checks
            $this->gate(...$args);

            // Phase 2: Perform side effects without active DB locks
            $this->sideEffect(...$args);

            // Phase 3: Transition state or confirm completion
            $this->commit(...$args);

            // Phase 4: Post completion actions
            $this->afterCommit(...$args);

            return null; // Activities can override by using a tailored return structure if needed

        } catch (SkipActivityException $e) {
            // Activity safely skipped due to already being processed or state irrelevant
            return null;
        } catch (Throwable $e) {
            // Converts known domain exceptions into non-retryable exceptions
            $this->throwAsNonRetryableIfConfigured($e, $this->getNonRetryableExceptions());
            
            // Should be unreachable if mapped, will throw original otherwise
            throw $e;
        }
    }

    /**
     * Define which exceptions should permanently fail the activity instead of retrying.
     * 
     * @return array<class-string<Throwable>>
     */
    protected function getNonRetryableExceptions(): array
    {
        return [];
    }
}
