<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Install;

use Throwable;
use YezzMedia\Foundation\Contracts\DefinesInstallSteps;
use YezzMedia\Foundation\Contracts\PlatformPackage;
use YezzMedia\Foundation\Data\InstallContext;
use YezzMedia\Foundation\Data\InstallResult;
use YezzMedia\Foundation\Events\WebsiteInstalled;
use YezzMedia\Foundation\Exceptions\InstallationFailedException;
use YezzMedia\Foundation\Exceptions\InvalidPackageDefinitionException;
use YezzMedia\Foundation\Support\PackageManifestLoader;

/**
 * Orchestrates explicit install steps declared by registered platform packages.
 */
class InstallManager
{
    public function __construct(private readonly PackageManifestLoader $manifestLoader) {}

    /**
     * @param  array<int, string>|null  $only
     */
    public function run(?array $only = null, ?InstallContext $context = null): InstallResult
    {
        $context ??= new InstallContext;
        $steps = $this->steps($only);
        $executedSteps = [];
        $failedSteps = [];
        $messages = [];
        $skippedSteps = [];

        foreach ($steps as $step) {
            if (! $step->shouldRun($context)) {
                if ($step instanceof OptionalInstallStep && $step->isOptional()) {
                    continue;
                }

                $skippedSteps[] = $this->stepReference($step);
                $messages[] = sprintf('Skipped install step [%s] for package [%s].', $step->key(), $step->package());

                continue;
            }

            try {
                $step->handle($context);
            } catch (Throwable $throwable) {
                // Foundation stops at the first blocking failure so downstream setup
                // does not run against a partially initialized package state.
                $failedSteps[] = $this->stepReference($step);
                $messages[] = InstallationFailedException::forStep($step->package(), $step->key(), $throwable)->getMessage();

                return new InstallResult(
                    status: 'failed',
                    executedSteps: $executedSteps,
                    failedSteps: $failedSteps,
                    messages: $messages,
                    context: $this->resultContext($only, $skippedSteps, $context),
                );
            }

            $executedSteps[] = $this->stepReference($step);
            $messages[] = sprintf('Executed install step [%s] for package [%s].', $step->key(), $step->package());
        }

        $result = new InstallResult(
            status: $this->determineStatus($only, $skippedSteps),
            executedSteps: $executedSteps,
            failedSteps: $failedSteps,
            messages: $messages === [] ? ['No install steps were available.'] : $messages,
            context: $this->resultContext($only, $skippedSteps, $context),
        );

        if ($result->status === 'success') {
            event(new WebsiteInstalled(
                status: $result->status,
                executedStepCount: count($result->executedSteps),
                failedStepCount: count($result->failedSteps),
                context: $result->context,
            ));
        }

        return $result;
    }

    /**
     * @return array<int, InstallStep>
     */
    public function stepsFor(string $package): array
    {
        return $this->steps([$package]);
    }

    /**
     * @param  array<int, string>|null  $only
     * @return array<int, InstallStep>
     */
    private function steps(?array $only = null): array
    {
        $steps = [];

        foreach ($this->manifestLoader->packages() as $platformPackage) {
            if ($only !== null && ! in_array($platformPackage->metadata()->name, $only, true)) {
                continue;
            }

            if (! $platformPackage instanceof DefinesInstallSteps) {
                continue;
            }

            foreach ($platformPackage->installSteps() as $step) {
                $this->ensureValidStep($platformPackage, $step);
                $steps[] = $step;
            }
        }

        usort($steps, function (InstallStep $left, InstallStep $right): int {
            $priorityComparison = $left->priority() <=> $right->priority();

            if ($priorityComparison !== 0) {
                return $priorityComparison;
            }

            $packageComparison = $left->package() <=> $right->package();

            if ($packageComparison !== 0) {
                return $packageComparison;
            }

            return $left->key() <=> $right->key();
        });

        return $steps;
    }

    /**
     * @param  array<int, string>|null  $only
     * @param  array<int, array{package: string, step: string}>  $skippedSteps
     */
    private function determineStatus(?array $only, array $skippedSteps): string
    {
        // Filtered or skipped runs are intentionally partial even when nothing fails.
        if ($only !== null || $skippedSteps !== []) {
            return 'partial';
        }

        return 'success';
    }

    private function ensureValidStep(PlatformPackage $platformPackage, InstallStep $step): void
    {
        if ($step->key() === '') {
            throw new InvalidPackageDefinitionException('Install step key must not be empty.');
        }

        if ($step->package() !== $platformPackage->metadata()->name) {
            throw new InvalidPackageDefinitionException(sprintf(
                'Install step [%s] must belong to package [%s].',
                $step->key(),
                $platformPackage->metadata()->name,
            ));
        }
    }

    /**
     * @return array{package: string, step: string}
     */
    private function stepReference(InstallStep $step): array
    {
        return [
            'package' => $step->package(),
            'step' => $step->key(),
        ];
    }

    /**
     * @param  array<int, string>|null  $only
     * @param  array<int, array{package: string, step: string}>  $skippedSteps
     * @return array<string, mixed>|null
     */
    private function resultContext(?array $only, array $skippedSteps, InstallContext $installContext): ?array
    {
        $context = [];

        if ($only !== null) {
            $context['requested_packages'] = array_values($only);
        }

        if ($skippedSteps !== []) {
            $context['skipped_steps'] = $skippedSteps;
        }

        if ($installContext->allowMigrations) {
            $context['allow_migrations'] = true;
        }

        if ($installContext->refreshPublishedResources) {
            $context['refresh_published_resources'] = true;
        }

        if ($installContext->configureAccessAudit) {
            $context['configure_access_audit'] = true;
        }

        return $context === [] ? null : $context;
    }
}
