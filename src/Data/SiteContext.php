<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Data;

/**
 * Represents the minimal site context approved for foundation V1.
 */
final readonly class SiteContext
{
    public function __construct(
        public string $environment,
        public ?string $locale = null,
    ) {}
}
