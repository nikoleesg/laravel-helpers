# Goal Description

The goal is to introduce two new traits to the `nikoleesg/laravel-helpers` package:
1. `HasUuid`: A trait that handles automatic UUID generation for Eloquent models upon creation, with configurable properties for the column name, route key, and whether it acts as the primary key.
2. `HasTablePrefix`: A trait that dynamically prefixes the guessed database table name of an Eloquent model using a defined property.

## Proposed Changes

> [!NOTE]
> Based on Laravel naming conventions, traits that add capabilities to models typically start with `Has...` (e.g., `HasFactory`, `HasApiTokens`). Therefore, the recommended names are `HasUuid` and `HasTablePrefix`.

### Traits
---
#### [NEW] [HasUuid.php](file:///Users/nikolee/projects/php/packages/nikoleesg/laravel-helpers/src/Traits/HasUuid.php)
This trait will automatically generate a UUID during the `creating` event of the Eloquent model.

**Properties to be used by the model:**
- `protected string $uuidColumn`: (Optional) Defaults to `'uuid'`.
- `protected bool $useUuidForRouteKey`: (Optional) Defaults to `false`. If true, the route key resolves to the UUID column.
- `protected bool $useUuidAsPrimaryKey`: (Optional) Defaults to `false`. If true, overrides Laravel's default primary key logic (disables incrementing, sets key type to string, and uses the UUID column as the key name).

**Implementation Details:**
- Boot method: `bootHasUuid()` running `static::creating(...)`.
- Override methods: `getRouteKeyName()`, `getKeyName()`, `getIncrementing()`, and `getKeyType()`.

---
#### [NEW] [HasTablePrefix.php](file:///Users/nikolee/projects/php/packages/nikoleesg/laravel-helpers/src/Traits/HasTablePrefix.php)
This trait prefixes the model's dynamically guessed table name.

**Properties to be used by the model:**
- `protected string $tablePrefix`: (Optional) Defaults to `''`. For example, `'inv_'`.

**Implementation Details:**
- Override method: `getTable()`.
- Logic: Check if `$this->table` is strictly defined on the model. If defined, respect it unchanged. If not (the table name is derived from the model's class name using `class_basename()`), prepend the prefix: `return ($this->tablePrefix ?? '') . Str::snake(Str::pluralStudly(class_basename($this)));`.

## Verification Plan

### Automated Tests
I will add comprehensive tests for both traits in the `tests/Traits/` directory.

- `tests/Traits/HasUuidTest.php`:
   - Test default configuration (generates UUID on `uuid` column, standard primary key/route key).
   - Test custom column name.
   - Test setting `$useUuidForRouteKey = true` and verifying `getRouteKeyName()`.
   - Test setting `$useUuidAsPrimaryKey = true` and verifying `getKeyName()`, `getIncrementing()`, and `getKeyType()`.

- `tests/Traits/HasTablePrefixTest.php`:
   - Test a standard model with the trait and verify `getTable()` returns the base name with the custom prefix.
   - Test a model with an explicitly defined `$table` name and verify that the table prefix is ignored (respects the hardcoded table property).
