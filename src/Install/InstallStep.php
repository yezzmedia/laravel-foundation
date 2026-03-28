<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Install;

interface InstallStep
{
    public function key(): string;

    public function package(): string;

    public function priority(): int;

    public function shouldRun(): bool;

    public function handle(): void;
}
