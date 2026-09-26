<?php

declare(strict_types=1);

namespace Nikoleesg\LaravelHelpers\Facades;

use Illuminate\Support\Facades\Facade;
use Nikoleesg\LaravelHelpers\OneMap\OneMapClient;

/**
 * @method static array getToken(?string $email = null, ?string $password = null)
 * @method static string resolveToken(?string $token = null)
 * @method static array search(string $searchVal, bool $returnGeom = true, bool $getAddrDetails = true, int $pageNum = 1, ?string $token = null)
 * @method static array reverseGeocode(float $latitude, float $longitude, ?int $buffer = null, string $addressType = 'All', bool $otherFeatures = false, ?string $token = null)
 *
 * @see OneMapClient
 */
class OneMap extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return OneMapClient::class;
    }
}
