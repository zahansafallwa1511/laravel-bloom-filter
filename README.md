# Laravel Bloom Filter

A simple and efficient Bloom filter implementation for Laravel using Redis as the storage backend. This package provides an easy way to implement probabilistic data structures for checking set membership with minimal memory usage.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/zahansafallwa1511/laravel-bloom-filter.svg?style=flat-square)](https://packagist.org/packages/zahansafallwa1511/laravel-bloom-filter)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/zahansafallwa1511/laravel-bloom-filter/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/zahansafallwa1511/laravel-bloom-filter/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/zahansafallwa1511/laravel-bloom-filter/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/zahansafallwa1511/laravel-bloom-filter/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/zahansafallwa1511/laravel-bloom-filter.svg?style=flat-square)](https://packagist.org/packages/zahansafallwa1511/laravel-bloom-filter)

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/laravel-bloom-filter.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/laravel-bloom-filter)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation

```bash
composer require zahansafallwa1511/laravel-bloom-filter
```

## Requirements

- Laravel 8.0 or higher
- Redis server with Redis extension for PHP

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="laravel-bloom-filter-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-bloom-filter-config"
```

This is the contents of the published config file:

```php
return [
];
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="laravel-bloom-filter-views"
```

## Usage

### Creating a Bloom Filter

You can create a new Bloom filter class using the artisan command:

```bash
php artisan bloom-filter:make UserEmailFilter
```

This will create a new class in `app/BloomFilters/UserEmailFilter.php` with all the necessary methods to override.

### Example Implementation

```php
namespace App\BloomFilters;

use Intimation\LaravelBloomFilter\BloomFilter;

class UserEmailFilter extends BloomFilter
{
    protected function getKey(): string
    {
        return 'users_email_filter';
    }

    protected function getSize(): int
    {
        return 1000000; // Size based on expected number of items
    }

    protected function getHashCount(): int
    {
        return 7; // Number of hash functions
    }

    protected function getHashAlgorithm(): string
    {
        return 'md5'; // Choose from: 'crc32', 'md5', 'sha1'
    }
}
```

### Using the Bloom Filter

```php
namespace App\Services;

use App\BloomFilters\UserEmailFilter;

class UserService
{
    public function __construct(
        private readonly UserEmailFilter $emailFilter
    ) {}

    public function isEmailRegistered(string $email): bool
    {
        return $this->emailFilter->exists($email);
    }

    public function registerEmail(string $email): void
    {
        $this->emailFilter->add($email);
    }
}
```

### Available Methods

- `add(string $value)`: Add an item to the Bloom filter
- `exists(string $value)`: Check if an item might exist in the filter
- `clear()`: Clear all items from the filter

### Configuration

The Bloom filter requires three main parameters:

1. **Size**: The number of bits in the filter
   - Formula: `m = -n * ln(p) / (ln(2)^2)`
   - Where:
     - n = expected number of items
     - p = desired false positive probability

2. **Hash Count**: Number of hash functions to use
   - Formula: `k = (m/n) * ln(2)`
   - Where:
     - m = size of the filter
     - n = expected number of items

3. **Hash Algorithm**: Choose from:
   - `crc32`: Fastest but less collision-resistant
   - `md5`: Good balance of speed and collision resistance
   - `sha1`: Most collision-resistant but slower

### Important Notes

- The Bloom filter may return false positives (saying an item exists when it doesn't)
- It will never return false negatives (saying an item doesn't exist when it does)
- The filter requires Redis as the storage backend
- The size and hash count should be carefully chosen based on your expected data size and desired false positive rate

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

- [Mohammad Sajib Jahan](https://github.com/zahansafallwa1511)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
