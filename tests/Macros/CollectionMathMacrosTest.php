<?php

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

it('normalizes a collection by dividing each item by the sum', function () {
    $collection = collect(['a' => 10, 'b' => 20, 'c' => 20]);
    $result = $collection->normalizeBySum();

    expect($result->toArray())->toEqual(['a' => 0.2, 'b' => 0.4, 'c' => 0.4]);
});

it('returns an empty collection when normalizing an empty collection', function () {
    $collection = collect([]);
    $result = $collection->normalizeBySum();

    expect($result->isEmpty())->toBeTrue();
});

it('throws an exception when normalizing a collection whose sum is zero', function () {
    $collection = collect(['a' => 0, 'b' => 0]);

    expect(fn () => $collection->normalizeBySum())
        ->toThrow(InvalidArgumentException::class, 'Sum of elements cannot be zero.');
});

it('rounds using the largest remainder method perfectly', function () {
    $collection = collect(['a' => 33.333, 'b' => 33.333, 'c' => 33.333]);
    // 33.333 floored is 33. Sum is 99. Difference = 1.
    // Remainders all .333. Top gets +1.
    $result = $collection->largestRemainderRound(100);

    expect($result->toArray())->toEqual(['a' => 34, 'b' => 33, 'c' => 33]);
    expect($result->sum())->toBe(100);
});

it('rounds distinct values using largest remainder method', function () {
    $collection = collect([
        '15-19' => 2.4,
        '20-34' => 7.5,
        '35-49' => 8.5,
        '50-64' => 7.4,
        '65 & above' => 3.5,
    ]);

    $result = $collection->largestRemainderRound(29);

    expect($result->toArray())->toEqual([
        '15-19' => 2,
        '20-34' => 8,
        '35-49' => 9,
        '50-64' => 7,
        '65 & above' => 3,
    ]);
    expect($result->sum())->toBe(29);
});

it('returns an empty collection if largest remainder is called on an empty collection', function () {
    $collection = collect([]);
    $result = $collection->largestRemainderRound(100);
    expect($result->isEmpty())->toBeTrue();
});

it('safeguards against negative differences in largest remainder round', function () {
    // Floored values sum to 105. Target is 100. Diff is -5.
    $collection = collect(['a' => 50.4, 'b' => 55.5]);
    $result = $collection->largestRemainderRound(100);

    // Should return essentially the floored values mapped to keys without trying to subtract.
    expect($result->toArray())->toEqual(['a' => 50, 'b' => 55]);
});
