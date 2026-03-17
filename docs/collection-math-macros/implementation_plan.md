# Goal Description

The goal is to extend `CollectionMathMacros` to include an implementation of the **Largest Remainder Method** (also known as the Hare-Niemeyer method). 

The `largestRemainderRound(int $targetSum)` macro will:
1. Floor all elements in the collection initially.
2. Calculate the difference between the sum of the floored values and the `$targetSum`.
3. Distribute the missing integer amount (the difference) exactly by $1$ to the elements that have the highest *fractional remainder*, until the resulting sum equals `$targetSum`.

> [!NOTE]  
> This macro is especially useful when computing integer percentages that *must* total exactly 100, bypassing traditional `round()` errors where the total ends up as 99 or 101.

## Proposed Usage & Edge Cases

```php
// Imagine we want to distribute 100 exactly across 3 items proportional to 1/3rd each (33.333...)
$collection = collect(['a' => 33.333, 'b' => 33.333, 'c' => 33.333]);

// Without largest remainder setting target to 100:
// Floor yields: [33, 33, 33] (Sum = 99)
// The remainder of each is .333. A tie occurs.
// The algorithm gives the missing $1$ to the first element it processes.

$result = $collection->largestRemainderRound(100);
// => Illuminate\Support\Collection { "a": 34, "b": 33, "c": 33 }  (Sum = 100)
```

```php
// An example with distinct remainders:
$collection = collect(['a' => 50.4, 'b' => 25.5, 'c' => 24.1]);
$result = $collection->largestRemainderRound(100);

// Base floors:  a=50, b=25, c=24 (Sum = 99. Target = 100. Diff = +1)
// Remainders:   a=0.4, b=0.5, c=0.1
// `b` has the highest remainder (0.5), so it gets the missing +1
// => Illuminate\Support\Collection { "a": 50, "b": 26, "c": 24 } (Sum = 100)
```

**Known Edge Cases to handle:**
1. **Empty Array**: If the collection is empty, returning an empty collection.
2. **Difference is negative**: If the sum of floored numbers happens to exceed the target sum (unlikely unless negative values are involved or edge-case floats behave weirdly). We will design it to support standard positive distribution.
3. **Preserving Keys**: Essential for named associates. We will decouple keys and values during sorting to ensure keys are preserved exactly.

## Proposed Code Structure

Inside `src/Macros/CollectionMathMacros.php`:

```php
public function largestRemainderRound()
{
    return function (int $targetSum): static {
        if ($this->isEmpty()) {
            return $this;
        }

        // 1. Calculate floor and remainder for each item, keeping original keys
        $items = $this->map(function ($value, $key) {
            $floor = (int) floor($value);
            return [
                'key' => $key,
                'floor' => $floor,
                'remainder' => $value - $floor,
            ];
        });

        // 2. Find how many +1s we need to distribute
        $currentSum = $items->sum('floor');
        $difference = $targetSum - $currentSum;

        // 3. Sort by the highest remainder
        $sortedByRemainder = $items->sortByDesc('remainder')->values();

        // 4. Distribute the difference (+1) to the top remainders
        for ($i = 0; $i < $difference; $i++) {
            // Use modulo to wrap around if difference is larger than array size (edge case)
            $index = $i % $sortedByRemainder->count();
            $sortedByRemainder[$index]['floor'] += 1;
        }

        // 5. Restore original order and pull out the keys
        return $this->map(function ($originalValue, $key) use ($sortedByRemainder) {
            return $sortedByRemainder->firstWhere('key', $key)['floor'];
        });
    };
}
```

## User Review Required

Does this implementation logic correctly encapsulate your requirement for `largestRemainderRound`? If so, I will integrate it alongside `normalizeBySum` and test them both out! 
