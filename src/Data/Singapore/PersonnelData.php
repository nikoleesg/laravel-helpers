<?php

namespace Nikoleesg\LaravelHelpers\Data\Singapore;

use Spatie\LaravelData\Data;
use Nikoleesg\LaravelHelpers\Enums\Gender;
use Nikoleesg\LaravelHelpers\Enums\Race;

class PersonnelData extends Data
{
    public function __construct(
        public string $name,
        public int $age,
        public Race $race,
        public Gender $gender,
        public string $phone_number,
    ) {}
}
