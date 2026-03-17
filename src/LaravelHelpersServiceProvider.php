<?php

namespace Nikoleesg\LaravelHelpers;

use Illuminate\Support\Collection;
use Nikoleesg\LaravelHelpers\Commands\LaravelHelpersCommand;
use Nikoleesg\LaravelHelpers\Macros\CollectionMathMacros;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelHelpersServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-helpers')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel_helpers_table')
            ->hasCommand(LaravelHelpersCommand::class);
    }

    public function packageBooted(): void
    {
        Collection::mixin(new CollectionMathMacros);
    }
}
