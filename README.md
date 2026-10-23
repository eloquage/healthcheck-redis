# Healthcheck redis for Laravel

[![Tests](https://github.com/eloquage/healthcheck-redis/actions/workflows/run-tests.yml/badge.svg)](https://github.com/eloquage/healthcheck-redis/actions)
[![Packagist License](https://img.shields.io/badge/Licence-MIT-blue)](http://choosealicense.com/licenses/mit/)
[![Latest Stable Version](https://img.shields.io/packagist/v/illuma-law/healthcheck-redis?label=Version)](https://packagist.org/packages/illuma-law/healthcheck-redis)

A focused redis health check for Spatie's [Laravel Health](https://spatie.be/docs/laravel-health/v1/introduction) package.

This package provides direct health checks to verify that the PHP `redis` extension is installed and that your Redis instance is operating within safe memory limits.

## Features

- **Extension Detection:** Checks if the PHP `redis` extension is enabled and reports the specific version installed.
- **Memory Monitoring:** Verifies that your Redis instance is not exceeding its `maxmemory` limit (or a custom threshold).
- **Configurable Strictness:** Choose whether a missing extension or high memory usage should return a Warning or a Failure status.

## Installation

Require this package with composer:

```shell
composer require illuma-law/healthcheck-redis
```

## Usage & Integration

Register the checks inside your application's health service provider (e.g. `AppServiceProvider` or a dedicated `HealthServiceProvider`), alongside your other Spatie Laravel Health checks:

### 1. Redis Extension Check

```php
use IllumaLaw\HealthCheckRedis\RedisExtensionCheck;
use Spatie\Health\Facades\Health;

Health::checks([
    RedisExtensionCheck::new()->required(true),
]);
```

### 2. Redis Memory Check

```php
use IllumaLaw\HealthCheckRedis\RedisMemoryCheck;
use Spatie\Health\Facades\Health;

Health::checks([
    RedisMemoryCheck::new()
        ->connection('default')
        ->thresholdPercent(90),
]);
```

### Expected Result States

- **Ok:** The extension is installed and Redis memory usage is within safe limits.
- **Warning:** The extension is missing (if not required) or Redis memory usage is above the warning threshold.
- **Failed:** The extension is missing (if required) or Redis has reached its `maxmemory` limit.

## Testing

Run the test suite:

```shell
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
