<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Testing;

use Orchestra\Testbench\TestCase as Orchestra;
use YezzMedia\Foundation\FoundationServiceProvider;

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
        $app['config']->set('foundation.registry.seal_after_boot', false);
    }
}
