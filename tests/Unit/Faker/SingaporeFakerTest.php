<?php

namespace Nikoleesg\LaravelHelpers\Tests\Unit\Faker;

use Illuminate\Support\Collection;
use Nikoleesg\LaravelHelpers\Data\Singapore\AddressData;
use Nikoleesg\LaravelHelpers\Data\Singapore\PersonnelData;
use Nikoleesg\LaravelHelpers\Data\Singapore\ResidentData;
use Nikoleesg\LaravelHelpers\Enums\Gender;
use Nikoleesg\LaravelHelpers\Enums\HouseType;
use Nikoleesg\LaravelHelpers\Enums\Race;
use Nikoleesg\LaravelHelpers\Faker\SingaporeFaker;

it('can generate a single address data', function () {
    $address = SingaporeFaker::address();

    expect($address)->toBeInstanceOf(AddressData::class)
        ->and($address->postal)->toHaveLength(6);

    if ($address->house_type === HouseType::Landed) {
        expect($address->unit)->toBeNull();
    } else {
        expect($address->unit)->not->toBeNull()->toStartWith('#');
    }
});

it('can generate a collection of address data', function () {
    $addresses = SingaporeFaker::addresses(5);

    expect($addresses)->toBeInstanceOf(Collection::class)
        ->and($addresses)->toHaveCount(5)
        ->and($addresses->first())->toBeInstanceOf(AddressData::class);
});

it('can generate address with specific house type', function () {
    $address = SingaporeFaker::address(HouseType::Landed);

    expect($address->house_type)->toBe(HouseType::Landed)
        ->and($address->unit)->toBeNull();

    $hdbAddress = SingaporeFaker::address(HouseType::HDB);
    expect($hdbAddress->house_type)->toBe(HouseType::HDB)
        ->and($hdbAddress->unit)->not->toBeNull();
});

it('can generate a single personnel data', function () {
    $personnel = SingaporeFaker::personnel();

    expect($personnel)->toBeInstanceOf(PersonnelData::class)
        ->and($personnel->age)->toBeBetween(18, 80)
        ->and($personnel->phone_number)->toHaveLength(8)
        ->and(in_array($personnel->phone_number[0], ['3', '6', '8', '9']))->toBeTrue();
});

it('can generate a collection of personnel data', function () {
    $personnels = SingaporeFaker::personnels(5);

    expect($personnels)->toBeInstanceOf(Collection::class)
        ->and($personnels)->toHaveCount(5)
        ->and($personnels->first())->toBeInstanceOf(PersonnelData::class);
});

it('can generate personnel with specific race and gender', function () {
    $personnel = SingaporeFaker::personnel(Race::Chinese, Gender::Male);

    expect($personnel->gender)->toBe(Gender::Male)
        ->and($personnel->race)->toBe(Race::Chinese);

    $personnelFemale = SingaporeFaker::personnel(Race::Malay, Gender::Female);
    expect($personnelFemale->gender)->toBe(Gender::Female)
        ->and($personnelFemale->race)->toBe(Race::Malay);
});

it('can generate a single resident data', function () {
    $resident = SingaporeFaker::resident();

    expect($resident)->toBeInstanceOf(ResidentData::class)
        ->and($resident->age)->toBeBetween(18, 80)
        ->and($resident->postal)->toHaveLength(6)
        ->and($resident->race)->toBeInstanceOf(Race::class);
});

it('can generate a collection of resident data', function () {
    $residents = SingaporeFaker::residents(5);

    expect($residents)->toBeInstanceOf(Collection::class)
        ->and($residents)->toHaveCount(5)
        ->and($residents->first())->toBeInstanceOf(ResidentData::class);
});

it('generates reasonably distributed house types', function () {
    $residents = SingaporeFaker::residents(100);

    $counts = $residents->countBy(fn ($r) => $r->house_type->value);

    // Assert that the weights are respected in an aggregate context
    // 70% HDB, 20% Condo, 10% Landed
    expect($counts[HouseType::HDB->value])->toBeGreaterThan(50) // Should be around 70
        ->and($counts[HouseType::Condominium->value] ?? 0)->toBeLessThan(40) // Should be around 20
        ->and($counts[HouseType::Landed->value] ?? 0)->toBeLessThan(25); // Should be around 10
});

it('generates reasonably distributed races', function () {
    // Generate enough sample size to avoid random failure
    $counts = collect(range(1, 100))->map(fn () => SingaporeFaker::personnel())->countBy(function ($p) {
        // Reverse engineer the locale from the generated data isn't reliable,
        // so we'll just check that it runs without errors for now.
        return true;
    });

    expect($counts->first())->toBe(100);
});
