<?php

namespace Nikoleesg\LaravelHelpers\Faker;

use Faker\Factory;
use Faker\Provider\en_SG\Address;
use Illuminate\Support\Collection;
use Nikoleesg\LaravelHelpers\Data\Singapore\AddressData;
use Nikoleesg\LaravelHelpers\Data\Singapore\PersonnelData;
use Nikoleesg\LaravelHelpers\Data\Singapore\ResidentData;
use Nikoleesg\LaravelHelpers\Enums\Gender;
use Nikoleesg\LaravelHelpers\Enums\HouseType;
use Nikoleesg\LaravelHelpers\Enums\Race;

class SingaporeFaker
{
    /**
     * Get a weighted random house type.
     */
    protected static function getWeightedHouseType(): HouseType
    {
        $weights = collect([
            HouseType::HDB->value         => 70,
            HouseType::Condominium->value => 20,
            HouseType::Landed->value      => 10,
        ]);

        $normalizedWeights = $weights->normalizeBySum(100)->multiplyValues(100)->largestRemainderRound(100);

        $expanded = collect();
        foreach ($normalizedWeights as $type => $count) {
            for ($i = 0; $i < $count; $i++) {
                $expanded->push(HouseType::from($type));
            }
        }

        return $expanded->random();
    }

    /**
     * Get a weighted random race.
     */
    protected static function getWeightedRace(): Race
    {
        $weights = collect([
            Race::Chinese->value => 70,
            Race::Malay->value   => 20,
            Race::Indian->value  => 5,
            Race::Other->value   => 5,
        ]);

        $normalizedWeights = $weights->normalizeBySum(100)->multiplyValues(100)->largestRemainderRound(100);

        $expanded = collect();
        foreach ($normalizedWeights as $race => $count) {
            for ($i = 0; $i < $count; $i++) {
                $expanded->push(Race::from($race));
            }
        }

        return $expanded->random();
    }

    /**
     * Generate a single Address Data.
     */
    public static function address(?HouseType $houseType = null): AddressData
    {
        $faker = Factory::create('en_SG');
        $addressProvider = new \Nikoleesg\LaravelHelpers\Faker\Providers\SingaporeAddressProvider($faker);
        $faker->addProvider($addressProvider);

        $type = $houseType ?? self::getWeightedHouseType();
        $addressProvider->setHouseType($type);

        return new AddressData(
            block: $faker->blockNumber(),
            street: $faker->streetAddress(),
            unit: $faker->unitNumber(),
            postal: $faker->postcode(),
            house_type: $type,
        );
    }

    /**
     * Generate multiple Address Data (randomly weighted).
     *
     * @return Collection<int, AddressData>
     */
    public static function addresses(int $count): Collection
    {
        return collect(range(1, $count))->map(fn () => self::address());
    }

    /**
     * Generate a single Personnel Data.
     */
    public static function personnel(?Race $race = null, ?Gender $gender = null): PersonnelData
    {
        $selectedRace = $race ?? self::getWeightedRace();
        $locale = match ($selectedRace) {
            Race::Chinese => 'en_SG',
            Race::Malay   => 'ms_MY',
            Race::Indian  => 'en_IN',
            Race::Other   => 'en_US',
        };

        $faker = Factory::create($locale);

        if ($locale === 'en_SG') {
            $faker->addProvider(new \Faker\Provider\en_SG\Person($faker));
        } elseif ($locale === 'ms_MY') {
            $faker->addProvider(new \Faker\Provider\ms_MY\Person($faker));
        } elseif ($locale === 'en_IN') {
            $faker->addProvider(new \Faker\Provider\en_IN\Person($faker));
        } elseif ($locale === 'en_US') {
            $faker->addProvider(new \Faker\Provider\en_US\Person($faker));
        }

        $selectedGender = $gender ?? $faker->randomElement([Gender::Male, Gender::Female]);

        $name = $selectedGender === Gender::Male ? $faker->name('male') : $faker->name('female');

        $age = $faker->numberBetween(18, 80);

        $startDigit = $faker->randomElement([3, 6, 8, 9]);
        $remaining = str_pad((string) $faker->numberBetween(0, 9999999), 7, '0', STR_PAD_LEFT);
        $phone = current(explode(' ', $startDigit.$remaining));

        return new PersonnelData(
            name: $name,
            age: $age,
            race: $selectedRace,
            gender: $selectedGender,
            phone_number: $phone,
        );
    }

    /**
     * Generate multiple Personnel Data (randomly weighted).
     *
     * @return Collection<int, PersonnelData>
     */
    public static function personnels(int $count): Collection
    {
        return collect(range(1, $count))->map(fn () => self::personnel());
    }

    /**
     * Generate a single Resident Data.
     */
    public static function resident(?HouseType $houseType = null, ?Race $race = null, ?Gender $gender = null): ResidentData
    {
        $addressData = self::address($houseType);
        $personnelData = self::personnel($race, $gender);

        return new ResidentData(
            name: $personnelData->name,
            age: $personnelData->age,
            race: $personnelData->race,
            gender: $personnelData->gender,
            phone_number: $personnelData->phone_number,
            block: $addressData->block,
            street: $addressData->street,
            unit: $addressData->unit,
            postal: $addressData->postal,
            house_type: $addressData->house_type,
        );
    }

    /**
     * Generate multiple Resident Data (randomly weighted).
     *
     * @return Collection<int, ResidentData>
     */
    public static function residents(int $count): Collection
    {
        return collect(range(1, $count))->map(fn () => self::resident());
    }
}
