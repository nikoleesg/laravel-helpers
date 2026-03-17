<?php

namespace Nikoleesg\LaravelHelpers\Tests\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Nikoleesg\LaravelHelpers\Traits\HasUuid;

beforeEach(function () {
    Schema::create('uuid_models', function (Blueprint $table) {
        $table->id();
        $table->uuid('uuid')->nullable();
        $table->timestamps();
    });

    Schema::create('custom_uuid_models', function (Blueprint $table) {
        $table->id();
        $table->uuid('my_custom_uuid')->nullable();
        $table->timestamps();
    });

    Schema::create('primary_uuid_models', function (Blueprint $table) {
        $table->uuid('id')->primary();
        $table->string('name')->nullable();
        $table->timestamps();
    });
});

class UuidModel extends Model
{
    use HasUuid;

    protected $guarded = [];
}

class CustomUuidModel extends Model
{
    use HasUuid;

    protected $guarded = [];

    protected $uuidColumn = 'my_custom_uuid';
}

class RouteKeyUuidModel extends Model
{
    use HasUuid;

    protected $table = 'uuid_models';

    protected $guarded = [];

    protected $useUuidForRouteKey = true;
}

class PrimaryKeyUuidModel extends Model
{
    use HasUuid;

    protected $table = 'primary_uuid_models';

    protected $guarded = [];

    protected $useUuidAsPrimaryKey = true;

    protected $uuidColumn = 'id';
}

it('automatically generates a uuid on creation', function () {
    $model = UuidModel::create();

    expect($model->uuid)->not->toBeNull()
        ->and(Str::isUuid($model->uuid))->toBeTrue();
});

it('uses a custom column name when defined', function () {
    $model = CustomUuidModel::create();

    expect($model->my_custom_uuid)->not->toBeNull()
        ->and(Str::isUuid($model->my_custom_uuid))->toBeTrue()
        ->and($model->getUuidColumn())->toBe('my_custom_uuid');
});

it('does not overwrite an existing uuid', function () {
    $uuid = (string) Str::uuid();
    $model = UuidModel::create(['uuid' => $uuid]);

    expect($model->uuid)->toBe($uuid);
});

it('uses uuid as the route key when configured', function () {
    $model = new RouteKeyUuidModel;

    expect($model->getRouteKeyName())->toBe('uuid');

    $defaultModel = new UuidModel;
    expect($defaultModel->getRouteKeyName())->toBe('id');
});

it('uses uuid as primary key when configured', function () {
    $model = new PrimaryKeyUuidModel;

    expect($model->getKeyName())->toBe('id')
        ->and($model->getIncrementing())->toBeFalse()
        ->and($model->getKeyType())->toBe('string');
});

it('creates model using uuid as primary key successfully', function () {
    $model = PrimaryKeyUuidModel::create(['name' => 'test']);

    expect($model->id)->not->toBeNull()
        ->and(Str::isUuid($model->id))->toBeTrue();
});
