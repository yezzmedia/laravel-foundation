<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Console;

use Illuminate\Console\Command;
use YezzMedia\Foundation\Data\PackageMetadata;
use YezzMedia\Foundation\Registry\PackageRegistry;

class WebsitePackagesCommand extends Command
{
    protected $signature = 'website:packages';

    protected $description = 'List registered platform packages';

    public function handle(PackageRegistry $packageRegistry): int
    {
        $packages = $packageRegistry->all()->sortBy('name')->values();

        if ($packages->isEmpty()) {
            $this->info('No packages registered.');

            return self::SUCCESS;
        }

        $this->table(
            ['Package', 'Vendor', 'Enabled', 'Priority'],
            $packages->map(static fn (PackageMetadata $package): array => [
                $package->name,
                $package->vendor,
                $package->enabled ? 'yes' : 'no',
                $package->priority ?? '-',
            ])->all(),
        );

        return self::SUCCESS;
    }
}
