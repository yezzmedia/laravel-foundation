<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Events;

final readonly class PackageRegistered
{
    public function __construct(public string $packageName) {}
}
