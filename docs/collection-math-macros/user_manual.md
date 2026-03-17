# Collection Math Macros User Manual

This package provides convenient math operations that can be performed directly on Laravel Collections.

## `addValueByKey`

Adds values by key between two collections/arrays. Keeps the left-hand collection's keys. Missing keys on the right are treated as 0.

```php
$collection = collect(['a' => 1, 'b' => 2, 'c' => 3]);
$result = $collection->addValueByKey(['a' => 10, 'b' => 20]);

// => Illuminate\Support\Collection { "a": 11, "b": 22, "c": 3 }
```

## `subtractValueByKey`

Subtracts values by key between two collections/arrays. Keeps the left-hand collection's keys. Missing keys on the right are treated as 0.

```php
$collection = collect(['a' => 10, 'b' => 20, 'c' => 30]);
$result = $collection->subtractValueByKey(['a' => 5, 'c' => 10]);

// => Illuminate\Support\Collection { "a": 5, "b": 20, "c": 20 }
```

## `multiplyValues`

Multiplies each item in the collection by a scalar value.

```php
$collection = collect(['a' => 10, 'b' => 20, 'c' => 30]);
$result = $collection->multiplyValues(2);

// => Illuminate\Support\Collection { "a": 20, "b": 40, "c": 60 }
```

## `divideValues`

Divides each item in the collection by a scalar value. Throws an `InvalidArgumentException` if the divisor is zero.

```php
$collection = collect(['a' => 10, 'b' => 20, 'c' => 30]);
$result = $collection->divideValues(2);

// => Illuminate\Support\Collection { "a": 5, "b": 10, "c": 15 }
```

## `normalizeBySum`

Calculates the percentage distribution (weight) of each element relative to the sum of all elements in the collection. 
Throws an `InvalidArgumentException` if the sum of all elements is zero.

```php
$collection = collect(['a' => 10, 'b' => 20, 'c' => 20]);
$result = $collection->normalizeBySum();

// => Illuminate\Support\Collection { "a": 0.2, "b": 0.4, "c": 0.4 }
```

## `largestRemainderRound`

Rounds the collection's values to integers while guaranteeing their sum exactly matches the provided `$targetSum`. It uses the **Largest Remainder Method** (Hare-Niemeyer) to seamlessly distribute leftover fractional remainders.

```php
$collection = collect([
    '15-19' => 2.4,
    '20-34' => 7.5,
    '35-49' => 8.5,
    '50-64' => 7.4,
    '65 & above' => 3.5,
]);

$result = $collection->largestRemainderRound(29);

// The floored values sum to 27 (2+7+8+7+3). The target is 29, so the difference is +2.
// The top 2 largest fractional remainders (0.5, 0.5) get the +1s.
// => Illuminate\Support\Collection {
//      "15-19": 2,
//      "20-34": 8, // +1 (Remainder 0.5)
//      "35-49": 9, // +1 (Remainder 0.5)
//      "50-64": 7, // (Remainder 0.4)
//      "65 & above": 3 // (Remainder 0.5... but we only had 2 to give due to a tie, it misses out)
//    }
```
