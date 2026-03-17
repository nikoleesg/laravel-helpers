<?php

use Illuminate\Support\Collection;

it('adds values by key between two collections', function () {
    $collection = collect(['a' => 1, 'b' => 2, 'c' => 3]);
    $result = $collection->addValueByKey(['a' => 10, 'b' => 20]);

    expect($result->toArray())->toEqual(['a' => 11, 'b' => 22, 'c' => 3]);
});

it('subtracts values by key between two collections', function () {
    $collection = collect(['a' => 10, 'b' => 20, 'c' => 30]);
    $result = $collection->subtractValueByKey(['a' => 5, 'c' => 10]);

    expect($result->toArray())->toEqual(['a' => 5, 'b' => 20, 'c' => 20]);
});

it('multiplies each item by a scalar value', function () {
    $collection = collect(['a' => 10, 'b' => 20, 'c' => 30]);
    $result = $collection->multiplyValues(2);

    expect($result->toArray())->toEqual(['a' => 20, 'b' => 40, 'c' => 60]);
});

it('divides each item by a scalar value', function () {
    $collection = collect(['a' => 10, 'b' => 20, 'c' => 30]);
    $result = $collection->divideValues(2);

    expect($result->toArray())->toEqual(['a' => 5, 'b' => 10, 'c' => 15]);
});

it('throws an exception when dividing by zero', function () {
    $collection = collect(['a' => 10, 'b' => 20]);

    expect(fn () => $collection->divideValues(0))
        ->toThrow(InvalidArgumentException::class, 'Division by zero.');
});
