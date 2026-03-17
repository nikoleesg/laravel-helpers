<?php

namespace Nikoleesg\LaravelHelpers\Macros;

use InvalidArgumentException;

class CollectionMathMacros
{
    public function addValueByKey()
    {
        return function (iterable $other): static {
            $other = collect($other);

            return $this->map(function ($value, $key) use ($other) {
                return $value + $other->get($key, 0);
            });
        };
    }

    public function subtractValueByKey()
    {
        return function (iterable $other): static {
            $other = collect($other);

            return $this->map(function ($value, $key) use ($other) {
                return $value - $other->get($key, 0);
            });
        };
    }

    public function multiplyValues()
    {
        return function (int|float $multiplier): static {
            return $this->map(function ($value) use ($multiplier) {
                return $value * $multiplier;
            });
        };
    }

    public function divideValues()
    {
        return function (int|float $divisor): static {
            if ($divisor == 0) {
                throw new InvalidArgumentException('Division by zero.');
            }

            return $this->map(function ($value) use ($divisor) {
                return $value / $divisor;
            });
        };
    }

    public function normalizeBySum()
    {
        return function (): static {
            if ($this->isEmpty()) {
                return $this;
            }

            $sum = $this->sum();

            if ($sum == 0) {
                throw new InvalidArgumentException('Sum of elements cannot be zero.');
            }

            return $this->map(function ($value) use ($sum) {
                return $value / $sum;
            });
        };
    }

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

            // Optional safeguard against negative difference (target was lower than sum of floors)
            if ($difference < 0) {
                return $items->mapWithKeys(function ($item) {
                    return [$item['key'] => $item['floor']];
                });
            }

            // 3. Sort by the highest remainder
            // Using values() right after to re-index the collection to 0, 1, 2...
            $sortedByRemainder = $items->sortByDesc('remainder')->values();

            // 4. Distribute the difference (+1) to the top remainders
            for ($i = 0; $i < $difference; $i++) {
                $index = $i % $sortedByRemainder->count();

                $item = $sortedByRemainder->get($index);
                $item['floor'] += 1;
                $sortedByRemainder->put($index, $item);
            }

            // 5. Restore original order and pull out the keys
            return $this->map(function ($originalValue, $key) use ($sortedByRemainder) {
                return $sortedByRemainder->firstWhere('key', $key)['floor'];
            });
        };
    }
}
