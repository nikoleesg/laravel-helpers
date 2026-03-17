# Eloquent Model Traits User Manual

This package provides traits to add standard, helpful behaviors to your Eloquent models.

## `HasUuid`

Automatically generates a UUID string when a model is being created.

```php
use Illuminate\Database\Eloquent\Model;
use Nikoleesg\LaravelHelpers\Traits\HasUuid;

class Post extends Model
{
    use HasUuid;
    
    // Optional properties for custom behavior:
    // protected string $uuidColumn = 'my_uuid';
    // protected bool $useUuidAsPrimaryKey = true;
    // protected bool $useUuidForRouteKey = true;
}
```

## `HasTablePrefix`

Automatically prefixes the table name of the model when guessed by Laravel. Handily bypasses the explicit `protected $table` declaration.

```php
use Illuminate\Database\Eloquent\Model;
use Nikoleesg\LaravelHelpers\Traits\HasTablePrefix;

class Invoice extends Model
{
    use HasTablePrefix;
    
    // Prefix to prepend to the dynamically guessed table name (e.g., 'invoices')
    protected string $tablePrefix = 'inv_'; 
    
    // Resolves table name to: 'inv_invoices'
}
```
