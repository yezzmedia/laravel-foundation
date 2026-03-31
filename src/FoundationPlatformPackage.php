<?php

declare(strict_types=1);

namespace YezzMedia\Foundation;

use YezzMedia\Foundation\Contracts\PlatformPackage;
use YezzMedia\Foundation\Data\PackageMetadata;

/**
 * Describes the foundation package inside the platform registry itself.
 */
final class FoundationPlatformPackage implements PlatformPackage
{
    public function metadata(): PackageMetadata
    {
        return new PackageMetadata(
            name: 'yezzmedia/laravel-foundation',
            vendor: 'yezzmedia',
            description: 'Shared platform core for Yezz Media Laravel packages.',
            packageClass: self::class,
            priority: 0,
        );
    }
}
