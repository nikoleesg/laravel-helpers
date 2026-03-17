<?php

namespace Nikoleesg\LaravelHelpers\Macros;

use Illuminate\Support\Collection;
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
}
