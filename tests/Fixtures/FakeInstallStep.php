<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use RuntimeException;
use YezzMedia\Foundation\Install\InstallStep;

class FakeInstallStep implements InstallStep
{
    /**
     * @var array<int, string>
     */
    private static array $handled = [];

    public function __construct(
        private readonly string $key,
        private readonly string $package,
        private readonly int $priority = 10,
        private readonly bool $shouldRun = true,
        private readonly bool $shouldFail = false,
    ) {}

    public function key(): string
    {
        return $this->key;
    }

    public function package(): string
    {
        return $this->package;
    }

    public function priority(): int
    {
        return $this->priority;
    }

    public function shouldRun(): bool
    {
        return $this->shouldRun;
    }

    public function handle(): void
    {
        if ($this->shouldFail) {
            throw new RuntimeException(sprintf('Step [%s] failed.', $this->key));
        }

        self::$handled[] = sprintf('%s:%s', $this->package, $this->key);
    }

    public static function reset(): void
    {
        self::$handled = [];
    }

    /**
     * @return array<int, string>
     */
    public static function handled(): array
    {
        return self::$handled;
    }
}
