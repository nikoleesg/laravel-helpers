# OneMap API Helper — User Manual

The OneMap API helper provides a Laravel-friendly client for OneMap authentication, address search, and WGS84 reverse geocoding.

Phase 1 focuses on server-side API access for common address workflows:

- Generate and cache OneMap API tokens.
- Search for Singapore addresses, roads, buildings, postal codes, and POIs.
- Reverse geocode browser/GPS latitude and longitude coordinates.

## Scope

Included in Phase 1:

| Feature | Endpoint |
|---|---|
| Auth token | `POST /api/auth/post/getToken` |
| Search | `GET /api/common/elastic/search` |
| Reverse geocode, WGS84 only | `GET /api/public/revgeocode` |

Excluded from Phase 1:

- SVY21 reverse geocode (`/api/public/revgeocodexy`)
- Map services and basemap URLs
- Static maps
- Routing
- Coordinate conversion
- Themes, planning areas, and population APIs

## Configuration

Publish the package config:

```bash
php artisan vendor:publish --tag="laravel-helpers-config"
```

Set your OneMap credentials in `.env`:

```dotenv
ONEMAP_EMAIL=your-email@example.com
ONEMAP_PASSWORD=your-password
```

Optional configuration:

```dotenv
ONEMAP_BASE_URL=https://www.onemap.gov.sg
ONEMAP_TOKEN=
ONEMAP_TOKEN_CACHE_KEY=laravel-helpers:onemap:token
ONEMAP_TOKEN_CACHE_BUFFER_SECONDS=300
ONEMAP_TIMEOUT=10
```

Expected config shape:

```php
return [
    'onemap' => [
        'base_url' => env('ONEMAP_BASE_URL', 'https://www.onemap.gov.sg'),
        'email' => env('ONEMAP_EMAIL'),
        'password' => env('ONEMAP_PASSWORD'),
        'token' => env('ONEMAP_TOKEN'),
        'token_cache_key' => env('ONEMAP_TOKEN_CACHE_KEY', 'laravel-helpers:onemap:token'),
        'token_cache_buffer_seconds' => env('ONEMAP_TOKEN_CACHE_BUFFER_SECONDS', 300),
        'timeout' => env('ONEMAP_TIMEOUT', 10),
    ],
];
```

## Token Management

Recommended behaviour for the helper:

1. Use a manually configured or supplied token when available.
2. Otherwise, retrieve a token using `ONEMAP_EMAIL` and `ONEMAP_PASSWORD`.
3. Cache the token until shortly before OneMap's `expiry_timestamp`.
4. Re-authenticate automatically after the cached token expires.

OneMap tokens are valid for 3 days. The expiry timestamp returned by OneMap is a Unix timestamp.

> Keep OneMap credentials server-side. Do not expose your email, password, or token in browser JavaScript.

## Usage

### Resolve a token

```php
use Nikoleesg\LaravelHelpers\Facades\OneMap;

$token = OneMap::getToken();

// [
//     'access_token' => '...',
//     'expiry_timestamp' => '1689388144',
// ]
```

### Search by postal code

```php
use Nikoleesg\LaravelHelpers\Facades\OneMap;

$results = OneMap::search('200640');

$first = $results['results'][0] ?? null;

$address = $first['ADDRESS'] ?? null;
$latitude = $first['LATITUDE'] ?? null;
$longitude = $first['LONGITUDE'] ?? null;
```

The default search options should request both geometry and address details:

```php
$results = OneMap::search(
    searchVal: '200640',
    returnGeom: true,
    getAddrDetails: true,
    pageNum: 1,
);
```

### Search by building or road keyword

```php
$results = OneMap::search('Rowell Road');
```

### Reverse geocode browser coordinates

Browser geolocation returns WGS84 latitude and longitude, which matches OneMap's `/api/public/revgeocode` endpoint.

Client-side JavaScript:

```js
navigator.geolocation.getCurrentPosition((position) => {
    const latitude = position.coords.latitude;
    const longitude = position.coords.longitude;

    fetch('/api/location/reverse-geocode', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ latitude, longitude }),
    });
});
```

Server-side Laravel code:

```php
use Illuminate\Http\Request;
use Nikoleesg\LaravelHelpers\Facades\OneMap;

Route::post('/api/location/reverse-geocode', function (Request $request) {
    $data = $request->validate([
        'latitude' => ['required', 'numeric', 'between:-90,90'],
        'longitude' => ['required', 'numeric', 'between:-180,180'],
    ]);

    return OneMap::reverseGeocode(
        latitude: (float) $data['latitude'],
        longitude: (float) $data['longitude'],
        buffer: 40,
        addressType: 'All',
    );
});
```

### Include other features

OneMap supports an optional `otherFeatures` flag for reservoirs, playgrounds, jetties, and similar features.

```php
$results = OneMap::reverseGeocode(
    latitude: 1.3254295,
    longitude: 103.9005321,
    buffer: 40,
    addressType: 'All',
    otherFeatures: true,
);
```

### Use a manual token

If your application manages OneMap tokens outside this package, pass the token explicitly or configure `ONEMAP_TOKEN`.

```php
$results = OneMap::search(
    searchVal: '200640',
    token: $token,
);
```

## Response Shapes

The helper returns OneMap's decoded JSON response arrays without renaming fields.

Search responses include fields such as:

```php
[
    'found' => 1,
    'totalNumPages' => 1,
    'pageNum' => 1,
    'results' => [
        [
            'SEARCHVAL' => '640 ROWELL ROAD SINGAPORE 200640',
            'ADDRESS' => '640 ROWELL ROAD SINGAPORE 200640',
            'POSTAL' => '200640',
            'LATITUDE' => '1.30743547948389',
            'LONGITUDE' => '103.854713903431',
        ],
    ],
]
```

Reverse geocode responses include `GeocodeInfo`:

```php
[
    'GeocodeInfo' => [
        [
            'BUILDINGNAME' => 'KAMPONG UBI VIEW',
            'BLOCK' => '351',
            'ROAD' => 'UBI AVENUE 1',
            'POSTALCODE' => '400351',
            'LATITUDE' => '1.325486284730739',
            'LONGITUDE' => '103.90072773995409',
        ],
    ],
]
```

## Validation

The helper validates obvious client-side mistakes before calling OneMap:

| Input | Validation |
|---|---|
| `searchVal` | Non-empty string |
| `pageNum` | Integer greater than or equal to 1 |
| `latitude` | Between `-90` and `90` |
| `longitude` | Between `-180` and `180` |
| `buffer` | Between `0` and `500` when supplied |
| `addressType` | `All` or `HDB` when supplied |

Invalid local input throws `InvalidArgumentException`.

## Testing Guidance

Application tests should fake OneMap requests instead of calling live endpoints.

Package tests should use Laravel's HTTP fake tools to verify:

- Auth request body
- Authorization header on protected requests
- Search query parameters
- Reverse geocode `location` formatting
- Token caching and refresh behaviour
- Validation failures
