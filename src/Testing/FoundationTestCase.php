<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Testing;

use Orchestra\Testbench\TestCase as Orchestra;
use YezzMedia\Foundation\FoundationServiceProvider;

/**
 * Provides a stable Testbench baseline for packages that consume foundation.
 */
abstract class FoundationTestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            FoundationServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('cache.default', 'array');
        $app['config']->set('session.driver', 'array');
        $app['config']->set('queue.default', 'sync');
        // Tests often register additional fake packages after boot, so sealing would
        // block the lightweight helpers that this base class is meant to support.
        $app['config']->set('foundation.registry.seal_after_boot', false);
    }
}
