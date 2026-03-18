# Singapore Faker Helpers Walkthrough

## Overview
Successfully implemented helper classes to generate Singapore-specific fake data including Addresses, Personnel, and combined Resident profiles. 

## Changes Made
- Added `fakerphp/faker` and `spatie/laravel-data` to the `require` block in `composer.json`.
- Created Enums in `src/Enums/`:
  - `HouseType` (HDB, Condominium, Landed)
  - `Race` (Chinese, Malay, Indian, Other)
  - `Gender` (Male, Female)
- Created DTOs in `src/Data/Singapore/`:
  - `AddressData` 
  - `PersonnelData`
  - `ResidentData` (Flat combination of the Address & Personnel properties)
- Created main logic class in `src/Faker/SingaporeFaker.php` with 3 robust static generators:
  - `address(houseType)` / `addresses(count)`
  - `personnel(race, gender)` / `personnels(count)`
  - `resident(houseType, race, gender)` / `residents(count)`
- Created the user manual in `docs/singapore-faker/user_manual.md`.
- Implemented weighted logic (70/20/10 House Type, 70/20/5/5 Race) leveraging the package's existing `normalizeBySum()`, `multiplyValues()`, and `largestRemainderRound()` collection macros to guarantee exact percentage distribution during generation arrays.

## Testing & Verification
- Unit test file created at `tests/Unit/Faker/SingaporeFakerTest.php`.
- Verified that returning `$count = 1` yields exactly one valid DTO (`AddressData|PersonnelData|ResidentData`).
- Verified that returning `$count > 1` yields exactly an `Illuminate\Support\Collection` of DTOs.
- Verified properties comply with strict SG rules: 6-digit postal codes, 8-digit mobile starting with 3,6,8,9, and unit block strings only created for HDB/Condominiums starting with `#`.
- Aggregate test executed to ensure generated payloads strictly adhered to mathematical percentage weights across 100 randomly requested entries.
- All 10 Pest unit tests covering 35 assertions passed flawlessly.
