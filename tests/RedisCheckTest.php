<?php

declare(strict_types=1);

use IllumaLaw\HealthCheckRedis\RedisExtensionCheck;
use IllumaLaw\HealthCheckRedis\RedisMemoryCheck;
use Illuminate\Support\Facades\Redis;
use Spatie\Health\Enums\Status;

it('succeeds when redis extension is installed', function () {
    if (! extension_loaded('redis')) {
        $this->markTestSkipped('Redis extension not installed.');
    }

    $result = RedisExtensionCheck::new()->run();

    expect($result->status)->toEqual(Status::ok())
        ->and($result->meta)->toHaveKey('installed_version');
});

it('succeeds when redis is reachable', function () {
    $mockConnection = Mockery::mock();
    $mockConnection->shouldReceive('ping')->once()->andReturn(true);
    $mockConnection->shouldReceive('info')->once()->with('memory')->andReturn([
        'used_memory_human' => '1.23M',
        'maxmemory_human' => '100M',
        'used_memory' => 1230000,
        'maxmemory' => 100000000,
    ]);

    Redis::shouldReceive('connection')->with('default')->once()->andReturn($mockConnection);

    $result = RedisMemoryCheck::new()->run();

    expect($result->status)->toEqual(Status::ok())
        ->and($result->notificationMessage)->toBe('Redis is reachable.')
        ->and($result->shortSummary)->toContain('1.23M');
});

it('fails when redis is unreachable', function () {
    Redis::shouldReceive('connection')->with('default')->once()->andThrow(new Exception('Connection failed'));

    $result = RedisMemoryCheck::new()->run();

    expect($result->status)->toEqual(Status::failed())
        ->and($result->notificationMessage)->toContain('An exception occurred when connecting to Redis');
});

it('warns when memory usage reaches the configured threshold', function () {
    $mockConnection = Mockery::mock();
    $mockConnection->shouldReceive('ping')->once()->andReturn(true);
    $mockConnection->shouldReceive('info')->once()->with('memory')->andReturn([
        'used_memory_human' => '80M',
        'maxmemory_human' => '100M',
        'used_memory' => 80,
        'maxmemory' => 100,
    ]);

    Redis::shouldReceive('connection')->with('cache')->once()->andReturn($mockConnection);

    $result = RedisMemoryCheck::new()
        ->connection('cache')
        ->thresholdPercent(80)
        ->run();

    expect($result->status)->toEqual(Status::warning())
        ->and($result->notificationMessage)->toContain('80%')
        ->and($result->shortSummary)->toContain('80% of max');
});

it('uses the configured memory warning threshold', function () {
    config()->set('healthcheck-redis.memory_warning_threshold_percent', 50);

    $mockConnection = Mockery::mock();
    $mockConnection->shouldReceive('ping')->once()->andReturn(true);
    $mockConnection->shouldReceive('info')->once()->with('memory')->andReturn([
        'used_memory' => 60,
        'maxmemory' => 100,
    ]);

    Redis::shouldReceive('connection')->with('default')->once()->andReturn($mockConnection);

    $result = RedisMemoryCheck::new()->run();

    expect($result->status)->toEqual(Status::warning())
        ->and($result->meta['memory_usage_percent'])->toBe(60.0);
});

it('fails when memory usage reaches maxmemory', function () {
    $mockConnection = Mockery::mock();
    $mockConnection->shouldReceive('ping')->once()->andReturn(true);
    $mockConnection->shouldReceive('info')->once()->with('memory')->andReturn([
        'used_memory_human' => '100M',
        'maxmemory_human' => '100M',
        'used_memory' => 100,
        'maxmemory' => 100,
    ]);

    Redis::shouldReceive('connection')->with('default')->once()->andReturn($mockConnection);

    $result = RedisMemoryCheck::new()->run();

    expect($result->status)->toEqual(Status::failed())
        ->and($result->notificationMessage)->toContain('reached 100%');
});

it('rejects a memory warning threshold outside zero through one hundred', function () {
    expect(fn () => RedisMemoryCheck::new()->thresholdPercent(101))
        ->toThrow(InvalidArgumentException::class);
});
