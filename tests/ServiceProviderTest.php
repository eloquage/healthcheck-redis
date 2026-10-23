<?php

declare(strict_types=1);

use IllumaLaw\HealthCheckRedis\HealthcheckRedisServiceProvider;
use Illuminate\Support\ServiceProvider;

it('registers the config publish path under the correct tag', function () {
    $provider = app()->getProvider(HealthcheckRedisServiceProvider::class);
    if (! ($provider instanceof HealthcheckRedisServiceProvider)) {
        throw new RuntimeException('The package service provider must be registered.');
    }

    $source = dirname(__DIR__).'/src/../config/healthcheck-redis.php';

    expect(ServiceProvider::pathsToPublish($provider::class, 'healthcheck-redis-config'))
        ->toBe([$source => config_path('healthcheck-redis.php')]);
});

it('loads config with the correct default', function () {
    expect(config('healthcheck-redis.required'))->toBeFalse();
});
