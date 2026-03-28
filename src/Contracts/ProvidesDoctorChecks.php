<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Contracts;

use YezzMedia\Foundation\Doctor\DoctorCheck;

interface ProvidesDoctorChecks
{
    /**
     * @return array<int, DoctorCheck>
     */
    public function doctorChecks(): array;
}
