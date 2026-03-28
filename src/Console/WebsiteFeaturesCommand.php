<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Console;

use Illuminate\Console\Command;
use YezzMedia\Foundation\Data\FeatureDefinition;
use YezzMedia\Foundation\Registry\FeatureRegistry;

class WebsiteFeaturesCommand extends Command
{
    protected $signature = 'website:features';

    protected $description = 'List declared platform features';

    public function handle(FeatureRegistry $featureRegistry): int
    {
        $features = $featureRegistry->all()->sortBy('name')->values();

        if ($features->isEmpty()) {
            $this->info('No features registered.');

            return self::SUCCESS;
        }

        $this->table(
            ['Feature', 'Package', 'Label'],
            $features->map(static fn (FeatureDefinition $feature): array => [
                $feature->name,
                $feature->package,
                $feature->label,
            ])->all(),
        );

        return self::SUCCESS;
    }
}
