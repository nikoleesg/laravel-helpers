<?php

namespace Nikoleesg\LaravelHelpers\Data\Singapore;

use Nikoleesg\LaravelHelpers\Enums\HouseType;
use Spatie\LaravelData\Data;

class AddressData extends Data
{
    public function __construct(
        public string $block,
        public string $street,
        public ?string $unit,
        public string $postal,
        public HouseType $house_type,
    ) {}
}
