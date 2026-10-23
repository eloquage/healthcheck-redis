# Healthcheck Redis for Laravel

![Healthcheck Redis banner](art/banner.png)

[![Tests](https://github.com/eloquage/healthcheck-redis/actions/workflows/run-tests.yml/badge.svg)](https://github.com/eloquage/healthcheck-redis/actions/workflows/run-tests.yml)
[![Latest Stable Version](https://img.shields.io/packagist/v/illuma-law/healthcheck-redis?label=Version)](https://packagist.org/packages/illuma-law/healthcheck-redis)
[![License](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE.md)

Two checks for [Spatie Laravel Health](https://spatie.be/docs/laravel-health): one reports whether the PHP Redis extension is installed, and one pings Redis and reports memory use against its configured `maxmemory` limit.

## Requirements

- PHP 8.3 or later
- Laravel 11, 12, or 13
- `spatie/laravel-health` 1.39 or later

## Installation

```bash
composer require illuma-law/healthcheck-redis
```

The service provider is discovered automatically. Publish the configuration if you want to change the defaults:

```bash
php artisan vendor:publish --tag=healthcheck-redis-config
```

Set `HEALTH_REDIS_REQUIRED=true` to fail when the PHP Redis extension is missing. Set `HEALTH_REDIS_MEMORY_WARNING_PERCENT=90` to choose the memory usage percentage that triggers a warning. Memory at or above 100% of Redis `maxmemory` is reported as failed. When `maxmemory` is unlimited or unavailable, the check reports reachability and any available memory information without calculating a percentage.

## Usage

Register the checks in your health service provider:

```php
use IllumaLaw\HealthCheckRedis\RedisExtensionCheck;
use IllumaLaw\HealthCheckRedis\RedisMemoryCheck;
use Spatie\Health\Facades\Health;

Health::checks([
    RedisExtensionCheck::new(),
    RedisMemoryCheck::new()
        ->connection('default')
        ->thresholdPercent(90),
]);
```

`RedisExtensionCheck` warns when the extension is missing by default. Use `RedisExtensionCheck::new()->required()` or set `HEALTH_REDIS_REQUIRED=true` to make that a failure. `RedisMemoryCheck` warns at its configured threshold and fails when usage reaches `maxmemory`.

## Testing

```bash
composer test
composer analyse
vendor/bin/pint --test
```

## License

MIT. See [LICENSE.md](LICENSE.md).
