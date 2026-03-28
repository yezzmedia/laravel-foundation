<?php

declare(strict_types=1);

namespace Tests;

use YezzMedia\Foundation\Registry\FeatureRegistry;
use YezzMedia\Foundation\Registry\OpsModuleRegistry;
use YezzMedia\Foundation\Registry\PackageRegistry;
use YezzMedia\Foundation\Registry\PermissionRegistry;
use YezzMedia\Foundation\Support\PackageManifestLoader;
use YezzMedia\Foundation\Testing\FoundationTestCase;

abstract class SealedFoundationTestCase extends FoundationTestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('foundation.registry.seal_after_boot', true);
    }

    protected function setUp(): void
    {
        parent::setUp();

        app(PackageRegistry::class)->seal();
        app(FeatureRegistry::class)->seal();
        app(PermissionRegistry::class)->seal();
        app(OpsModuleRegistry::class)->seal();
        app(PackageManifestLoader::class)->seal();
    }
}
