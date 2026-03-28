<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Data;

final readonly class CacheProfile
{
    /**
     * @param  array<int, string>|null  $tags
     * @param  array<int, string>  $invalidationEvents
     */
    public function __construct(
        public string $key,
        public string $package,
        public string $prefix,
        public ?array $tags,
        public int $ttl,
        public array $invalidationEvents,
        public ?string $description = null,
    ) {}
}
