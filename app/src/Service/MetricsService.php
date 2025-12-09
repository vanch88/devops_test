<?php

namespace App\Service;

use Prometheus\CollectorRegistry;
use Prometheus\Storage\InMemory;
use Prometheus\Storage\Adapter;

class MetricsService
{
    private CollectorRegistry $registry;

    public function __construct()
    {
        if (extension_loaded('apcu') && ini_get('apc.enabled')) {
            try {
                $adapter = new \Prometheus\Storage\APCng();
            } catch (\Throwable $e) {
                $adapter = new InMemory();
            }
        } elseif (extension_loaded('apc') && ini_get('apc.enabled')) {
            try {
                $adapter = new \Prometheus\Storage\APC();
            } catch (\Throwable $e) {
                $adapter = new InMemory();
            }
        } else {
            $adapter = new InMemory();
        }
        $this->registry = new CollectorRegistry($adapter);
    }

    public function getRegistry(): CollectorRegistry
    {
        return $this->registry;
    }

    public function recordHttpRequest(string $method, string $route, int $statusCode, float $duration): void
    {
        $counter = $this->registry->getOrRegisterCounter(
            'app',
            'http_requests_total',
            'Total number of HTTP requests',
            ['method', 'route', 'status']
        );
        $counter->incBy(1, [$method, $route, (string)$statusCode]);

        $histogram = $this->registry->getOrRegisterHistogram(
            'app',
            'http_request_duration_seconds',
            'HTTP request duration in seconds',
            ['method', 'route', 'status'],
            [0.1, 0.5, 1, 2, 5, 10]
        );
        $histogram->observe($duration, [$method, $route, (string)$statusCode]);
    }

    public function recordValidationError(string $endpoint): void
    {
        $counter = $this->registry->getOrRegisterCounter(
            'app',
            'validation_errors_total',
            'Total number of validation errors',
            ['endpoint']
        );
        $counter->incBy(1, [$endpoint]);
    }

    public function recordDatabaseQuery(string $operation, float $duration): void
    {
        $histogram = $this->registry->getOrRegisterHistogram(
            'app',
            'database_query_duration_seconds',
            'Database query duration in seconds',
            ['operation'],
            [0.01, 0.05, 0.1, 0.5, 1, 2]
        );
        $histogram->observe($duration, [$operation]);
    }

    public function setUsersCount(int $count): void
    {
        $gauge = $this->registry->getOrRegisterGauge(
            'app',
            'users_total',
            'Total number of users',
            []
        );
        $gauge->set($count);
    }

    public function setPostsCount(int $count): void
    {
        $gauge = $this->registry->getOrRegisterGauge(
            'app',
            'posts_total',
            'Total number of posts',
            []
        );
        $gauge->set($count);
    }
}

