# Eloquent Model Traits User Manual

This package provides reusable traits to add standard, helpful behaviors to your Eloquent models.

## `HasGeoLocation`

Provides Haversine-based Eloquent scopes for location-based filtering and distance calculations.

### Basic Usage

```php
use Illuminate\Database\Eloquent\Model;
use Nikoleesg\LaravelHelpers\Traits\HasGeoLocation;

class Listing extends Model
{
    use HasGeoLocation;

    // Optional: customize column names (defaults: 'latitude', 'longitude')
    // protected string $latitudeColumn = 'lat';
    // protected string $longitudeColumn = 'lng';
}
```

### Available Scopes

**`nearby($lat, $lng, $radius = 10, $unit = 'km')`** — Find records within a radius, ordered nearest-first:

```php
// Find listings within 25 km
Listing::nearby($lat, $lng, 25)->get();

// Find listings within 10 miles
Listing::nearby($lat, $lng, 10, 'mi')->get();

// Chain with other query conditions
Listing::where('is_active', true)->nearby($lat, $lng, 5)->paginate();
```

**`withDistance($lat, $lng, $unit = 'km')`** — Add a computed `distance` column without filtering:

```php
// Get all listings with distance info
Listing::withDistance($lat, $lng)->orderBy('distance')->get();

// Display distance in miles
Listing::withDistance($lat, $lng, 'mi')->get();
```

### Column Configuration

Override the default column names by defining properties on your model:

```php
class Store extends Model
{
    use HasGeoLocation;

    protected string $latitudeColumn = 'lat';
    protected string $longitudeColumn = 'lng';
}
```

### Supported Units

| Unit | Constant | Earth Radius |
|---|---|---|
| `'km'` (default) | 6371 km | Kilometers |
| `'mi'` | 3959 mi | Miles |
