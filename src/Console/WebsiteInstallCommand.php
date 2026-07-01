<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Console;

use Illuminate\Console\Command;
use YezzMedia\Foundation\Data\InstallContext;
use YezzMedia\Foundation\Install\InstallManager;

use function implode;

class WebsiteInstallCommand extends Command
{
    protected $signature = 'website:install {--only=* : Run install steps for specific packages} {--migrate : Allow install steps to run required migrations} {--refresh-publish : Allow install steps to refresh already published resources} {--configure-audit : Configure audit persistence for selected packages} {--audit-package=* : Configure audit persistence for specific packages or [all]} {--configure-access-audit : Deprecated alias for configuring access audit persistence} {--configure-http-middleware-bridge : Configure the generic foundation HTTP middleware bridge in bootstrap/app.php}';

    protected $description = 'Run declared platform install steps';

    public function handle(InstallManager $installManager): int
    {
        $onlyOption = $this->option('only');
        $only = array_values(array_filter(
            is_array($onlyOption) ? $onlyOption : [],
            static fn (mixed $value): bool => is_string($value) && trim($value) !== '',
        ));
        $configureAudit = (bool) $this->option('configure-audit');
        $configureAccessAudit = (bool) $this->option('configure-access-audit');
        $selectedAuditPackages = $this->resolveAuditPackages($installManager, $configureAudit, $configureAccessAudit);

        if ($selectedAuditPackages === null) {
            return self::FAILURE;
        }

        if (($configureAudit || $configureAccessAudit) && $only !== []) {
            $this->error('The [--only] option cannot be combined with audit configuration. Use [--audit-package=*] instead.');

            return self::FAILURE;
        }

        $context = new InstallContext(
            allowMigrations: (bool) $this->option('migrate'),
            refreshPublishedResources: (bool) $this->option('refresh-publish'),
            configureAccessAudit: $configureAccessAudit,
            configureAudit: $configureAudit || $configureAccessAudit,
            configureHttpMiddlewareBridge: (bool) $this->option('configure-http-middleware-bridge'),
            checkFrontendAssets: true,
            checkMigrations: true,
            checkAdminUser: true,
            auditPackages: $selectedAuditPackages,
        );

        $auditPackages = $installManager->auditPackages();
        $result = $context->configuresAudit()
            ? $installManager->runAudit($selectedAuditPackages === $auditPackages ? null : $selectedAuditPackages, $context)
            : $installManager->run($only === [] ? null : $only, $context);

        if ($context->configuresAudit() && $auditPackages === []) {
            $this->warn('No audit-capable packages are currently registered.');
        }

        if ($context->allowMigrations) {
            $this->warn('Migration execution is enabled for this install run.');
        }

        if ($context->refreshPublishedResources) {
            $this->warn('Published resource refresh is enabled for this install run.');
        }

        if ($context->configureHttpMiddlewareBridge) {
            $this->warn('HTTP middleware bridge configuration is enabled for this install run.');
        }

        if ($context->configuresAudit()) {
            $this->warn('Audit persistence configuration is enabled for this install run.');
        }

        if ($context->configureAccessAudit) {
            $this->warn('The [--configure-access-audit] option is deprecated. Use [--configure-audit --audit-package=yezzmedia/laravel-access] instead.');
        }

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

        if (($result->context['audit_packages'] ?? null) !== null) {
            $this->line('Audit packages: '.implode(', ', $result->context['audit_packages']));
        }

        if (($result->context['skipped_steps'] ?? null) !== null) {
            $this->table(['Skipped Package', 'Skipped Step'], $result->context['skipped_steps']);
        }

        return $result->status === 'failed' ? self::FAILURE : self::SUCCESS;
    }

    /**
     * @return array<int, string>|null
     */
    private function resolveAuditPackages(InstallManager $installManager, bool $configureAudit, bool $configureAccessAudit): ?array
    {
        $auditPackageOption = $this->normalizedArrayOption('audit-package');

        if ($auditPackageOption !== [] && ! $configureAudit) {
            $this->error('The [--audit-package] option requires [--configure-audit].');

            return null;
        }

        if (! $configureAudit && ! $configureAccessAudit) {
            return [];
        }

        $availableAuditPackages = $installManager->auditPackages();

        if ($configureAccessAudit && ! in_array('yezzmedia/laravel-access', $availableAuditPackages, true)) {
            $this->error('The deprecated [--configure-access-audit] option requires the installed [yezzmedia/laravel-access] package with audit install support.');

            return null;
        }

        $selectedAuditPackages = $auditPackageOption;

        if ($configureAudit && $selectedAuditPackages === []) {
            if ($availableAuditPackages === []) {
                return [];
            }

            if (! $this->input->isInteractive()) {
                $this->error('The [--configure-audit] option requires [--audit-package=*] in non-interactive mode.');

                return null;
            }

            $selectedAuditPackages = $this->auditPackageSelection($availableAuditPackages);
        }

        if ($configureAccessAudit) {
            $selectedAuditPackages[] = 'yezzmedia/laravel-access';
        }

        $selectedAuditPackages = array_values(array_unique(array_filter(
            $selectedAuditPackages,
            static fn (mixed $package): bool => is_string($package) && $package !== '',
        )));

        if (in_array('all', $selectedAuditPackages, true)) {
            return $availableAuditPackages;
        }

        $invalidAuditPackages = array_values(array_diff($selectedAuditPackages, $availableAuditPackages));

        if ($invalidAuditPackages !== []) {
            $this->error('The following audit packages are not available: '.implode(', ', $invalidAuditPackages));

            return null;
        }

        return $selectedAuditPackages;
    }

    /**
     * @return array<int, string>
     */
    private function auditPackageSelection(array $availableAuditPackages): array
    {
        $selection = $this->choice(
            'Which packages should have audit persistence configured?',
            ['all', ...$availableAuditPackages],
            multiple: true,
        );

        return array_values(array_filter(
            is_array($selection) ? $selection : [$selection],
            static fn (mixed $value): bool => is_string($value) && $value !== '',
        ));
    }

    /**
     * @return array<int, string>
     */
    private function normalizedArrayOption(string $option): array
    {
        $values = $this->option($option);

        return array_values(array_filter(
            is_array($values) ? $values : [],
            static fn (mixed $value): bool => is_string($value) && trim($value) !== '',
        ));
    }
}
