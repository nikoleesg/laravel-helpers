# Singapore Faker Helpers User Manual

This package provides a set of generator functions to easily seed and fake Singapore-specific entities, specifically focusing on strongly-typed Addresses, Personnel data, and combined Resident profiles. 

Under the hood, it utilizes customized local adaptations for `fakerphp/faker` to enforce rigorous naming standards that correctly respect local formats (missing internally within Faker default). It seamlessly streams collections of `spatie/laravel-data` objects.

## Installation 
These features are available out of the box in the `nikoleesg/laravel-helpers` package. Because it relies on heavily tested generic libraries inherently present in production, it is fully viable for immediate use within staging setups.

## Enumerations (`Enums`)
Generators take advantage of defined PHP Enums for structured definitions. Ensure you reference these to apply filters to generation constraints:

| Enum Name | Cases | Weights Default |
| :--- | :--- | :--- | 
| `HouseType` | `1` (HDB), `2` (Condominium), `3` (Landed) | 70% HDB, 20% Condo, 10% Landed |
| `Race` | `1` (Chinese), `2` (Malay), `3` (Indian), `4` (Other) | 70% Chinese, 20% Malay, 5% Indian, 5% Other |
| `Gender` | `1` (Male), `2` (Female) | ~50% each |

## Usage Examples

All methods belong to the `Nikoleesg\LaravelHelpers\Faker\SingaporeFaker` static class.
*When generating a single item, use the singular method (e.g., `address()`) which returns a **single DTO** instance.*
*When generating multiple items, use the plural method (e.g., `addresses(10)`) which returns an **`Illuminate\Support\Collection`** of strictly distributed, logically scaled items exactly mapping configured weights.*

### Generating Addresses

Returns instance(s) of `Nikoleesg\LaravelHelpers\Data\Singapore\AddressData`. The backend resolves formatting seamlessly; Landed properties receive no units, whilst HDB strings might include a letter on the block.

```php
use Nikoleesg\LaravelHelpers\Faker\SingaporeFaker;
use Nikoleesg\LaravelHelpers\Enums\HouseType;

// Generate 1 random address
$address = SingaporeFaker::address();

// Generate 1 Landed property address dynamically filtering rules
$landedProperty = SingaporeFaker::address(HouseType::Landed);

// Generate 10 addresses distributed accurately across 70/20/10 math
$addresses = SingaporeFaker::addresses(10); 
```

### Generating Personnel

Returns instance(s) of `Nikoleesg\LaravelHelpers\Data\Singapore\PersonnelData`. Singapore localized names natively resolve permutations like standard `<English> <Surname> <Chinese>` outputs.

```php
use Nikoleesg\LaravelHelpers\Faker\SingaporeFaker;
use Nikoleesg\LaravelHelpers\Enums\Race;
use Nikoleesg\LaravelHelpers\Enums\Gender;

// Generate 1 random person (Chinese/Malay/Indian/Other weighted)
$person = SingaporeFaker::personnel();

// Generate 1 specific profile: Malay Female
$malayFemale = SingaporeFaker::personnel(Race::Malay, Gender::Female);

// Generate 5 perfectly scaled representative individuals
$people = SingaporeFaker::personnels(5);
```

### Generating Combined Residents

Returns instance(s) of `Nikoleesg\LaravelHelpers\Data\Singapore\ResidentData`. This flat generic merges all identity arrays concurrently. 

```php
use Nikoleesg\LaravelHelpers\Faker\SingaporeFaker;
use Nikoleesg\LaravelHelpers\Enums\HouseType;
use Nikoleesg\LaravelHelpers\Enums\Race;
use Nikoleesg\LaravelHelpers\Enums\Gender;

// Generate 1 random resident
$resident = SingaporeFaker::resident();

// Generate 1 highly specific profiled profile
$specificResident = SingaporeFaker::resident(
    houseType: HouseType::Condominium, 
    race: Race::Chinese, 
    gender: Gender::Male
);

// Generate 100 random residents simulating large scale migrations seamlessly
$residents = SingaporeFaker::residents(100);
```

### DTO Properties Overview

**`AddressData`**
- `string $block` (e.g., `'112'` or `'112A'`)
- `string $street` (e.g., `'Ang Mo Kio Ave 1'`)
- `?string $unit` (e.g., `'#04-123'`, `null` if HouseType::Landed)
- `string $postal` (e.g., `'560112'`)
- `HouseType $house_type`

**`PersonnelData`**
- `string $name` (e.g., `'Desmond Yong Qiang'`, `'Alex Lim'`)
- `int $age` (18-80)
- `Race $race`
- `Gender $gender`
- `string $phone_number` (Starts with 3, 6, 8, 9 representing local SG telecom assignments)

**`ResidentData`**
Combines properties of both structures above into a cohesive single flat DTO footprint.
