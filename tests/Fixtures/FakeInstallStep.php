<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use RuntimeException;
use YezzMedia\Foundation\Data\InstallContext;
use YezzMedia\Foundation\Install\InstallStep;

class FakeInstallStep implements InstallStep
{
    /**
     * @var array<int, string>
     */
    private static array $handled = [];

    /**
     * @var array<int, array{reference: string, allow_migrations: bool, refresh_published_resources: bool}>
     */
    private static array $handledContexts = [];

    public function __construct(
        private readonly string $key,
        private readonly string $package,
        private readonly int $priority = 10,
        private readonly bool $shouldRun = true,
        private readonly bool $shouldFail = false,
        private readonly bool $requiresMigrations = false,
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

    public function shouldRun(InstallContext $context): bool
    {
        if (! $this->shouldRun) {
            return false;
        }

        if ($this->requiresMigrations && ! $context->allowMigrations) {
            return false;
        }

        return true;
    }

    public function handle(InstallContext $context): void
    {
        if ($this->shouldFail) {
            throw new RuntimeException(sprintf('Step [%s] failed.', $this->key));
        }

        self::$handled[] = sprintf('%s:%s', $this->package, $this->key);
        self::$handledContexts[] = [
            'reference' => sprintf('%s:%s', $this->package, $this->key),
            'allow_migrations' => $context->allowMigrations,
            'refresh_published_resources' => $context->refreshPublishedResources,
        ];
    }

    public static function reset(): void
    {
        self::$handled = [];
        self::$handledContexts = [];
    }

    /**
     * @return array<int, string>
     */
    public static function handled(): array
    {
        return self::$handled;
    }

    /**
     * @return array<int, array{reference: string, allow_migrations: bool, refresh_published_resources: bool}>
     */
    public static function handledContexts(): array
    {
        return self::$handledContexts;
    }
}
