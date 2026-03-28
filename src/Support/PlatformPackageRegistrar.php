<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Support;

use YezzMedia\Foundation\Contracts\PlatformPackage;
use YezzMedia\Foundation\Contracts\RegistersFeatures;
use YezzMedia\Foundation\Data\FeatureDefinition;
use YezzMedia\Foundation\Data\PackageMetadata;
use YezzMedia\Foundation\Events\FeatureRegistered;
use YezzMedia\Foundation\Events\PackageRegistered;
use YezzMedia\Foundation\Exceptions\InvalidPackageDefinitionException;
use YezzMedia\Foundation\Registry\FeatureRegistry;
use YezzMedia\Foundation\Registry\PackageRegistry;

class PlatformPackageRegistrar
{
    public function __construct(
        private readonly PackageRegistry $packages,
        private readonly FeatureRegistry $features,
    ) {}

    public function register(PlatformPackage $package): void
    {
        $metadata = $package->metadata();

        $this->ensureValidPackageMetadata($package, $metadata);

        $this->packages->register($metadata);

        event(new PackageRegistered($metadata));

        if (! $metadata->enabled || ! $package instanceof RegistersFeatures) {
            return;
        }

        foreach ($package->featureDefinitions() as $featureDefinition) {
            $this->ensureValidFeatureDefinition($metadata, $featureDefinition);
            $this->features->register($featureDefinition);

            event(new FeatureRegistered($featureDefinition));
        }
    }

    private function ensureValidPackageMetadata(PlatformPackage $package, PackageMetadata $metadata): void
    {
        if ($metadata->vendor === '') {
            throw new InvalidPackageDefinitionException('Package vendor must not be empty.');
        }

        if ($metadata->description === '') {
            throw new InvalidPackageDefinitionException('Package description must not be empty.');
        }

        if ($metadata->packageClass !== $package::class) {
            throw new InvalidPackageDefinitionException(sprintf(
                'Package class [%s] does not match descriptor [%s].',
                $metadata->packageClass,
                $package::class,
            ));
        }
    }

    private function ensureValidFeatureDefinition(PackageMetadata $metadata, FeatureDefinition $featureDefinition): void
    {
        if ($featureDefinition->package !== $metadata->name) {
            throw new InvalidPackageDefinitionException(sprintf(
                'Feature [%s] must belong to package [%s].',
                $featureDefinition->name,
                $metadata->name,
            ));
        }

        if ($featureDefinition->label === '') {
            throw new InvalidPackageDefinitionException(sprintf(
                'Feature [%s] must define a label.',
                $featureDefinition->name,
            ));
        }
    }
}
