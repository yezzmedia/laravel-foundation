<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Data;

final readonly class SiteContext
{
    public function __construct(
        public string $environment,
        public ?string $locale = null,
    ) {}
}
