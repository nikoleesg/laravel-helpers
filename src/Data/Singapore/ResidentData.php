<?php

namespace Nikoleesg\LaravelHelpers\Data\Singapore;

use Nikoleesg\LaravelHelpers\Enums\Gender;
use Nikoleesg\LaravelHelpers\Enums\HouseType;
use Nikoleesg\LaravelHelpers\Enums\Race;
use Spatie\LaravelData\Data;

class ResidentData extends Data
{
    public function __construct(
        public string $name,
        public int $age,
        public Race $race,
        public Gender $gender,
        public string $phone_number,
        public string $block,
        public string $street,
        public ?string $unit,
        public string $postal,
        public HouseType $house_type,
    ) {}
}
