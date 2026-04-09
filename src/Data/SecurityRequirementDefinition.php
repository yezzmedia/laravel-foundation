<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Data;

final readonly class SecurityRequirementDefinition
{
    /**
     * @param  array<int, string>  $appliesTo
     */
    public function __construct(
        public string $key,
        public string $package,
        public string $domain,
        public string $control,
        public string $level,
        public string $scope,
        public string $description,
        public string $enforcementMode,
        public array $appliesTo = [],
        public ?string $notes = null,
    ) {}
}
