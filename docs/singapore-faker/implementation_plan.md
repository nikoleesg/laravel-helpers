# Goal Description
The objective is to implement a set of helper classes and Data Transfer Objects (DTOs) for generating fake Singapore personnel and address data. This is useful for seeding demonstration or staging data without relying exclusively on dev dependencies, making it accessible in production scenarios when required.
We will use `spatie/laravel-data` for the DTOs and `fakerphp/faker` for data generation. Both will be added to the package's dependencies.

## Proposed Changes

### Dependencies
#### [MODIFY] composer.json
- Add `"spatie/laravel-data": "^4.0"` and `"fakerphp/faker": "^1.23"` to the `require` section.

### Enums
#### [NEW] src/Enums/HouseType.php
- Enum with cases: `HDB` (1), `Condominium` (2), `Landed` (3). Default distributions: 70%, 20%, 10%.
#### [NEW] src/Enums/Race.php
- Enum with cases: `Chinese` (1), `Malay` (2), `Indian` (3), `Other` (4). Default distributions: 70%, 20%, 5%, 5%.
#### [NEW] src/Enums/Gender.php
- Enum with cases: `Male` (1), `Female` (2).

### Data Transfer Objects (DTOs)
#### [NEW] src/Data/Singapore/AddressData.php
- Properties: `string $block`, `string $street`, `?string $unit`, `string $postal`, `HouseType $house_type`.
#### [NEW] src/Data/Singapore/PersonnelData.php
- Properties: `string $name`, `int $age`, `Race $race`, `Gender $gender`, `string $phone_number`.
#### [NEW] src/Data/Singapore/ResidentData.php
- A flattened DTO combining both personnel and address information.
- Properties: `string $name`, `int $age`, `Race $race`, `Gender $gender`, `string $phone_number`, `string $block`, `string $street`, `?string $unit`, `string $postal`, `HouseType $house_type`.

### Data Generator Helper
#### [NEW] src/Faker/SingaporeFaker.php
- `SingaporeFaker` class with the following methods:
  - `public static function address(?HouseType $houseType = null): AddressData`
  - `public static function addresses(int $count): Collection<int, AddressData>`
  - `public static function personnel(?Race $race = null, ?Gender $gender = null): PersonnelData`
  - `public static function personnels(int $count): Collection<int, PersonnelData>`
  - `public static function resident(?HouseType $houseType = null, ?Race $race = null, ?Gender $gender = null): ResidentData`
  - `public static function residents(int $count): Collection<int, ResidentData>`
- Implementation details:
  - Return Format: Returns a single strongly typed DTO object for singular methods. Returns an `Illuminate\Support\Collection` of DTOs for plural methods.
  - Parameters: Specify parameters to bypass random weighted generation for singular sets.
  - Accurate Scaling: Plural methods utilize `normalizeBySum()->multiplyValues($count)->largestRemainderRound($count)` macro to ensure generated Collections perfectly map the distribution weights across the requested `$count`.

### Custom Faker Providers
#### [NEW] src/Faker/Providers/SingaporeAddressProvider.php
- Extends `Faker\Provider\en_SG\Address`.
- Overloads `$streetNumber`, `$streetSuffix`, `$streetPrefix`, `$streetName`, `$streetAddressFormats`, `$floorNumber`, and `$apartmentNumber` properties exactly to Singapore lexical formats avoiding library oversights.
- Exposes `$houseType` injection locally securely managing the formatting of `blockNumber()` and HDB constraint logic on `unitNumber()`.

#### [NEW] src/Faker/Providers/SingaporePersonProvider.php
- Extends `Faker\Provider\en_SG\Person`.
- Extensively overrides lists for `$lastName`, `$firstNameMale`, `$firstNameFemale` with common Singaporean Chinese names (e.g., Tan, Lee, Goh, Wei Jie).
- Adds lists for common English identifiers (`$firstNameMaleEn`, `$firstNameFemaleEn`) typically used by SG Chinese (e.g., Alex, Desmond, Michelle, Rachael).
- Adjusts `$formats` generation logic to natively form permutations conforming to local real-world structures: `<Chinese Surname> <Chinese Given Name>`, `<English Name> <Chinese Surname>`, or `<English Name> <Chinese Surname> <Chinese Given Name>`.

## Verification Plan

### Automated Tests
#### [NEW] tests/Unit/Faker/SingaporeFakerTest.php
We will write Pest unit tests to verify:
- `SingaporeFaker::address()` generates valid properties based on house type constraints (e.g. `unit` exists only for non-Landed, `postal` is 6 digits, `block` generates randomly distributed text suffix for `HDB`).
- `SingaporeFaker::personnel()` overrides generic English dependencies and implements rigorous realistic variations of SG metrics.
- All plural implementations dynamically match counts precisely while enforcing deterministic mathematical aggregates based on House Type and Race arrays.
