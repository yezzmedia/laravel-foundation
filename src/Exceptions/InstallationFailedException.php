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
            message: sprintf('Install step [%s] for package [%s] failed. %s', $step, $package, $previous->getMessage()),
            previous: $previous,
        );
    }
}
