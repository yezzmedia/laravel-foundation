<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Events;

use YezzMedia\Foundation\Data\PackageMetadata;

final readonly class PackageRegistered
{
    public function __construct(public PackageMetadata $package) {}
}
