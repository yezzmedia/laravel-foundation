<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Data;

/**
 * Represents one normalized doctor check result.
 */
final readonly class DoctorResult
{
    /**
     * @param  array<string, mixed>|null  $context
     */
    public function __construct(
        public string $key,
        public string $package,
        public string $status,
        public string $message,
        public bool $isBlocking,
        public ?array $context = null,
    ) {}
}
