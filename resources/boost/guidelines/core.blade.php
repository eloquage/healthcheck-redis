# eloquage/healthcheck-redis

Checks if the `vector` extension (redis) is enabled and active in PostgreSQL.

## Usage

```php
use IllumaLaw\HealthCheckRedis\RedisExtensionCheck;
use Spatie\Health\Facades\Health;

Health::checks([
    RedisExtensionCheck::new()
        ->required(true), // If true, FAIL if missing. If false, WARNING.
]);
```

## Configuration

Publish config: `php artisan vendor:publish --tag="healthcheck-redis-config"`

Options in `config/healthcheck-redis.php`:
- `required`: (bool) Global default for strictness.
