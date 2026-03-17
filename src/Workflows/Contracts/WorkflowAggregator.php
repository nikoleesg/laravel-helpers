<?php

namespace Nikoleesg\LaravelHelpers\Workflows\Contracts;

interface WorkflowAggregator
{
    /**
     * Get the name of the state column used by the workflow aggregator model.
     * This field should be managed by spatie/laravel-model-states.
     *
     * @return string
     */
    public function getWorkflowStateColumn(): string;
}
