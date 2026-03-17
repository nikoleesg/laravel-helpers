<?php

namespace Nikoleesg\LaravelHelpers\Workflows\Traits;

use DomainException;
use InvalidArgumentException;
use Throwable;
use Workflow\Exceptions\NonRetryableException;

trait MapsWorkflowExceptions
{
    /**
     * Map common domain/validation exceptions to NonRetryableException 
     * to prevent infinite retry loops in laravel-workflow activities.
     *
     * @param Throwable $e
     * @param array<class-string<Throwable>> $additionalToMap
     * @return void
     * @throws NonRetryableException|Throwable
     */
    protected function throwAsNonRetryableIfConfigured(Throwable $e, array $additionalToMap = []): void
    {
        $nonRetryableExceptions = array_merge([
            DomainException::class,
            InvalidArgumentException::class,
            // Add more default non-retryable exceptions here if needed
        ], $additionalToMap);

        foreach ($nonRetryableExceptions as $exceptionClass) {
            if ($e instanceof $exceptionClass) {
                // To allow graceful failing of activities without infinite retry
                throw new NonRetryableException(
                    $e->getMessage(),
                    $e->getCode(),
                    $e
                );
            }
        }

        // If not mapped, throw original and let it retry (e.g. timeout, connection error)
        throw $e;
    }
}
