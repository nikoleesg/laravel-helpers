# Singapore Faker Helpers User Manual

This package provides a set of generator functions to easily seed and fake Singapore-specific entities, specifically focusing on Addresses, Personnel data, and combined Resident profiles. 

Under the hood, it utilizes `fakerphp/faker` dynamically configured with appropriate regional locales and weights, and relies on `spatie/laravel-data` to consistently return strongly-typed Data Transfer Objects (DTOs) or robust Laravel Collections.

## Installation 
These features are available out of the box in the `nikoleesg/laravel-helpers` package. Because it incorporates production-ready dependencies natively, it functions gracefully in non-dev deployment scenarios.

## Enumerations (`Enums`)
Generators take advantage of defined PHP Enums for structured definitions:

| Enum Name | Cases | Weights Default |
| :--- | :--- | :--- | 
| `HouseType` | `10` (HDB), `20` (Condominium), `30` (Landed) | 70% HDB, 20% Condo, 10% Landed |
| `Race` | `10` (Chinese), `20` (Malay), `30` (Indian), `90` (Other) | 70% Chinese, 20% Malay, 5% Indian, 5% Other |
| `Gender` | `1` (Male), `2` (Female) | ~50% each |

## Usage Examples

All methods belong to the `Nikoleesg\LaravelHelpers\Faker\SingaporeFaker` static class.
*When generating a single item, use the singular method (e.g., `address()`) which returns a **single DTO** instance.*
*When generating multiple items, use the plural method (e.g., `addresses(10)`) which returns an **`Illuminate\Support\Collection`** of DTOs randomly weighted.*

### Generating Addresses

Returns instance(s) of `Nikoleesg\LaravelHelpers\Data\Singapore\AddressData`.

```php
use Nikoleesg\LaravelHelpers\Faker\SingaporeFaker;
use Nikoleesg\LaravelHelpers\Enums\HouseType;

// Generate 1 random address
$address = SingaporeFaker::address();

// Generate 1 Landed property address
$landedProperty = SingaporeFaker::address(HouseType::Landed);

// Generate 10 random addresses (weights applied)
$addresses = SingaporeFaker::addresses(10); 
```

### Generating Personnel

Returns instance(s) of `Nikoleesg\LaravelHelpers\Data\Singapore\PersonnelData`.

```php
use Nikoleesg\LaravelHelpers\Faker\SingaporeFaker;
use Nikoleesg\LaravelHelpers\Enums\Race;
use Nikoleesg\LaravelHelpers\Enums\Gender;

// Generate 1 random person (Chinese/Malay/Indian/Other weighted)
$person = SingaporeFaker::personnel();

// Generate 1 specific profile: Malay Female
$malayFemale = SingaporeFaker::personnel(Race::Malay, Gender::Female);

// Generate 5 random persons 
$people = SingaporeFaker::personnels(5);
```

### Generating Combined Residents

Returns instance(s) of `Nikoleesg\LaravelHelpers\Data\Singapore\ResidentData`. This creates a flattened DTO merging attributes from both personnel and addresses. 

```php
use Nikoleesg\LaravelHelpers\Faker\SingaporeFaker;
use Nikoleesg\LaravelHelpers\Enums\HouseType;
use Nikoleesg\LaravelHelpers\Enums\Race;
use Nikoleesg\LaravelHelpers\Enums\Gender;

// Generate 1 random resident
$resident = SingaporeFaker::resident();

// Generate 1 highly specific profile
$specificResident = SingaporeFaker::resident(
    houseType: HouseType::Condominium, 
    race: Race::Chinese, 
    gender: Gender::Male
);

// Generate 10 random residents
$residents = SingaporeFaker::residents(10);
```

### DTO Properties Overview

**`AddressData`**
- `string $block` (e.g., `'112'` or `'112A'`)
- `string $street` (e.g., `'Ang Mo Kio Ave 1'`)
- `?string $unit` (e.g., `'#04-123'`, `null` if HouseType::Landed)
- `string $postal` (e.g., `'560112'`)
- `HouseType $house_type`

**`PersonnelData`**
- `string $name`
- `int $age` (18-80)
- `Race $race`
- `Gender $gender`
- `string $phone_number` (Starts with 3, 6, 8, 9)

**`ResidentData`**
Combines properties of both structures above into a single flat DTO footprint.
