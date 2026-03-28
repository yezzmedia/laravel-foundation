<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Contracts;

use YezzMedia\Foundation\Data\PackageMetadata;

interface PlatformPackage
{
    public function metadata(): PackageMetadata;
}
