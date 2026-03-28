<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Events;

final readonly class WebsiteInstalled
{
    /**
     * @param  array<string, mixed>|null  $context
     */
    public function __construct(
        public string $status,
        public int $executedStepCount,
        public int $failedStepCount,
        public ?array $context = null,
    ) {}
}
