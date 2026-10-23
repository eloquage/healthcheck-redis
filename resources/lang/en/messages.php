<?php

declare(strict_types=1);

return [
    'installed' => 'redis extension is installed (version :version).',
    'missing' => 'redis extension is required but not installed.',
    'not_installed' => 'redis extension is not installed.',
    'query_failed' => 'Could not verify redis extension: :message',
    'memory_warning' => 'Redis memory usage is :percent% and has reached the :threshold% warning threshold.',
    'memory_limit_reached' => 'Redis memory usage has reached :percent% of its maxmemory limit.',
];
