<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Exceptions;

use RuntimeException;
use Throwable;

class InstallationFailedException extends RuntimeException
{
    public static function forStep(string $package, string $step, Throwable $previous): self
    {
        return new self(
            message: sprintf('Install step [%s] for package [%s] failed.', $step, $package),
            previous: $previous,
        );
    }
}
