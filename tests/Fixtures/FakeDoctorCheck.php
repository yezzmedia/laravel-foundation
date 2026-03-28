<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use YezzMedia\Foundation\Data\DoctorResult;
use YezzMedia\Foundation\Doctor\DoctorCheck;

class FakeDoctorCheck implements DoctorCheck
{
    /**
     * @var array<int, string>
     */
    private static array $executed = [];

    /**
     * @param  array<string, mixed>|null  $context
     */
    public function __construct(
        private readonly string $key,
        private readonly string $package,
        private readonly string $status = 'passed',
        private readonly string $message = 'Check passed.',
        private readonly bool $isBlocking = false,
        private readonly ?array $context = null,
        private readonly ?string $resultKey = null,
        private readonly ?string $resultPackage = null,
        private readonly ?string $resultStatus = null,
    ) {}

    public function key(): string
    {
        return $this->key;
    }

    public function package(): string
    {
        return $this->package;
    }

    public function run(): DoctorResult
    {
        self::$executed[] = sprintf('%s:%s', $this->package, $this->key);

        return new DoctorResult(
            key: $this->resultKey ?? $this->key,
            package: $this->resultPackage ?? $this->package,
            status: $this->resultStatus ?? $this->status,
            message: $this->message,
            isBlocking: $this->isBlocking,
            context: $this->context,
        );
    }

    public static function reset(): void
    {
        self::$executed = [];
    }

    /**
     * @return array<int, string>
     */
    public static function executed(): array
    {
        return self::$executed;
    }
}
