<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Contracts;

use YezzMedia\Foundation\Data\SiteContext;

interface ResolvesSiteContext
{
    public function resolve(): SiteContext;
}
