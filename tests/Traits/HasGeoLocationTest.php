<?php

namespace Nikoleesg\LaravelHelpers\Tests\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Nikoleesg\LaravelHelpers\Traits\HasGeoLocation;

beforeEach(function () {
    Schema::create('locations', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->decimal('latitude', 10, 7);
        $table->decimal('longitude', 10, 7);
        $table->timestamps();
    });

    Schema::create('custom_locations', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->decimal('lat', 10, 7);
        $table->decimal('lng', 10, 7);
        $table->timestamps();
    });

    // Seed with real-world coordinates (Singapore area)
    // Marina Bay Sands:  1.2834,  103.8607
    // Orchard Road:      1.3048,  103.8318  (~3.5 km from MBS)
    // Changi Airport:    1.3644,  103.9915  (~16 km from MBS)
    // Sentosa:           1.2494,  103.8303  (~5 km from MBS)
    // Johor Bahru (MY):  1.4655,  103.7578  (~22 km from MBS)

    LocationModel::create(['name' => 'Marina Bay Sands', 'latitude' => 1.2834, 'longitude' => 103.8607]);
    LocationModel::create(['name' => 'Orchard Road', 'latitude' => 1.3048, 'longitude' => 103.8318]);
    LocationModel::create(['name' => 'Sentosa', 'latitude' => 1.2494, 'longitude' => 103.8303]);
    LocationModel::create(['name' => 'Changi Airport', 'latitude' => 1.3644, 'longitude' => 103.9915]);
    LocationModel::create(['name' => 'Johor Bahru', 'latitude' => 1.4655, 'longitude' => 103.7578]);
});

class LocationModel extends Model
{
    use HasGeoLocation;

    protected $table = 'locations';

    protected $guarded = [];
}

class CustomLocationModel extends Model
{
    use HasGeoLocation;

    protected $table = 'custom_locations';

    protected $guarded = [];

    protected string $latitudeColumn = 'lat';

    protected string $longitudeColumn = 'lng';
}

// Reference point: Marina Bay Sands (1.2834, 103.8607)
$refLat = 1.2834;
$refLng = 103.8607;

it('adds a distance attribute with scopeWithDistance', function () use ($refLat, $refLng) {
    $results = LocationModel::withDistance($refLat, $refLng)->get();

    expect($results)->toHaveCount(5)
        ->and($results->first()->distance)->not->toBeNull();
});

it('returns distance of zero for the reference point itself', function () use ($refLat, $refLng) {
    $results = LocationModel::withDistance($refLat, $refLng)
        ->orderBy('distance')
        ->get();

    $mbs = $results->first();

    expect($mbs->name)->toBe('Marina Bay Sands')
        ->and((float) $mbs->distance)->toBeLessThan(0.1);
});

it('returns only locations within the given radius', function () use ($refLat, $refLng) {
    // 6 km radius should include MBS, Orchard Road, Sentosa (all < 6 km)
    $nearby = LocationModel::nearby($refLat, $refLng, 6)->get();

    expect($nearby)->toHaveCount(3)
        ->and($nearby->pluck('name')->toArray())->toContain('Marina Bay Sands', 'Orchard Road', 'Sentosa');
});

it('orders results by distance ascending', function () use ($refLat, $refLng) {
    $results = LocationModel::nearby($refLat, $refLng, 25)->get();

    $distances = $results->pluck('distance')->map(fn ($d) => (float) $d)->toArray();

    // Each distance should be <= the next
    for ($i = 0; $i < count($distances) - 1; $i++) {
        expect($distances[$i])->toBeLessThanOrEqual($distances[$i + 1]);
    }
});

it('returns an empty result when no locations are within radius', function () {
    // Use a tiny radius that excludes everything except the point itself
    $results = LocationModel::nearby(0, 0, 1)->get();

    expect($results)->toHaveCount(0);
});

it('supports miles as the distance unit', function () use ($refLat, $refLng) {
    // 6 km ≈ 3.73 mi — using 4 mi radius should give the same 3 results
    $nearbyMi = LocationModel::nearby($refLat, $refLng, 4, 'mi')->get();

    expect($nearbyMi)->toHaveCount(3)
        ->and($nearbyMi->pluck('name')->toArray())->toContain('Marina Bay Sands', 'Orchard Road', 'Sentosa');
});

it('works with custom latitude and longitude column names', function () use ($refLat, $refLng) {
    // Seed the custom table
    CustomLocationModel::create(['name' => 'Marina Bay Sands', 'lat' => 1.2834, 'lng' => 103.8607]);
    CustomLocationModel::create(['name' => 'Orchard Road', 'lat' => 1.3048, 'lng' => 103.8318]);
    CustomLocationModel::create(['name' => 'Johor Bahru', 'lat' => 1.4655, 'lng' => 103.7578]);

    $nearby = CustomLocationModel::nearby($refLat, $refLng, 6)->get();

    expect($nearby)->toHaveCount(2)
        ->and($nearby->pluck('name')->toArray())->toContain('Marina Bay Sands', 'Orchard Road');
});

it('returns the correct column names from getters', function () {
    $default = new LocationModel;
    expect($default->getLatitudeColumn())->toBe('latitude')
        ->and($default->getLongitudeColumn())->toBe('longitude');

    $custom = new CustomLocationModel;
    expect($custom->getLatitudeColumn())->toBe('lat')
        ->and($custom->getLongitudeColumn())->toBe('lng');
});
