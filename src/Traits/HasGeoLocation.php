<?php

namespace Nikoleesg\LaravelHelpers\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * @method static Builder withDistance(float $lat, float $lng, string $unit = 'km')
 * @method static Builder nearby(float $lat, float $lng, float $radius = 10, string $unit = 'km')
 */
trait HasGeoLocation
{
    /**
     * Earth's radius constants used in Haversine formula.
     */
    private const EARTH_RADIUS_KM = 6371;

    private const EARTH_RADIUS_MI = 3959;

    /**
     * Get the name of the latitude column.
     */
    public function getLatitudeColumn(): string
    {
        return property_exists($this, 'latitudeColumn') ? $this->latitudeColumn : 'latitude';
    }

    /**
     * Get the name of the longitude column.
     */
    public function getLongitudeColumn(): string
    {
        return property_exists($this, 'longitudeColumn') ? $this->longitudeColumn : 'longitude';
    }

    /**
     * Get the Earth's radius for the given unit.
     */
    private function getEarthRadius(string $unit): float
    {
        return match (strtolower($unit)) {
            'mi' => self::EARTH_RADIUS_MI,
            default => self::EARTH_RADIUS_KM,
        };
    }

    /**
     * Build the raw Haversine SQL expression.
     *
     * Returns the expression string and the bindings array.
     *
     * @return array{expression: string, bindings: array<int, float>}
     */
    private function getHaversineExpression(float $lat, float $lng, string $unit = 'km'): array
    {
        $earthRadius = $this->getEarthRadius($unit);
        $latCol = $this->getLatitudeColumn();
        $lngCol = $this->getLongitudeColumn();

        $expression = "(
            {$earthRadius} * acos(
                cos(radians(?)) *
                cos(radians({$latCol})) *
                cos(radians({$lngCol}) - radians(?)) +
                sin(radians(?)) *
                sin(radians({$latCol}))
            )
        )";

        return [
            'expression' => $expression,
            'bindings' => [$lat, $lng, $lat],
        ];
    }

    /**
     * Add a computed `distance` column using the Haversine formula.
     *
     * Does not filter or order — use `nearby()` for that.
     *
     * @param  float  $lat  Reference latitude in degrees
     * @param  float  $lng  Reference longitude in degrees
     * @param  string  $unit  Distance unit: 'km' (default) or 'mi'
     */
    public function scopeWithDistance(Builder $query, float $lat, float $lng, string $unit = 'km'): Builder
    {
        $haversine = $this->getHaversineExpression($lat, $lng, $unit);

        return $query->selectRaw("*, {$haversine['expression']} AS distance", $haversine['bindings']);
    }

    /**
     * Filter and order results by distance from a given point.
     *
     * Uses the Haversine formula to find records within the given radius
     * and orders them nearest-first.
     *
     * Uses `whereRaw` with the full expression instead of `having` on the
     * alias to ensure compatibility across all database drivers (including SQLite).
     *
     * @param  float  $lat  Reference latitude in degrees
     * @param  float  $lng  Reference longitude in degrees
     * @param  float  $radius  Maximum distance (default 10)
     * @param  string  $unit  Distance unit: 'km' (default) or 'mi'
     */
    public function scopeNearby(Builder $query, float $lat, float $lng, float $radius = 10, string $unit = 'km'): Builder
    {
        $haversine = $this->getHaversineExpression($lat, $lng, $unit);

        return $query
            ->selectRaw("*, {$haversine['expression']} AS distance", $haversine['bindings'])
            ->whereRaw("{$haversine['expression']} <= CAST(? AS REAL)", [...$haversine['bindings'], $radius])
            ->orderByRaw("{$haversine['expression']} ASC", $haversine['bindings']);
    }
}
