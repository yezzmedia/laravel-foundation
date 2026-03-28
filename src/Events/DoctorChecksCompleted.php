<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Events;

final readonly class DoctorChecksCompleted
{
    /**
     * @param  array{passed: int, warning: int, failed: int, skipped: int}  $summary
     */
    public function __construct(public array $summary) {}
}
