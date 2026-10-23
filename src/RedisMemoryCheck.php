<?php

declare(strict_types=1);

namespace IllumaLaw\HealthCheckRedis;

use Illuminate\Support\Facades\Redis;
use InvalidArgumentException;
use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;
use Throwable;

class RedisMemoryCheck extends Check
{
    protected string $connectionName = 'default';

    protected ?int $warningThresholdPercent = null;

    public function connectionName(string $connectionName): self
    {
        $this->connectionName = $connectionName;

        return $this;
    }

    public function connection(string $connectionName): self
    {
        return $this->connectionName($connectionName);
    }

    public function thresholdPercent(int $thresholdPercent): self
    {
        if ($thresholdPercent < 0 || $thresholdPercent > 100) {
            throw new InvalidArgumentException('The Redis memory warning threshold must be between 0 and 100.');
        }

        $this->warningThresholdPercent = $thresholdPercent;

        return $this;
    }

    public function run(): Result
    {
        $baseMeta = ['connection_name' => $this->connectionName];

        try {
            $connection = Redis::connection($this->connectionName);
            $ping = $connection->ping();
        } catch (Throwable $exception) {
            return Result::make()
                ->meta($baseMeta)
                ->failed("An exception occurred when connecting to Redis: `{$exception->getMessage()}`");
        }

        if ($ping === false) {
            return Result::make()
                ->meta($baseMeta)
                ->failed('Redis returned a falsy response when trying to connect to it.');
        }

        try {
            /** @var array<string, mixed>|false $memory */
            $memory = $connection->info('memory');
        } catch (Throwable $exception) {
            return Result::make()
                ->meta($baseMeta)
                ->failed("Redis ping succeeded but INFO memory failed: {$exception->getMessage()}");
        }

        if (! is_array($memory) || $memory === []) {
            return Result::make()
                ->meta($baseMeta)
                ->ok('Redis is reachable.')
                ->shortSummary('Ping OK');
        }

        $usedHumanValue = $memory['used_memory_human'] ?? null;
        $maxHumanValue = $memory['maxmemory_human'] ?? null;
        $usedBytesValue = $memory['used_memory'] ?? null;
        $maxBytesValue = $memory['maxmemory'] ?? null;

        $usedHuman = is_scalar($usedHumanValue) ? (string) $usedHumanValue : '';
        $maxHuman = is_scalar($maxHumanValue) ? (string) $maxHumanValue : '';
        $usedBytes = is_numeric($usedBytesValue) ? (int) $usedBytesValue : 0;
        $maxBytes = is_numeric($maxBytesValue) ? (int) $maxBytesValue : 0;

        $usagePercent = null;
        $usagePercentPrecise = null;
        if ($maxBytes > 0) {
            $usagePercentPrecise = ($usedBytes / $maxBytes) * 100;
            $usagePercent = round($usagePercentPrecise, 1);
        }

        $meta = $baseMeta;

        if ($usedHuman !== '') {
            $meta['used_memory_human'] = $usedHuman;
        }

        if ($maxHuman !== '') {
            $meta['maxmemory_human'] = $maxHuman;
        }

        if ($usagePercent !== null) {
            $meta['memory_usage_percent'] = $usagePercent;
        }

        $summary = $usedHuman !== '' ? $usedHuman : 'Ping OK';
        if ($usagePercent !== null) {
            $summary .= " ({$usagePercent}% of max)";
        }

        $configuredThreshold = config('healthcheck-redis.memory_warning_threshold_percent', 90);
        $warningThreshold = $this->warningThresholdPercent
            ?? (is_numeric($configuredThreshold) ? (int) $configuredThreshold : 90);
        $warningThreshold = max(0, min(100, $warningThreshold));

        if ($usagePercentPrecise !== null && $usagePercentPrecise >= 100) {
            return Result::make()
                ->meta($meta)
                ->failed(trans('healthcheck-redis::messages.memory_limit_reached', ['percent' => $usagePercent]))
                ->shortSummary($summary);
        }

        if ($usagePercentPrecise !== null && $usagePercentPrecise >= $warningThreshold) {
            return Result::make()
                ->meta($meta)
                ->warning(trans('healthcheck-redis::messages.memory_warning', [
                    'percent' => $usagePercent,
                    'threshold' => $warningThreshold,
                ]))
                ->shortSummary($summary);
        }

        return Result::make()
            ->meta($meta)
            ->ok('Redis is reachable.')
            ->shortSummary($summary);
    }
}
