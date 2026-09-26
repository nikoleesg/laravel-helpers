# GeoCalculator — Usage Guide (Draft)

A stateless utility class for geographic calculations on coordinate arrays. Complements the `HasGeoLocation` Eloquent trait — use `HasGeoLocation` for database queries, and `GeoCalculator` for PHP-level math on arbitrary coordinate data.

## Import

```php
use Nikoleesg\LaravelHelpers\Classes\GeoCalculator;
```

## Point Format

All points are `[latitude, longitude]` arrays in **degrees**.

```php
$singapore = [1.3521, 103.8198];
$kualaLumpur = [3.1390, 101.6869];
```

---

## Methods

### `distance` — Distance Between Two Points

```php
// Default: kilometers
$km = GeoCalculator::distance([1.3521, 103.8198], [3.1390, 101.6869]);
// ≈ 315.0

// Miles
$mi = GeoCalculator::distance([1.3521, 103.8198], [3.1390, 101.6869], 'mi');
// ≈ 195.7

// Meters
$m = GeoCalculator::distance([1.3521, 103.8198], [3.1390, 101.6869], 'm');
// ≈ 315,000
```

### `distanceBetween` — Pairwise Distances

Calculates distances between each consecutive pair.

```php
$distances = GeoCalculator::distanceBetween([
    [1.3521, 103.8198],   // Singapore
    [3.1390, 101.6869],   // KL
    [35.6762, 139.6503],  // Tokyo
]);
// Returns:
// [
//     ['from' => 0, 'to' => 1, 'distance' => 315.0],
//     ['from' => 1, 'to' => 2, 'distance' => 5100.5],
// ]
```

### `center` — Geographic Center

```php
$center = GeoCalculator::center([
    [1.3521, 103.8198],   // Singapore
    [3.1390, 101.6869],   // Kuala Lumpur
    [35.6762, 139.6503],  // Tokyo
]);
// Returns: ['lat' => 13.38, 'lng' => 115.52] (approximate)
```

### `closest` — Find Nearest Point

```php
$result = GeoCalculator::closest(
    [1.3521, 103.8198],  // origin: Singapore
    [
        [3.1390, 101.6869],   // KL
        [35.6762, 139.6503],  // Tokyo
        [51.5074, -0.1278],   // London
    ]
);
// Returns: ['index' => 0, 'point' => [3.1390, 101.6869], 'distance' => 315.0]
```

### `farthest` — Find Farthest Point

```php
$result = GeoCalculator::farthest(
    [1.3521, 103.8198],  // origin: Singapore
    [
        [3.1390, 101.6869],   // KL
        [35.6762, 139.6503],  // Tokyo
        [51.5074, -0.1278],   // London
    ]
);
// Returns: ['index' => 2, 'point' => [51.5074, -0.1278], 'distance' => 10846.3]
```

### `isWithinRadius` — Point in Circle Check

```php
// Is KL within 500 km of Singapore?
GeoCalculator::isWithinRadius(
    [1.3521, 103.8198],  // origin
    [3.1390, 101.6869],  // point to check
    500                   // radius in km
);
// Returns: true

// Is Tokyo within 500 km of Singapore?
GeoCalculator::isWithinRadius(
    [1.3521, 103.8198],
    [35.6762, 139.6503],
    500
);
// Returns: false
```

### `orderByNearest` — Nearest Neighbor Ordering

Orders points greedily: start at origin → find nearest → from there find nearest unvisited → repeat.

```php
$ordered = GeoCalculator::orderByNearest(
    [1.3521, 103.8198],  // origin: Singapore
    [
        [35.6762, 139.6503],  // Tokyo
        [51.5074, -0.1278],   // London
        [3.1390, 101.6869],   // KL
    ]
);
// Returns ordered: KL → Tokyo → London (nearest-first from each step)
// [
//     ['index' => 2, 'point' => [3.1390, 101.6869], 'distance' => 315.0],
//     ['index' => 0, 'point' => [35.6762, 139.6503], 'distance' => 5100.5],
//     ['index' => 1, 'point' => [51.5074, -0.1278], 'distance' => 9560.2],
// ]
```

---

## Supported Units

| Key | Unit | Earth Radius |
|---|---|---|
| `'km'` (default) | Kilometers | 6,371 |
| `'mi'` | Miles | 3,959 |
| `'m'` | Meters | 6,371,000 |
| `'nm'` | Nautical miles | 3,440.065 |

---

## Comparison: GeoCalculator vs HasGeoLocation

| | `GeoCalculator` | `HasGeoLocation` |
|---|---|---|
| **Operates on** | PHP arrays | Eloquent models in DB |
| **Use case** | Math on coordinate data | Database queries |
| **Example** | `GeoCalculator::distance($a, $b)` | `Listing::nearby($lat, $lng, 25)->get()` |
| **State** | Stateless (static methods) | Stateless (Eloquent scopes) |
