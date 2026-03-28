<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Doctor;

use Illuminate\Support\Collection;
use YezzMedia\Foundation\Contracts\PlatformPackage;
use YezzMedia\Foundation\Contracts\ProvidesDoctorChecks;
use YezzMedia\Foundation\Data\DoctorResult;
use YezzMedia\Foundation\Events\DoctorChecksCompleted;
use YezzMedia\Foundation\Exceptions\InvalidPackageDefinitionException;
use YezzMedia\Foundation\Support\PackageManifestLoader;

class DoctorManager
{
    /**
     * @var array<int, string>
     */
    private const ALLOWED_STATUSES = [
        'passed',
        'warning',
        'failed',
        'skipped',
    ];

    public function __construct(private readonly PackageManifestLoader $manifestLoader) {}

    /**
     * @return Collection<int, DoctorResult>
     */
    public function run(): Collection
    {
        $results = $this->results();

        event(new DoctorChecksCompleted($results, $this->summary($results)));

        return $results;
    }

    /**
     * @return Collection<int, DoctorResult>
     */
    public function failing(): Collection
    {
        return $this->results()
            ->filter(static fn (DoctorResult $result): bool => $result->status === 'failed' && $result->isBlocking)
            ->values();
    }

    /**
     * @return Collection<int, DoctorResult>
     */
    private function results(): Collection
    {
        return collect($this->checks())
            ->map(function (DoctorCheck $check): DoctorResult {
                $result = $check->run();

                $this->ensureValidResult($check, $result);

                return $result;
            })
            ->values();
    }

    /**
     * @return array<int, DoctorCheck>
     */
    private function checks(): array
    {
        $checks = [];

        foreach ($this->manifestLoader->packages() as $platformPackage) {
            if (! $platformPackage instanceof ProvidesDoctorChecks) {
                continue;
            }

            foreach ($platformPackage->doctorChecks() as $check) {
                $this->ensureValidCheck($platformPackage, $check);
                $checks[] = $check;
            }
        }

        usort($checks, function (DoctorCheck $left, DoctorCheck $right): int {
            $packageComparison = $left->package() <=> $right->package();

            if ($packageComparison !== 0) {
                return $packageComparison;
            }

            return $left->key() <=> $right->key();
        });

        return $checks;
    }

    private function ensureValidCheck(PlatformPackage $platformPackage, DoctorCheck $check): void
    {
        if ($check->key() === '') {
            throw new InvalidPackageDefinitionException('Doctor check key must not be empty.');
        }

        if ($check->package() !== $platformPackage->metadata()->name) {
            throw new InvalidPackageDefinitionException(sprintf(
                'Doctor check [%s] must belong to package [%s].',
                $check->key(),
                $platformPackage->metadata()->name,
            ));
        }
    }

    private function ensureValidResult(DoctorCheck $check, DoctorResult $result): void
    {
        if ($result->key !== $check->key()) {
            throw new InvalidPackageDefinitionException(sprintf(
                'Doctor result [%s] must match check key [%s].',
                $result->key,
                $check->key(),
            ));
        }

        if ($result->package !== $check->package()) {
            throw new InvalidPackageDefinitionException(sprintf(
                'Doctor result [%s] must belong to package [%s].',
                $result->key,
                $check->package(),
            ));
        }

        if (! in_array($result->status, self::ALLOWED_STATUSES, true)) {
            throw new InvalidPackageDefinitionException(sprintf(
                'Doctor result [%s] has invalid status [%s].',
                $result->key,
                $result->status,
            ));
        }
    }

    /**
     * @param  Collection<int, DoctorResult>  $results
     * @return array{passed: int, warning: int, failed: int, skipped: int}
     */
    private function summary(Collection $results): array
    {
        return [
            'passed' => $results->where('status', 'passed')->count(),
            'warning' => $results->where('status', 'warning')->count(),
            'failed' => $results->where('status', 'failed')->count(),
            'skipped' => $results->where('status', 'skipped')->count(),
        ];
    }
}
