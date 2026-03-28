<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Events;

use YezzMedia\Foundation\Data\InstallResult;

final readonly class WebsiteInstalled
{
    public function __construct(public InstallResult $result) {}
}
