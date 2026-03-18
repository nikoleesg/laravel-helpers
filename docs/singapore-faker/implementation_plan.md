# Goal Description
The objective is to implement a set of helper classes and Data Transfer Objects (DTOs) for generating fake Singapore personnel and address data. This is useful for seeding demonstration or staging data without relying exclusively on dev dependencies, making it accessible in production scenarios when required.
We will use `spatie/laravel-data` for the DTOs and `fakerphp/faker` for data generation. Both will be added to the package's dependencies.

## Proposed Changes

### Dependencies
#### [MODIFY] composer.json
- Add `"spatie/laravel-data": "^4.0"` and `"fakerphp/faker": "^1.23"` to the `require` section.

### Enums
#### [NEW] src/Enums/HouseType.php
- Enum with cases: `HDB` (70%), `Condominium` (20%), `Landed` (10%).
#### [NEW] src/Enums/Race.php
- Enum with cases: `Chinese` (70%), `Malay` (20%), `Indian` (5%), `Other` (5%).
#### [NEW] src/Enums/Gender.php
- Enum with cases: `Male`, `Female`.

### Data Transfer Objects (DTOs)
#### [NEW] src/Data/Singapore/AddressData.php
- Properties: `string $block`, `string $street`, `?string $unit`, `string $postal`, `HouseType $house_type`.
#### [NEW] src/Data/Singapore/PersonnelData.php
- Properties: `string $name`, `int $age`, `Gender $gender`, `string $phone_number`.
#### [NEW] src/Data/Singapore/ResidentData.php
- A flattened DTO combining both personnel and address information.
- Properties: `string $name`, `int $age`, `Gender $gender`, `string $phone_number`, `string $block`, `string $street`, `?string $unit`, `string $postal`, `HouseType $house_type`.

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
  - Return Format: Returns a single DTO object for singular methods (e.g. `address()`). Returns an `Illuminate\Support\Collection` of DTOs for plural methods (e.g. `addresses()`).
  - Parameters: By specifying parameters like `$houseType`, `$race`, or `$gender`, users can override the random weighting for both single and multiple item generations.
  - Address Generation: `block` generated up to 3 digits (with optional uppercase letter for HDB). `unit` generated with `#` prefix and standard format for HDB/Condominium. `postal` 6 digits.
  - Personnel Generation: Age generated between 18 and 80. Race instantiation determines the proper Faker locale (`en_SG` for Chinese, `ms_MY` for Malay, `en_IN` for Indian, `en_US` for Other) used for the name. Phone generated prefixed with 3, 6, 8, or 9 and exactly 8 digits.

## Verification Plan

### Automated Tests
#### [NEW] tests/Unit/Faker/SingaporeFakerTest.php
We will write Pest unit tests to verify:
- `SingaporeFaker::address()` generates valid properties based on house type constraints (e.g. `unit` exists only for non-Landed, `postal` is 6 digits, `block` only has letters if `HDB`).
- `SingaporeFaker::personnel()` generates valid properties (e.g. `phone_number` is 8 digits and starts with 3/6/8/9, `age` is between 18 and 80).
- `SingaporeFaker::resident()` returns precisely the flattened DTO with the combined properties.
- Generating with specific parameter overrides (e.g. `SingaporeFaker::address(houseType: HouseType::Landed)` ensures that generation adheres strictly to the passed parameters).
- The returned data types exactly match `DTO` for singular requests and `Collection` of `DTOs` when a count is provided.
- All tests will logically assert weights are respected in an aggregate context.
