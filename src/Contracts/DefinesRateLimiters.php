<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Contracts;

use YezzMedia\Foundation\Data\RateLimitDefinition;

interface DefinesRateLimiters
{
    /**
     * @return array<int, RateLimitDefinition>
     */
    public function rateLimitDefinitions(): array;
}
