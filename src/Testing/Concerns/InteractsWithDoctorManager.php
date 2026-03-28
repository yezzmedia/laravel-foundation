<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Testing\Concerns;

use Illuminate\Support\Collection;
use PHPUnit\Framework\Assert;
use YezzMedia\Foundation\Data\DoctorResult;
use YezzMedia\Foundation\Doctor\DoctorManager;

trait InteractsWithDoctorManager
{
    public function doctorManager(): DoctorManager
    {
        return app(DoctorManager::class);
    }

    /**
     * @return Collection<int, DoctorResult>
     */
    public function runDoctor(): Collection
    {
        return $this->doctorManager()->run();
    }

    /**
     * @return Collection<int, DoctorResult>
     */
    public function failingDoctorResults(): Collection
    {
        return $this->doctorManager()->failing();
    }

    /**
     * @param  Collection<int, DoctorResult>  $results
     */
    public function assertDoctorResult(Collection $results, string $key, string $status): void
    {
        $result = $results->first(static fn (DoctorResult $doctorResult): bool => $doctorResult->key === $key);

        Assert::assertInstanceOf(DoctorResult::class, $result);
        Assert::assertSame($status, $result->status);
    }
}
