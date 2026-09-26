<?php

declare(strict_types=1);

namespace Nikoleesg\LaravelHelpers\OneMap;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

class OneMapClient
{
    protected string $baseUrl;

    protected ?string $email;

    protected ?string $password;

    protected ?string $manualToken;

    protected string $cacheKey;

    protected int $expiryBuffer;

    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl = config('helpers.onemap.base_url', 'https://www.onemap.gov.sg');
        $this->email = config('helpers.onemap.email');
        $this->password = config('helpers.onemap.password');
        $this->manualToken = config('helpers.onemap.token');
        $this->cacheKey = config('helpers.onemap.cache_key', 'onemap_api_token');
        $this->expiryBuffer = config('helpers.onemap.expiry_buffer', 300);
        $this->timeout = config('helpers.onemap.timeout', 10);
    }

    public function getToken(?string $email = null, ?string $password = null): array
    {
        $email ??= $this->email;
        $password ??= $this->password;

        if (empty($email) || empty($password)) {
            throw new InvalidArgumentException('OneMap credentials (email and password) are required to generate a token.');
        }

        $response = Http::timeout($this->timeout)
            ->baseUrl($this->baseUrl)
            ->post('/api/auth/post/getToken', [
                'email' => $email,
                'password' => $password,
            ]);

        $response->throw();

        return $response->json();
    }

    public function resolveToken(?string $token = null): string
    {
        if ($token !== null) {
            return $token;
        }

        if ($this->manualToken !== null) {
            return $this->manualToken;
        }

        if (Cache::has($this->cacheKey)) {
            return Cache::get($this->cacheKey);
        }

        $response = $this->getToken();

        $token = $response['access_token'];
        $expiryTimestamp = (int) $response['expiry_timestamp'];

        $ttl = max(0, $expiryTimestamp - time() - $this->expiryBuffer);

        if ($ttl > 0) {
            Cache::put($this->cacheKey, $token, $ttl);
        }

        return $token;
    }

    public function search(
        string $searchVal,
        bool $returnGeom = true,
        bool $getAddrDetails = true,
        int $pageNum = 1,
        ?string $token = null,
    ): array {
        if (empty(trim($searchVal))) {
            throw new InvalidArgumentException('Search value cannot be empty.');
        }

        if ($pageNum < 1) {
            throw new InvalidArgumentException('Page number must be 1 or greater.');
        }

        $response = Http::timeout($this->timeout)
            ->baseUrl($this->baseUrl)
            ->withToken($this->resolveToken($token))
            ->get('/api/common/elastic/search', [
                'searchVal' => $searchVal,
                'returnGeom' => $returnGeom ? 'Y' : 'N',
                'getAddrDetails' => $getAddrDetails ? 'Y' : 'N',
                'pageNum' => $pageNum,
            ]);

        $response->throw();

        return $response->json();
    }

    public function reverseGeocode(
        float $latitude,
        float $longitude,
        ?int $buffer = null,
        string $addressType = 'All',
        bool $otherFeatures = false,
        ?string $token = null,
    ): array {
        if ($latitude < -90 || $latitude > 90) {
            throw new InvalidArgumentException('Latitude must be between -90 and 90.');
        }

        if ($longitude < -180 || $longitude > 180) {
            throw new InvalidArgumentException('Longitude must be between -180 and 180.');
        }

        if ($buffer !== null && ($buffer < 0 || $buffer > 500)) {
            throw new InvalidArgumentException('Buffer must be between 0 and 500.');
        }

        if (! in_array($addressType, ['All', 'HDB'], true)) {
            throw new InvalidArgumentException('Address type must be All or HDB.');
        }

        $query = [
            'location' => "{$latitude},{$longitude}",
            'addressType' => $addressType,
            'otherFeatures' => $otherFeatures ? 'Y' : 'N',
        ];

        if ($buffer !== null) {
            $query['buffer'] = $buffer;
        }

        $response = Http::timeout($this->timeout)
            ->baseUrl($this->baseUrl)
            ->withToken($this->resolveToken($token))
            ->get('/api/public/revgeocode', $query);

        $response->throw();

        return $response->json();
    }

    public function convertSvy21ToWgs84(
        float $x,
        float $y,
        ?string $token = null,
    ): array {
        $response = Http::timeout($this->timeout)
            ->baseUrl($this->baseUrl)
            ->withToken($this->resolveToken($token))
            ->get('/api/common/convert/3414to4326', [
                'X' => $x,
                'Y' => $y,
            ]);

        $response->throw();

        return $response->json();
    }
}
