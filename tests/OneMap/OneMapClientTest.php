<?php

declare(strict_types=1);

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Nikoleesg\LaravelHelpers\Facades\OneMap;
use Nikoleesg\LaravelHelpers\OneMap\OneMapClient;

it('can resolve OneMapClient from the container as a singleton', function () {
    $client1 = app(OneMapClient::class);
    $client2 = app(OneMapClient::class);

    expect($client1)->toBeInstanceOf(OneMapClient::class)
        ->and($client1)->toBe($client2);
});

it('can be accessed via the OneMap Facade', function () {
    $client = OneMap::getFacadeRoot();

    expect($client)->toBeInstanceOf(OneMapClient::class);
});

it('fetches token using configured credentials', function () {
    config()->set('helpers.onemap.email', 'test@example.com');
    config()->set('helpers.onemap.password', 'secret123');

    Http::fake([
        '*/api/auth/post/getToken' => Http::response([
            'access_token' => 'fake_token',
            'expiry_timestamp' => '1737700000',
        ], 200),
    ]);

    $client = app(OneMapClient::class);
    $response = $client->getToken();

    expect($response)
        ->toBeArray()
        ->toHaveKey('access_token', 'fake_token')
        ->toHaveKey('expiry_timestamp', '1737700000');

    Http::assertSent(function (Request $request) {
        return $request->url() === 'https://www.onemap.gov.sg/api/auth/post/getToken' &&
               $request['email'] === 'test@example.com' &&
               $request['password'] === 'secret123';
    });
});

it('throws InvalidArgumentException if credentials are missing', function () {
    config()->set('helpers.onemap.email', null);
    config()->set('helpers.onemap.password', null);

    $client = app(OneMapClient::class);
    $client->getToken();
})->throws(InvalidArgumentException::class, 'OneMap credentials (email and password) are required to generate a token.');

it('uses manual token from config', function () {
    config()->set('helpers.onemap.token', 'manual_token_123');

    $client = app(OneMapClient::class);
    $token = $client->resolveToken();

    expect($token)->toBe('manual_token_123');
});

it('uses supplied token parameter', function () {
    config()->set('helpers.onemap.token', 'manual_token_123');

    $client = app(OneMapClient::class);
    $token = $client->resolveToken('param_token_456');

    expect($token)->toBe('param_token_456');
});

it('caches the token and reuses it', function () {
    config()->set('helpers.onemap.email', 'test@example.com');
    config()->set('helpers.onemap.password', 'secret123');
    config()->set('cache.default', 'array');

    Http::fake([
        '*/api/auth/post/getToken' => Http::response([
            'access_token' => 'cached_token_789',
            'expiry_timestamp' => (string) (time() + 3600),
        ], 200),
    ]);

    $client = app(OneMapClient::class);
    $token1 = $client->resolveToken();
    $token2 = $client->resolveToken();

    expect($token1)->toBe('cached_token_789')
        ->and($token2)->toBe('cached_token_789');

    Http::assertSentCount(1);
});

it('searches address with correct parameters', function () {
    config()->set('helpers.onemap.token', 'test_token');

    Http::fake([
        '*/api/common/elastic/search*' => Http::response([
            'found' => 1,
            'totalNumPages' => 1,
            'pageNum' => 1,
            'results' => [
                ['SEARCHVAL' => '200640'],
            ],
        ], 200),
    ]);

    $client = app(OneMapClient::class);
    $response = $client->search('200640', false, false, 2);

    expect($response)
        ->toBeArray()
        ->toHaveKey('found', 1);

    Http::assertSent(function (Request $request) {
        return $request->url() === 'https://www.onemap.gov.sg/api/common/elastic/search?searchVal=200640&returnGeom=N&getAddrDetails=N&pageNum=2' &&
               $request->hasHeader('Authorization', 'Bearer test_token');
    });
});

it('throws InvalidArgumentException for empty search string', function () {
    $client = app(OneMapClient::class);
    $client->search('   ');
})->throws(InvalidArgumentException::class, 'Search value cannot be empty.');

it('throws InvalidArgumentException for invalid page number', function () {
    $client = app(OneMapClient::class);
    $client->search('200640', true, true, 0);
})->throws(InvalidArgumentException::class, 'Page number must be 1 or greater.');

it('reverse geocodes coordinates with correct parameters', function () {
    config()->set('helpers.onemap.token', 'test_token');

    Http::fake([
        '*/api/public/revgeocode*' => Http::response([
            'GeocodeInfo' => [
                ['BUILDINGNAME' => 'TEST BLK'],
            ],
        ], 200),
    ]);

    $client = app(OneMapClient::class);
    $response = $client->reverseGeocode(1.3254295, 103.9005321, 40, 'HDB', true);

    expect($response)
        ->toBeArray()
        ->toHaveKey('GeocodeInfo');

    Http::assertSent(function (Request $request) {
        return $request->url() === 'https://www.onemap.gov.sg/api/public/revgeocode?location=1.3254295%2C103.9005321&addressType=HDB&otherFeatures=Y&buffer=40' &&
               $request->hasHeader('Authorization', 'Bearer test_token');
    });
});

it('throws InvalidArgumentException for invalid latitude', function () {
    $client = app(OneMapClient::class);
    $client->reverseGeocode(91, 103.9);
})->throws(InvalidArgumentException::class, 'Latitude must be between -90 and 90.');

it('throws InvalidArgumentException for invalid longitude', function () {
    $client = app(OneMapClient::class);
    $client->reverseGeocode(1.3, 181);
})->throws(InvalidArgumentException::class, 'Longitude must be between -180 and 180.');

it('throws InvalidArgumentException for invalid buffer', function () {
    $client = app(OneMapClient::class);
    $client->reverseGeocode(1.3, 103.9, 501);
})->throws(InvalidArgumentException::class, 'Buffer must be between 0 and 500.');

it('throws InvalidArgumentException for invalid address type', function () {
    $client = app(OneMapClient::class);
    $client->reverseGeocode(1.3, 103.9, null, 'INVALID');
})->throws(InvalidArgumentException::class, 'Address type must be All or HDB.');

it('converts SVY21 to WGS84 with correct parameters', function () {
    config()->set('helpers.onemap.token', 'test_token');

    Http::fake([
        '*/api/common/convert/3414to4326*' => Http::response([
            'latitude' => 1.3254295,
            'longitude' => 103.9005321,
        ], 200),
    ]);

    $client = app(OneMapClient::class);
    $response = $client->convertSvy21ToWgs84(29383.0069359146, 32379.8329621008);

    expect($response)
        ->toBeArray()
        ->toHaveKey('latitude', 1.3254295);

    Http::assertSent(function (Request $request) {
        return str_starts_with($request->url(), 'https://www.onemap.gov.sg/api/common/convert/3414to4326') &&
               (float) $request['X'] === 29383.0069359146 &&
               (float) $request['Y'] === 32379.8329621008 &&
               $request->hasHeader('Authorization', 'Bearer test_token');
    });
});
