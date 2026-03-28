<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use YezzMedia\Foundation\Contracts\PlatformPackage;
use YezzMedia\Foundation\Data\PackageMetadata;

class FakePlatformPackage implements PlatformPackage
{
    public function __construct(
        private readonly string $name = 'yezzmedia/laravel-settings',
        private readonly bool $enabled = true,
    ) {}

    public function metadata(): PackageMetadata
    {
        return new PackageMetadata(
            name: $this->name,
            vendor: 'yezzmedia',
            description: 'Fake package used in tests.',
            packageClass: static::class,
            enabled: $this->enabled,
            priority: 10,
        );
    }
}
