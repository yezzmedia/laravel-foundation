<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Install;

interface OptionalInstallStep
{
    public function isOptional(): bool;
}
