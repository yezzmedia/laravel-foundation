<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Data;

/**
 * @phpstan-type InstallStepReference array{package: string, step: string}
 */
final readonly class InstallResult
{
    /**
     * @param  array<int, InstallStepReference>  $executedSteps
     * @param  array<int, InstallStepReference>  $failedSteps
     * @param  array<int, string>  $messages
     * @param  array<string, mixed>|null  $context
     */
    public function __construct(
        public string $status,
        public array $executedSteps,
        public array $failedSteps,
        public array $messages,
        public ?array $context = null,
    ) {}
}
