# HasGeoLocation — Haversine Distance Scopes

Add a reusable Eloquent trait that provides `scopeNearby` and `scopeWithDistance` for Haversine-based geospatial filtering.

## Proposed Changes

### Traits

---

#### [NEW] [HasGeoLocation.php](file:///Users/nikolee/projects/php/packages/nikoleesg/laravel-helpers/src/Traits/HasGeoLocation.php)

A trait with two Eloquent scopes and configurable helpers:

| Method | Purpose |
|---|---|
| `getLatitudeColumn()` | Returns the latitude column name (default `latitude`, override via `$latitudeColumn` property) |
| `getLongitudeColumn()` | Returns the longitude column name (default `longitude`, override via `$longitudeColumn` property) |
| `scopeWithDistance(Builder, lat, lng, unit)` | Adds a `selectRaw` for `distance` using the Haversine formula — no filtering, just adds the computed column |
| `scopeNearby(Builder, lat, lng, radius, unit)` | Filters with `whereRaw` using the full Haversine expression and orders by distance. Supports `km` and `mi` |

**Implementation Details:**
- Helper method: `getHaversineExpression()` returns the raw SQL expression and bindings array.
- Uses `whereRaw` with the full Haversine expression instead of `HAVING` on the alias for cross-database compatibility (SQLite rejects `HAVING` on non-aggregate queries).
- Uses `CAST(? AS REAL)` for the radius binding to ensure proper numeric comparison on SQLite.

## Verification Plan

### Automated Tests

- `tests/Traits/HasGeoLocationTest.php`:
   - Test `scopeWithDistance` adds a `distance` attribute to results.
   - Test `scopeNearby` returns only records within the given radius.
   - Test results are ordered by distance (nearest first).
   - Test custom column names work correctly.
   - Test switching `unit` to `mi` returns appropriate results.
   - Test empty result set when no records are within radius.
   - Test column name getters return correct defaults and overrides.
