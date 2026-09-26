# GeoCalculator — Implementation Plan

Rewrite of [karam-mustafa/laravel-geographical-calculator](https://github.com/karam-mustafa/laravel-geographical-calculator) into `nikoleesg/laravel-helpers` as a clean, stateless utility class compatible with Laravel 11/12.

## Code Review of Original Package

### Architecture Issues

| Issue | Detail |
|---|---|
| **Over-engineered** | 10 traits, 1 abstract class, 1 interface, Facade, ServiceProvider, Artisan command — for 6 methods of math |
| **Mutable state** | Uses `localStorage` array and requires `clearResult()` between calls — error-prone |
| **Dead code** | `Debugger` trait (just `dd()`), `Looper` trait (just `foreach`), `Formatter` trait (single-line config lookup) |
| **Broken deps** | `phpunit` and `php-cs-fixer` in `require` instead of `require-dev` |
| **Outdated** | Targets Laravel 5.8+ / testbench ^6.23 — incompatible with Laravel 11/12 |

### Bugs Found

> [!CAUTION]
> **`isInArea()` returns inverted boolean** — returns `true` when the point is OUTSIDE the area and `false` when INSIDE.
>
> ```php
> // Original code (Areas.php:100)
> return $this->getFromStorage('distanceToCompare') > $this->getDiameter();
> // Should be: < or <=
> ```

> [!WARNING]
> **`cm` unit conversion is wrong** — the config uses `1.609344 * 100` (= 160.9344) but centimeters should be `1.609344 * 100000` (= 160,934.4). The label says "cm" but the factor is closer to hectometers.

> [!NOTE]
> **Distance formula is non-standard** — computes distance as `acos(sin*sin + cos*cos*cos_theta) → rad2deg → * 60 * 1.1515` (nautical miles conversion → statute miles), then multiplies by unit factors. This produces correct results but is harder to reason about than the standard Haversine formula with Earth's radius.

### What's Worth Keeping (Algorithms Only)

| Feature | Algorithm | Lines of actual math |
|---|---|---|
| Distance between two points | Haversine (spherical law of cosines) | ~10 lines |
| Center of multiple points | Cartesian averaging on sphere | ~15 lines |
| Closest / Farthest point | Min/max by distance | ~5 lines each |
| Point in area | Distance comparison to radius | ~3 lines |
| Nearest neighbor ordering | Greedy nearest neighbor | ~20 lines |

---

## Proposed Design

### Class: `GeoCalculator`

- **Namespace**: `Nikoleesg\LaravelHelpers\Classes\GeoCalculator`
- **Location**: `src/Classes/GeoCalculator.php`
- **Approach**: **Pure static methods** — no mutable state, no `clearResult()`, no Facade needed
- **No dependencies**: Pure PHP math, no Laravel-specific code required (usable in any PHP project)

### Method Inventory (7 public static methods)

| # | Method | Signature | Purpose |
|---|---|---|---|
| 1 | `distance` | `(array $from, array $to, string $unit = 'km'): float` | Distance between two points |
| 2 | `distanceBetween` | `(array $points, string $unit = 'km'): array` | Pairwise distances between consecutive points in a set |
| 3 | `center` | `(array $points): array` | Geographic center (centroid) of a set of points |
| 4 | `closest` | `(array $origin, array $points, string $unit = 'km'): array` | Find the closest point to origin |
| 5 | `farthest` | `(array $origin, array $points, string $unit = 'km'): array` | Find the farthest point from origin |
| 6 | `isWithinRadius` | `(array $origin, array $point, float $radius, string $unit = 'km'): bool` | Check if a point is within radius |
| 7 | `orderByNearest` | `(array $origin, array $points, string $unit = 'km'): array` | Order points by nearest neighbor algorithm |

### Private Helper Methods (2)

| Method | Purpose |
|---|---|
| `getEarthRadius(string $unit): float` | Returns Earth's radius for the given unit |
| `toRadians(float $degrees): float` | Converts degrees to radians (wraps `deg2rad`) |

### Supported Units

| Unit | Key | Earth Radius |
|---|---|---|
| Kilometers | `'km'` (default) | 6,371 km |
| Miles | `'mi'` | 3,959 mi |
| Meters | `'m'` | 6,371,000 m |
| Nautical miles | `'nm'` | 3,440.065 nm |

### Point Format

Points are represented as `[float $lat, float $lng]` arrays. All coordinates in **degrees**.

---

## Key Improvements Over Original

| Area | Original | Rewrite |
|---|---|---|
| **Architecture** | 10 traits + abstract + Facade + state | Single class, pure static methods |
| **State management** | Mutable `localStorage`, requires `clearResult()` | Stateless — each call is independent |
| **API** | Fluent builder (`setPoint()->setPoint()->getDistance()`) | Direct calls (`GeoCalculator::distance($a, $b)`) |
| **Type safety** | No type hints, arrays everywhere | PHP 8.3 typed parameters and return types |
| **isInArea bug** | Returns inverted boolean | Fixed as `isWithinRadius` — `true` when inside |
| **Unit conversion** | Nautical miles → statute miles → multiply by factor | Direct Earth radius per unit — cleaner math |
| **cm bug** | Wrong conversion factor (off by 1000x) | Removed `cm`/`mm` — added `m` and `nm` (more practical) |
| **Distance formula** | `acos → rad2deg → * 60 * 1.1515` | Standard Haversine with Earth's radius — same result, clearer intent |
| **Nearest neighbor** | Recursive, complex key management | Iterative, returns sorted array of `['point' => [...], 'distance' => float]` |
| **Dependencies** | ServiceProvider, Facade, Config, Artisan command | None — pure PHP, works anywhere |

---

## Proposed Changes

### Classes

#### [NEW] [GeoCalculator.php](file:///Users/nikolee/projects/php/packages/nikoleesg/laravel-helpers/src/Classes/GeoCalculator.php)

Single class with 7 public static methods + 2 private helpers. ~150-200 lines total.

### Tests

#### [NEW] [GeoCalculatorTest.php](file:///Users/nikolee/projects/php/packages/nikoleesg/laravel-helpers/tests/Unit/GeoCalculatorTest.php)

Pest test file with known-answer tests using real-world coordinates.

### Documentation

#### [NEW] [docs/geo-calculator/implementation_plan.md](file:///Users/nikolee/projects/php/packages/nikoleesg/laravel-helpers/docs/geo-calculator/implementation_plan.md)

This document (saved to project docs).

#### [NEW] [docs/geo-calculator/user_manual.md](file:///Users/nikolee/projects/php/packages/nikoleesg/laravel-helpers/docs/geo-calculator/user_manual.md)

Usage guide with examples for all methods.

---

## Verification Plan

### Automated Tests

```bash
# Run only GeoCalculator tests
vendor/bin/pest tests/Unit/GeoCalculatorTest.php

# Full suite
vendor/bin/pest
```

### Known-Answer Validation

Use well-known city coordinates with pre-calculated distances (verifiable via Google Maps / online Haversine calculators):

| From | To | Expected (km) |
|---|---|---|
| Singapore (1.3521, 103.8198) | Kuala Lumpur (3.1390, 101.6869) | ~315 km |
| Singapore (1.3521, 103.8198) | Tokyo (35.6762, 139.6503) | ~5,312 km |
| London (51.5074, -0.1278) | Paris (48.8566, 2.3522) | ~343 km |
| New York (40.7128, -74.0060) | Los Angeles (34.0522, -118.2437) | ~3,944 km |
