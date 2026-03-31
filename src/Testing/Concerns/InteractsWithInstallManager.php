<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Testing\Concerns;

use PHPUnit\Framework\Assert;
use YezzMedia\Foundation\Data\InstallContext;
use YezzMedia\Foundation\Data\InstallResult;
use YezzMedia\Foundation\Install\InstallManager;

trait InteractsWithInstallManager
{
    public function installManager(): InstallManager
    {
        return app(InstallManager::class);
    }

    /**
     * @param  array<int, string>|null  $only
     */
    public function runInstall(?array $only = null, ?InstallContext $context = null): InstallResult
    {
        return $this->installManager()->run($only, $context);
    }

    public function assertInstallStatus(InstallResult $result, string $status): void
    {
        Assert::assertSame($status, $result->status);
    }

    public function assertExecutedInstallStep(InstallResult $result, string $package, string $step): void
    {
        Assert::assertContains([
            'package' => $package,
            'step' => $step,
        ], $result->executedSteps);
    }
}
