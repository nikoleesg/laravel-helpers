## Context

See `proposal.md` for motivation. This package is a Laravel helper package, not an application, so the OneMap integration should be reusable, configurable, testable with faked HTTP requests, and safe for server-side credential handling.

The current package configuration lives in `config/helpers.php`, and the package service provider already uses Spatie Laravel Package Tools. The package currently requires `illuminate/contracts` but does not explicitly require Laravel's HTTP client package.

The OneMap documentation pages are rendered by JavaScript. Endpoint details for this design were verified from OneMap's official documentation bundle and captured in `docs/onemap/implementation_plan.md`.

## Goals / Non-Goals

**Goals:**

- Provide a focused OneMap API client for token generation, address search, WGS84 reverse geocoding, and SVY21 to WGS84 coordinate conversion.
- Keep the public API small and ergonomic for Laravel applications.
- Avoid live HTTP calls in the package test suite.
- Preserve OneMap response arrays as returned by the API instead of normalising or renaming fields.
- Keep credentials and tokens server-side.

**Non-Goals:**

- No SVY21 reverse geocode method in Phase 1.
- No typed response DTO layer in Phase 1.
- No browser-side JavaScript package or frontend token flow.
- No support for OneMap APIs outside the Phase 1 spec boundary.

## Decisions

### Decision: Use Laravel HTTP client

Use Laravel's HTTP client for OneMap requests and add `illuminate/http` as a runtime dependency if Composer requires it.

Rationale:

- It is the Laravel-native API for HTTP calls.
- It supports `Http::fake()` for deterministic tests.
- It avoids introducing a non-Laravel HTTP dependency for a Laravel package.

Alternative considered: use raw cURL or PHP streams. Rejected because testing and timeout/error handling would be noisier and less idiomatic.

Alternative considered: require Guzzle directly. Rejected because Laravel's HTTP client already wraps Guzzle and provides the test APIs we need.

### Decision: Bind a container singleton client

Register `OneMapClient` as a container singleton in the service provider. The client reads package config and uses Laravel services for HTTP and cache access.

Rationale:

- Applications can inject `OneMapClient` directly.
- A singleton centralises configuration and token-cache behaviour.
- It keeps the existing package facade optional rather than forcing a new facade.

Alternative considered: only static methods. Rejected because token caching, configuration, and HTTP fakes are easier to test and override with an injectable service.

### Decision: Auto-manage tokens but allow manual token override

Protected operations resolve tokens in this order:

1. Per-call token argument.
2. Configured `onemap.token`.
3. Cached token still valid after expiry buffer.
4. Fresh token from configured email/password.

Rationale:

- Simple application usage works with only `ONEMAP_EMAIL` and `ONEMAP_PASSWORD`.
- Advanced applications can manage tokens externally.
- The helper avoids calling the auth endpoint for every request.

Alternative considered: require callers to always call `getToken()` first. Rejected because it leaks token lifecycle concerns into every application workflow.

### Decision: Cache token with expiry buffer

Cache the token until `expiry_timestamp - token_cache_buffer_seconds`. Default the buffer to 300 seconds.

Rationale:

- OneMap tokens are valid for 3 days and do not auto-renew.
- A buffer avoids using a token close to expiry during request processing.
- The cache key is configurable to avoid collisions.

Alternative considered: cache for a fixed 3 days. Rejected because the API response gives an exact expiry timestamp and tests can validate expiry behaviour precisely.

### Decision: Return decoded arrays

Return OneMap's decoded JSON arrays directly for successful responses.

Rationale:

- OneMap field names are uppercase and stringly typed; preserving them avoids accidental semantic changes.
- DTOs can be added later if repeated app usage proves stable.
- It keeps Phase 1 small.

Alternative considered: create DTOs for token, search result, and geocode result. Rejected for Phase 1 because it adds mapping decisions and maintenance cost before the API usage patterns are proven.

### Decision: Validate local input before HTTP calls

Throw `InvalidArgumentException` for local validation failures, including empty search values, invalid pages, invalid coordinates, invalid buffer values, and invalid address types.

Rationale:

- Prevents avoidable API calls.
- Makes errors deterministic and easier to test.
- Keeps network errors distinct from caller input mistakes.

Alternative considered: let OneMap reject all invalid input. Rejected because some invalid values are obvious and should be caught before making external requests.

### Decision: Keep `otherFeatures` as a boolean option

Expose the official `otherFeatures=Y|N` parameter as a boolean argument, defaulting to `false`.

Rationale:

- The docs define `Y`/`N`, but Laravel callers should not need to handle string flags.
- It keeps the request API consistent with `returnGeom` and `getAddrDetails`.

Alternative considered: expose a raw string. Rejected because only `Y` and `N` are valid and booleans are clearer.

## Proposed Structure

```text
src/OneMap/
|-- OneMapClient.php
|-- Enums/
|   `-- AddressType.php        optional if implementation benefits

tests/OneMap/
`-- OneMapClientTest.php
```

`OneMapClient` should provide methods equivalent to:

```php
public function getToken(?string $email = null, ?string $password = null): array;

public function search(
    string $searchVal,
    bool $returnGeom = true,
    bool $getAddrDetails = true,
    int $pageNum = 1,
    ?string $token = null,
): array;

public function reverseGeocode(
    float $latitude,
    float $longitude,
    ?int $buffer = null,
    string $addressType = 'All',
    bool $otherFeatures = false,
    ?string $token = null,
): array;

public function convertSvy21ToWgs84(
    float $x,
    float $y,
    ?string $token = null,
): array;
```

Exact signatures can be adjusted during implementation if PHP named-argument ergonomics or static analysis require it, but the observable behaviour must match the spec.

## Risks / Trade-offs

- **OneMap docs/API drift** -> Keep endpoint constants and tests close to the documented examples; update docs when OneMap changes.
- **Token cache stale or near expiry** -> Use `expiry_timestamp` minus a configurable buffer.
- **Configured manual token has no expiry metadata** -> Treat configured/manual tokens as caller-managed and do not cache/refresh them automatically.
- **Laravel package dependency footprint grows** -> Add only `illuminate/http`; avoid unrelated dependencies.
- **OneMap returns successful HTTP statuses with API error payloads** -> Phase 1 returns decoded arrays and lets applications inspect API payloads. If this becomes painful, add package-specific exceptions later.
- **Cache unavailable in unusual app contexts** -> Laravel applications normally provide cache. Tests should cover token resolution without requiring a persistent cache backend.

## Migration Plan

1. Add the runtime dependency if needed.
2. Add config keys with safe defaults and no required credentials at install time.
3. Add the client and service-provider binding.
4. Add tests using HTTP fakes and package config overrides.
5. Update README and `docs/onemap/` documentation.

Rollback is straightforward because this is an additive feature: remove the client binding, source files, dependency addition, config section, tests, and docs if the change is abandoned before release.
