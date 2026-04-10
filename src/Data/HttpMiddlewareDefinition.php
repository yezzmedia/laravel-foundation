<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Data;

final readonly class HttpMiddlewareDefinition
{
    public function __construct(
        public string $key,
        public string $package,
        public string $middleware,
        public string $kind,
        public ?string $alias = null,
        public bool $enabled = true,
        public string $description = '',
    ) {}
}
