<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Install;

use YezzMedia\Foundation\Data\InstallContext;

interface InstallStep
{
    public function key(): string;

    public function package(): string;

    public function priority(): int;

    public function shouldRun(InstallContext $context): bool;

    public function handle(InstallContext $context): void;
}
