<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Contracts;

use YezzMedia\Foundation\Data\CacheProfile;

interface DefinesCacheProfiles
{
    /**
     * @return array<int, CacheProfile>
     */
    public function cacheProfiles(): array;
}
