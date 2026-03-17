<?php

namespace Nikoleesg\LaravelHelpers\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Nikoleesg\LaravelHelpers\LaravelHelpers
 */
class LaravelHelpers extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Nikoleesg\LaravelHelpers\LaravelHelpers::class;
    }
}
