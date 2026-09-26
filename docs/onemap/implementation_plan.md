# OneMap API Helper — Implementation Plan

Implement a focused OneMap API helper for authenticated address lookup and WGS84 reverse geocoding in `nikoleesg/laravel-helpers`.

## Source of Truth

The official documentation pages are JavaScript-rendered, so plain HTML fetches return the shell application. The API details below were verified from OneMap's official bundled documentation script:

```text
https://www.onemap.gov.sg/apidocs/static/js/main.4c62b5c1.js
```

Relevant public documentation URLs:

- https://www.onemap.gov.sg/apidocs/search
- https://www.onemap.gov.sg/apidocs/authentication
- https://www.onemap.gov.sg/apidocs/reversegeocode/#wgs84

## Phase 1 Scope

Implement only:

1. Auth token API
2. Search API
3. Reverse geocode API in WGS84 latitude/longitude format

Explicitly exclude:

- SVY21 reverse geocode (`/api/public/revgeocodexy`)
- Map services, basemap tiles, minimap, advanced minimap, and static map
- Routing
- Coordinate conversion
- Themes, planning area, and population query APIs

## Official Endpoint Summary

### Authentication

```http
POST /api/auth/post/getToken
```

Body parameters:

| Parameter | Type | Required | Description |
|---|---|---:|---|
| `email` | string | yes | Registered email used for OneMap registration |
| `password` | string | yes | Password used for OneMap registration |

Sample response:

```json
{
  "access_token": "***********************",
  "expiry_timestamp": "1689388144"
}
```

Notes:

- The token is valid for 3 days.
- `expiry_timestamp` is a Unix timestamp.
- The token does not auto-renew; callers must re-authenticate after expiry.
- Credentials and tokens must be stored server-side and never exposed to browsers.

Possible error responses documented by OneMap:

- `401` — Authentication failed, contact `support@onemap.gov.sg`.
- `404` — User is not registered in system.
- `404` — A valid email address and password are required to generate a token.

### Search

```http
GET /api/common/elastic/search
```

Query parameters:

| Parameter | Type | Required | Description |
|---|---|---:|---|
| `searchVal` | string | yes | Keywords entered by users to filter results |
| `returnGeom` | string | yes | `Y` or `N`; return geometry values |
| `getAddrDetails` | string | yes | `Y` or `N`; return address details |
| `pageNum` | integer | no | Page to retrieve |

Sample request:

```text
/api/common/elastic/search?searchVal=200640&returnGeom=Y&getAddrDetails=Y&pageNum=1
```

Sample response shape:

```json
{
  "found": 1,
  "totalNumPages": 1,
  "pageNum": 1,
  "results": [
    {
      "SEARCHVAL": "640 ROWELL ROAD SINGAPORE 200640",
      "BLK_NO": "640",
      "ROAD_NAME": "ROWELL ROAD",
      "BUILDING": "NIL",
      "ADDRESS": "640 ROWELL ROAD SINGAPORE 200640",
      "POSTAL": "200640",
      "X": "30381.1007417506",
      "Y": "32195.1006872542",
      "LATITUDE": "1.30743547948389",
      "LONGITUDE": "103.854713903431"
    }
  ]
}
```

### Reverse Geocode (WGS84)

```http
GET /api/public/revgeocode
```

Header parameters:

| Header | Type | Required | Description |
|---|---|---:|---|
| `Authorization` | string | yes | API token provided by the Authentication Service |

Query parameters:

| Parameter | Type | Required | Description |
|---|---|---:|---|
| `location` | string | yes | Latitude and longitude coordinates in WGS84 format |
| `buffer` | string | no | `0` to `500` metres; search within a circumference from a point |
| `addressType` | string | no | `HDB` or `All`; limits property types within the buffer/radius |
| `otherFeatures` | string | no | `Y` or `N`; includes reservoirs, playgrounds, jetties, etc.; default `N` |

Sample request:

```text
/api/public/revgeocode?location=1.3254295,103.9005321&buffer=40&addressType=All
```

Sample response shape:

```json
{
  "GeocodeInfo": [
    {
      "BUILDINGNAME": "KAMPONG UBI VIEW",
      "BLOCK": "351",
      "ROAD": "UBI AVENUE 1",
      "POSTALCODE": "400351",
      "XCOORD": "35501.9607216",
      "YCOORD": "34191.1578935",
      "LATITUDE": "1.325486284730739",
      "LONGITUDE": "103.90072773995409"
    }
  ]
}
```

Notes:

- Browser JavaScript geolocation (`navigator.geolocation`) returns WGS84 latitude/longitude and should use this endpoint.
- The maximum buffer is 500m for buildings and 20m for roads.
- Fields without values return as `NIL`.
- Building name returns `null` if the building is unnamed.
- Longer response time is expected for some addresses.
- By default, OneMap returns a maximum of 10 nearest buildings.

Possible error responses documented by OneMap:

- `400` — Missing access token in request header.
- `400` — Provided location is empty.
- `400` — Provided location is invalid.
- `401` — Token has expired.
- `401` — Invalid token.
- `403` — Access forbidden for the user's role.
- `429` — API limits exceeded.

## Proposed Design

### Public API

Provide a small, Laravel-friendly client class plus facade-style access through this package's existing facade target where appropriate.

Recommended public methods:

```php
$token = $oneMap->getToken();

$results = $oneMap->search(
    searchVal: '200640',
    returnGeom: true,
    getAddrDetails: true,
    pageNum: 1,
);

$addresses = $oneMap->reverseGeocode(
    latitude: 1.3254295,
    longitude: 103.9005321,
    buffer: 40,
    addressType: 'All',
    otherFeatures: false,
);
```

### Token Handling

Recommended behaviour:

1. If a manual token is supplied for a request, use it.
2. Otherwise, retrieve a cached token.
3. If no valid cached token exists, call the authentication endpoint with configured credentials.
4. Cache the token until shortly before `expiry_timestamp` to avoid expired-token race conditions.

This gives simple usage for normal applications while still supporting users who manage tokens themselves.

### Configuration

Add an `onemap` section to `config/helpers.php`:

```php
'onemap' => [
    'base_url' => env('ONEMAP_BASE_URL', 'https://www.onemap.gov.sg'),
    'email' => env('ONEMAP_EMAIL'),
    'password' => env('ONEMAP_PASSWORD'),
    'token' => env('ONEMAP_TOKEN'),
    'token_cache_key' => env('ONEMAP_TOKEN_CACHE_KEY', 'laravel-helpers:onemap:token'),
    'token_cache_buffer_seconds' => env('ONEMAP_TOKEN_CACHE_BUFFER_SECONDS', 300),
    'timeout' => env('ONEMAP_TIMEOUT', 10),
],
```

### Dependencies

The current package requires only `illuminate/contracts`, but a best-practice Laravel HTTP implementation should use Laravel's HTTP client.

Recommended Composer addition:

```json
"illuminate/http": "^11.0||^12.0"
```

This provides `Illuminate\Support\Facades\Http` and integrates with Laravel's HTTP fake testing tools.

### Validation Rules

Validate input before making requests:

| Method | Rule |
|---|---|
| `getToken` | Configured or supplied email and password must be non-empty strings |
| `search` | `searchVal` must be a non-empty string |
| `search` | `returnGeom` and `getAddrDetails` should be converted from booleans to `Y`/`N` |
| `search` | `pageNum`, when supplied, must be `>= 1` |
| `reverseGeocode` | Latitude must be between `-90` and `90` |
| `reverseGeocode` | Longitude must be between `-180` and `180` |
| `reverseGeocode` | `buffer`, when supplied, must be between `0` and `500` |
| `reverseGeocode` | `addressType`, when supplied, must be `All` or `HDB` |
| `reverseGeocode` | `otherFeatures` should be converted from boolean to `Y`/`N` |

Throw `InvalidArgumentException` for local validation failures.

### Error Handling

Recommended initial behaviour:

- Use Laravel HTTP client's `throw()` to raise request exceptions for non-successful HTTP statuses.
- Return decoded arrays for successful responses.
- Do not introduce custom exception classes in Phase 1 unless the implementation needs clearer package-level exceptions.

Potential later enhancement:

- `OneMapAuthenticationException`
- `OneMapRateLimitException`
- `OneMapRequestException`

## Proposed File Changes

### Source

| File | Purpose |
|---|---|
| `src/OneMap/OneMapClient.php` | HTTP client and public OneMap methods |
| `src/OneMap/Enums/AddressType.php` | Optional enum for `All` / `HDB` |
| `src/OneMap/Enums/YesNo.php` | Optional internal enum/value helper for `Y` / `N` |
| `config/helpers.php` | OneMap configuration |
| `src/LaravelHelpersServiceProvider.php` | Bind `OneMapClient` as a singleton |
| `src/LaravelHelpers.php` | Optional convenience method/accessor if using existing facade target |

Avoid DTOs in Phase 1 unless the implementation benefits from stronger response typing. OneMap returns stringly typed uppercase fields, so returning raw decoded arrays preserves the official response without over-normalising.

### Tests

| File | Purpose |
|---|---|
| `tests/OneMap/OneMapClientTest.php` | HTTP fake tests for auth, search, reverse geocode, token caching, and validation |

Test with `Http::fake()`; never call live OneMap endpoints in the test suite.

### Documentation

| File | Purpose |
|---|---|
| `docs/onemap/implementation_plan.md` | This implementation plan |
| `docs/onemap/user_manual.md` | User-facing setup and usage guide |
| `README.md` | Feature table entry and short usage link |

## Test Plan

### Authentication

- Calls `POST /api/auth/post/getToken` with configured email/password.
- Returns `access_token` and `expiry_timestamp` from the decoded response.
- Caches token until `expiry_timestamp - buffer`.
- Reuses cached token for subsequent protected requests.
- Refreshes token when cached token is expired.
- Allows manually configured token to bypass email/password authentication.

### Search

- Sends `GET /api/common/elastic/search`.
- Sends `Authorization` header.
- Converts `returnGeom: true` to `Y` and `false` to `N`.
- Converts `getAddrDetails: true` to `Y` and `false` to `N`.
- Includes `pageNum` only when supplied or defaults to `1` if that is chosen by implementation.
- Returns decoded response array.
- Rejects empty `searchVal`.
- Rejects invalid `pageNum`.

### Reverse Geocode

- Sends `GET /api/public/revgeocode`.
- Sends `Authorization` header.
- Formats `location` as `latitude,longitude`.
- Includes optional `buffer`, `addressType`, and `otherFeatures`.
- Converts `otherFeatures: true` to `Y` and `false` to `N`.
- Returns decoded response array.
- Rejects invalid latitude/longitude.
- Rejects invalid `buffer` outside `0..500`.
- Rejects invalid `addressType` values.

## Documentation Plan

Update README feature table with:

| Feature | Description | Documentation |
|---|---|---|
| OneMap API Helper | Laravel HTTP client for OneMap auth, address search, and WGS84 reverse geocoding. | `docs/onemap/user_manual.md` |

The user manual should document:

- Installation/configuration
- Environment variables
- Token caching behaviour
- Search examples
- Browser geolocation to WGS84 reverse geocode example
- Manual token override example
- Explicit exclusions for Phase 1

## Final Implementation Decisions

- **Token Behaviour**: The helper auto-manages and caches the OneMap token using configured email/password with a default 300-second expiry buffer. It also allows a manually supplied token via configuration or per-method argument, which bypasses credential authentication.
- **Enums**: Excluded in Phase 1 to keep the surface area minimal.
- **Dependencies**: Added `illuminate/http` to explicitly depend on Laravel's HTTP client.
- **Client Binding**: `OneMapClient` is bound as a singleton in `LaravelHelpersServiceProvider` to allow dependency injection and unified configuration resolution.
