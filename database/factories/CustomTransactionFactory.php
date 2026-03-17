<?php

namespace Nikoleesg\LaravelHelpers\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Nikoleesg\LaravelHelpers\Tests\Models\CustomTransaction;

class CustomTransactionFactory extends Factory
{
    protected $model = CustomTransaction::class;

    public function definition(): array
    {
        return [
            'col1' => fake()->sentence(),
            'col2' => fake()->randomNumber(),
            'custom_created_at' => fake()->date(),
        ];
    }
}
