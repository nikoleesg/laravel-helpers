<?php

namespace Nikoleesg\LaravelHelpers\Enums;

enum HouseType: int
{
    case HDB         = 1;
    case Condominium = 2;
    case Landed      = 3;
}
