<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Doctor;

use YezzMedia\Foundation\Data\DoctorResult;

interface DoctorCheck
{
    public function key(): string;

    public function package(): string;

    public function run(): DoctorResult;
}
