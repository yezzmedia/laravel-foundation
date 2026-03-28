<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Console;

use Illuminate\Console\Command;
use YezzMedia\Foundation\Data\DoctorResult;
use YezzMedia\Foundation\Doctor\DoctorManager;

class WebsiteDoctorCommand extends Command
{
    protected $signature = 'website:doctor';

    protected $description = 'Run declared platform doctor checks';

    public function handle(DoctorManager $doctorManager): int
    {
        $results = $doctorManager->run();

        if ($results->isEmpty()) {
            $this->info('No doctor checks registered.');

            return self::SUCCESS;
        }

        $this->table(
            ['Check', 'Package', 'Status', 'Blocking', 'Message'],
            $results->map(static fn (DoctorResult $result): array => [
                $result->key,
                $result->package,
                $result->status,
                $result->isBlocking ? 'yes' : 'no',
                $result->message,
            ])->all(),
        );

        $this->line(sprintf(
            'Summary: passed=%d warning=%d failed=%d skipped=%d',
            $results->where('status', 'passed')->count(),
            $results->where('status', 'warning')->count(),
            $results->where('status', 'failed')->count(),
            $results->where('status', 'skipped')->count(),
        ));

        return $results->contains(
            static fn (DoctorResult $result): bool => $result->status === 'failed' && $result->isBlocking,
        ) ? self::FAILURE : self::SUCCESS;
    }
}
