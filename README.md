# Laravel helpers collection

[![Latest Version on Packagist](https://img.shields.io/packagist/v/nikoleesg/laravel-helpers.svg?style=flat-square)](https://packagist.org/packages/nikoleesg/laravel-helpers)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/nikoleesg/laravel-helpers/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/nikoleesg/laravel-helpers/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/nikoleesg/laravel-helpers/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/nikoleesg/laravel-helpers/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/nikoleesg/laravel-helpers.svg?style=flat-square)](https://packagist.org/packages/nikoleesg/laravel-helpers)

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/laravel-helpers.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/laravel-helpers)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation

You can install the package via composer:

```bash
composer require nikoleesg/laravel-helpers
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="laravel-helpers-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-helpers-config"
```

This is the contents of the published config file:

```php
return [
];
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="laravel-helpers-views"
```

## Usage

### Collection Math Macros

This package provides convenient math operations that can be performed directly on Laravel Collections.

#### `addValueByKey`

Adds values by key between two collections/arrays. Keeps the left-hand collection's keys. Missing keys on the right are treated as 0.

```php
$collection = collect(['a' => 1, 'b' => 2, 'c' => 3]);
$result = $collection->addValueByKey(['a' => 10, 'b' => 20]);

$result->toArray(); // ['a' => 11, 'b' => 22, 'c' => 3]
```

#### `subtractValueByKey`

Subtracts values by key between two collections/arrays. Keeps the left-hand collection's keys. Missing keys on the right are treated as 0.

```php
$collection = collect(['a' => 10, 'b' => 20, 'c' => 30]);
$result = $collection->subtractValueByKey(['a' => 5, 'c' => 10]);

$result->toArray(); // ['a' => 5, 'b' => 20, 'c' => 20]
```

#### `multiplyValues`

Multiplies each item in the collection by a scalar value.

```php
$collection = collect(['a' => 10, 'b' => 20, 'c' => 30]);
$result = $collection->multiplyValues(2);

$result->toArray(); // ['a' => 20, 'b' => 40, 'c' => 60]
```

#### `divideValues`

Divides each item in the collection by a scalar value. Throws an `InvalidArgumentException` if the divisor is zero.

```php
$collection = collect(['a' => 10, 'b' => 20, 'c' => 30]);
$result = $collection->divideValues(2);

$result->toArray(); // ['a' => 5, 'b' => 10, 'c' => 15]
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Niko Lee](https://github.com/nikoleesg)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
