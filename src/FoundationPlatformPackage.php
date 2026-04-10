<?php

declare(strict_types=1);

namespace YezzMedia\Foundation;

use YezzMedia\Foundation\Contracts\DefinesInstallSteps;
use YezzMedia\Foundation\Contracts\PlatformPackage;
use YezzMedia\Foundation\Contracts\ProvidesDoctorChecks;
use YezzMedia\Foundation\Data\PackageMetadata;
use YezzMedia\Foundation\Doctor\FoundationHttpMiddlewareBridgeConfiguredCheck;
use YezzMedia\Foundation\Install\ConfigureHttpMiddlewareBridgeInstallStep;

/**
 * Describes the foundation package inside the platform registry itself.
 */
final class FoundationPlatformPackage implements DefinesInstallSteps, PlatformPackage, ProvidesDoctorChecks
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

    public function installSteps(): array
    {
        return [
            new ConfigureHttpMiddlewareBridgeInstallStep,
        ];
    }

    public function doctorChecks(): array
    {
        return [
            new FoundationHttpMiddlewareBridgeConfiguredCheck,
        ];
    }
}
