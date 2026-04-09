<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Data;

final readonly class SecurityRequestDefinition
{
    /**
     * @param  array<string, string>  $payloadSchema
     * @param  array<int, string>  $allowedPreviewFields
     * @param  array<int, string>  $maskedFields
     */
    public function __construct(
        public string $key,
        public string $package,
        public string $domain,
        public string $control,
        public string $scope,
        public string $requestedLevel,
        public string $requestedEnforcementMode,
        public string $description,
        public array $payloadSchema = [],
        public array $allowedPreviewFields = [],
        public array $maskedFields = [],
        public ?string $notes = null,
    ) {}
}
