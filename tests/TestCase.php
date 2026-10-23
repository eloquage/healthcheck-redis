<?php

declare(strict_types=1);

namespace IllumaLaw\HealthCheckRedis\Tests;

use IllumaLaw\HealthCheckRedis\HealthcheckRedisServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\Health\HealthServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            HealthServiceProvider::class,
            HealthcheckRedisServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.redis.client', 'phpredis');
        $redisHost = getenv('REDIS_HOST');
        $redisPort = getenv('REDIS_PORT');
        $app['config']->set('database.redis.default', [
            'host' => is_string($redisHost) && $redisHost !== '' ? $redisHost : '127.0.0.1',
            'port' => is_string($redisPort) && $redisPort !== '' ? $redisPort : '6379',
            'database' => '0',
        ]);
    }
}
