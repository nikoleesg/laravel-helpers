<?php

namespace Nikoleesg\LaravelHelpers\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Nikoleesg\LaravelHelpers\LaravelHelpersServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Nikoleesg\\LaravelHelpers\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelHelpersServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        /*
         foreach (\Illuminate\Support\Facades\File::allFiles(__DIR__ . '/../database/migrations') as $migration) {
            (include $migration->getRealPath())->up();
         }
         */
    }
}
