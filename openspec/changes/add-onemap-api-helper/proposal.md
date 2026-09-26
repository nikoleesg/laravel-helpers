## Why

Applications using this package need a first-class, server-side way to call Singapore's OneMap APIs for address lookup and browser/GPS reverse geocoding without repeatedly hand-writing authentication, token caching, request construction, and validation logic.

This change adds a focused OneMap helper for the package's Singapore-oriented utilities, starting with the APIs needed for common address workflows.

## What Changes

- Add a Laravel-friendly OneMap API client for:
  - token generation via `POST /api/auth/post/getToken`
  - address/place search via `GET /api/common/elastic/search`
  - WGS84 reverse geocoding via `GET /api/public/revgeocode`
  - SVY21 to WGS84 coordinate conversion via `GET /api/common/convert/3414to4326`
- Auto-manage OneMap tokens from configured credentials, cache them until shortly before expiry, and allow callers to supply a token manually.
- Add OneMap configuration keys for base URL, credentials, optional token override, token cache key, token expiry buffer, and timeout.
- Add request validation for search text, pagination, WGS84 coordinates, buffer, address type, and `Y`/`N` style flags.
- Add tests using Laravel HTTP fakes; tests must not call live OneMap endpoints.
- Document setup, usage, browser geolocation flow, response shapes, and Phase 1 exclusions.
- Add the Laravel HTTP client dependency if needed to support `Http::fake()` and package HTTP requests.

Non-goals for Phase 1:

- SVY21 reverse geocoding (`/api/public/revgeocodexy`)
- map services, basemap URLs, minimap, advanced minimap, or static map helpers
- routing, themes, planning areas, or population APIs
- response DTOs or field renaming; preserve OneMap's decoded response arrays initially

## Capabilities

### New Capabilities

- `onemap-api-helper`: Authenticated OneMap API access for token generation, address search, WGS84 reverse geocoding, and SVY21 to WGS84 coordinate conversion.

### Modified Capabilities

- None.

## Impact

- New source files under `src/OneMap/` for the API client and any small supporting value helpers/enums.
- `config/helpers.php` gains an `onemap` configuration section.
- `src/LaravelHelpersServiceProvider.php` binds the OneMap client into the Laravel container.
- `composer.json` may gain `illuminate/http` for Laravel's HTTP client.
- New tests under `tests/OneMap/` using HTTP fakes and cache/config setup.
- `README.md` and `docs/onemap/` document the helper.
