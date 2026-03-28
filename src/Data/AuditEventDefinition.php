<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Data;

final readonly class AuditEventDefinition
{
    /**
     * @param  array<int, string>|null  $contextKeys
     */
    public function __construct(
        public string $key,
        public string $package,
        public string $action,
        public string $subjectType,
        public string $description,
        public ?string $severity = null,
        public ?array $contextKeys = null,
    ) {}
}
