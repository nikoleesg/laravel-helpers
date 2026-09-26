# GeoCalculator — Test Case Plan

All tests in `tests/Unit/GeoCalculatorTest.php` using Pest. Pure PHP math — no database, no model, no schema setup needed.

## Validation Data (Known-Answer Coordinates)

| City | Lat | Lng |
|---|---|---|
| Singapore | 1.3521 | 103.8198 |
| Kuala Lumpur | 3.1390 | 101.6869 |
| Tokyo | 35.6762 | 139.6503 |
| London | 51.5074 | -0.1278 |
| Paris | 48.8566 | 2.3522 |
| New York | 40.7128 | -74.0060 |
| Los Angeles | 34.0522 | -118.2437 |

Pre-calculated expected distances (±1 km tolerance for sphere approximation):

| From → To | Expected (km) | Expected (mi) |
|---|---|---|
| Singapore → KL | ~315 | ~196 |
| Singapore → Tokyo | ~5,312 | ~3,301 |
| London → Paris | ~343 | ~213 |
| NY → LA | ~3,944 | ~2,451 |

---

## Test Cases

### 1. `distance()` — Basic Haversine

| # | Name | Input | Expected | Tolerance |
|---|---|---|---|---|
| 1.1 | Known city pair (SG→KL) | `([1.3521, 103.8198], [3.1390, 101.6869])` | ~315 km | ±2 km |
| 1.2 | Known city pair (London→Paris) | `([51.5074, -0.1278], [48.8566, 2.3522])` | ~343 km | ±2 km |
| 1.3 | Known city pair (NY→LA) | `([40.7128, -74.0060], [34.0522, -118.2437])` | ~3,944 km | ±5 km |
| 1.4 | Same point (zero distance) | `([1.3521, 103.8198], [1.3521, 103.8198])` | 0.0 | exact |
| 1.5 | Miles conversion | SG→KL with `unit: 'mi'` | ~196 mi | ±2 mi |
| 1.6 | Meters | SG→KL with `unit: 'm'` | ~315,000 m | ±2,000 m |

### 2. `distanceBetween()` — Pairwise Distances

| # | Name | Input | Expected |
|---|---|---|---|
| 2.1 | Three points | `[SG, KL, Tokyo]` | 2 distances: SG→KL (~315), KL→Tokyo (~5,100) |
| 2.2 | Two points | `[London, Paris]` | 1 distance: London→Paris (~343) |

### 3. `center()` — Geographic Centroid

| # | Name | Input | Expected |
|---|---|---|---|
| 3.1 | Two points midpoint | `[SG, KL]` | Lat ~2.25, Lng ~102.75 (midpoint) |
| 3.2 | Three known points | `[SG, KL, Tokyo]` | Verifiable against online calculator |

### 4. `closest()` — Nearest Point

| # | Name | Input | Expected |
|---|---|---|---|
| 4.1 | Find nearest from set | Origin: SG, Points: [KL, Tokyo, London] | KL (closest to SG) |

### 5. `farthest()` — Farthest Point

| # | Name | Input | Expected |
|---|---|---|---|
| 5.1 | Find farthest from set | Origin: SG, Points: [KL, Tokyo, London] | London (farthest from SG) |

### 6. `isWithinRadius()` — Point in Circle

| # | Name | Input | Expected |
|---|---|---|---|
| 6.1 | Inside radius | Origin: SG, Point: KL, Radius: 500 km | `true` |
| 6.2 | Outside radius | Origin: SG, Point: Tokyo, Radius: 500 km | `false` |
| 6.3 | Exact boundary | Origin: SG, Point: KL, Radius: ~315 km | `true` (distance ≤ radius) |

### 7. `orderByNearest()` — Nearest Neighbor

| # | Name | Input | Expected |
|---|---|---|---|
| 7.1 | Order from SG | Origin: SG, Points: [Tokyo, London, KL] | [KL, Tokyo, London] |

### 8. Edge Cases

| # | Name | Input | Expected |
|---|---|---|---|
| 8.1 | Antipodal points | `([0, 0], [0, 180])` | ~20,015 km (half Earth circumference) |
| 8.2 | Points on equator | `([0, 0], [0, 90])` | ~10,008 km (quarter circumference) |
| 8.3 | Empty points (closest) | Origin: SG, Points: `[]` | Exception or null |
