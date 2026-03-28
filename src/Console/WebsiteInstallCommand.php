<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Console;

use Illuminate\Console\Command;
use YezzMedia\Foundation\Install\InstallManager;

class WebsiteInstallCommand extends Command
{
    protected $signature = 'website:install {--only=* : Run install steps for specific packages}';

    protected $description = 'Run declared platform install steps';

    public function handle(InstallManager $installManager): int
    {
        $onlyOption = $this->option('only');
        $only = array_values(array_filter(
            is_array($onlyOption) ? $onlyOption : [],
            static fn (mixed $value): bool => is_string($value) && trim($value) !== '',
        ));

        $result = $installManager->run($only === [] ? null : $only);

        $this->line(sprintf('Status: %s', $result->status));

        foreach ($result->messages as $message) {
            $this->line($message);
        }

        if ($result->executedSteps !== []) {
            $this->table(['Package', 'Step'], $result->executedSteps);
        }

        if ($result->failedSteps !== []) {
            $this->table(['Failed Package', 'Failed Step'], $result->failedSteps);
        }

        if (($result->context['requested_packages'] ?? null) !== null) {
            $this->line('Requested packages: '.implode(', ', $result->context['requested_packages']));
        }

        if (($result->context['skipped_steps'] ?? null) !== null) {
            $this->table(['Skipped Package', 'Skipped Step'], $result->context['skipped_steps']);
        }

        return $result->status === 'failed' ? self::FAILURE : self::SUCCESS;
    }
}
