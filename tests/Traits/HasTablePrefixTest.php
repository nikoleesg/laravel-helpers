<?php

namespace Nikoleesg\LaravelHelpers\Tests\Traits;

use Illuminate\Database\Eloquent\Model;
use Nikoleesg\LaravelHelpers\Traits\HasTablePrefix;

class StandardModel extends Model
{
    use HasTablePrefix;
    // Base table guessing expected: standard_models
}

class PrefixedModel extends Model
{
    use HasTablePrefix;
    protected $tablePrefix = 'inv_';
    // Base table guessing expected: inv_prefixed_models
}

class ExplicitTableModel extends Model
{
    use HasTablePrefix;
    protected $tablePrefix = 'inv_';
    protected $table = 'my_explicit_table';
    // Expected: my_explicit_table (prefix ignored)
}

it('guesses the table name normally if no prefix is set', function () {
    $model = new StandardModel();

    expect($model->getTable())->toBe('standard_models');
});

it('prefixes the guessed table name if a prefix is set', function () {
    $model = new PrefixedModel();

    expect($model->getTable())->toBe('inv_prefixed_models');
});

it('respects an explicitly defined table name and ignores the prefix', function () {
    $model = new ExplicitTableModel();

    expect($model->getTable())->toBe('my_explicit_table');
});
