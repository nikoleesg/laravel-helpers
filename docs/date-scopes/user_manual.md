# Date Scopes User Manual

This package provides a wide range of useful **date scopes** for your Laravel Eloquent models via the `DateScopes` trait. It is a direct integration inspired by the [laravel-date-scopes](https://github.com/laracraft-tech/laravel-date-scopes) package.

```php
use Nikoleesg\LaravelHelpers\Traits\DateScopes;

class Transaction extends Model
{
    use DateScopes;
}

// query transactions created today
Transaction::ofToday();
// query transactions created during the last week
Transaction::ofLastWeek();
// query transactions created during the start of the current month till now
Transaction::monthToDate();
// query transactions created during the last year, start from 2020
Transaction::ofLastYear(startFrom: '2020-01-01');

// Chain any Builder function you want here.
Transaction::ofToday()->sum('amount');
Transaction::ofLastWeek()->avg('amount');
```

By default, the date scopes act selectively depending on your requirement (exclusive vs inclusive ranges). You can also fluently customize the time columns or ranges inline. See the [detailed blog post](https://www.laracraft.tech/blog/laravel-date-scopes-a-package-to-filter-eloquent-models-by-common-date-ranges-conveniently) for a deep dive into advanced usage like `startFrom` bounds or custom `created_at` fields.
