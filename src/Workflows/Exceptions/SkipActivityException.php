<?php

namespace Nikoleesg\LaravelHelpers\Workflows\Exceptions;

use Exception;

/**
 * Exception thrown when an activity has already been processed or 
 * determined irrelevant to continue executing for the workflow's state.
 * Expected to be caught safely and ignored by the activity runner.
 */
class SkipActivityException extends Exception
{
    //
}
