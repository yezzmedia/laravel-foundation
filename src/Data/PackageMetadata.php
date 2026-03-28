<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Data;

use YezzMedia\Foundation\Contracts\PlatformPackage;

final readonly class PackageMetadata
{
    /**
     * @param  class-string<PlatformPackage>  $packageClass
     */
    public function __construct(
        public string $name,
        public string $vendor,
        public string $description,
        public string $packageClass,
        public bool $enabled = true,
        public ?int $priority = null,
    ) {}
}
