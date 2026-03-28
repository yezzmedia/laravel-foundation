<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use YezzMedia\Foundation\Contracts\ProvidesDoctorChecks;
use YezzMedia\Foundation\Doctor\DoctorCheck;

class FakeDoctorPackage extends FakePlatformPackage implements ProvidesDoctorChecks
{
    /**
     * @param  array<int, DoctorCheck>  $checks
     */
    public function __construct(
        string $name = 'yezzmedia/laravel-health',
        private readonly array $checks = [],
        bool $enabled = true,
    ) {
        parent::__construct($name, $enabled);
    }

    public function doctorChecks(): array
    {
        return $this->checks;
    }
}
