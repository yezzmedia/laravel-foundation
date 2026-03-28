<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Events;

use Illuminate\Support\Collection;
use YezzMedia\Foundation\Data\DoctorResult;

final readonly class DoctorChecksCompleted
{
    /**
     * @param  Collection<int, DoctorResult>  $results
     * @param  array{passed: int, warning: int, failed: int, skipped: int}  $summary
     */
    public function __construct(
        public Collection $results,
        public array $summary,
    ) {}
}
