<?php

declare(strict_types=1);

use YezzMedia\Foundation\Data\OpsModuleDefinition;
use YezzMedia\Foundation\Exceptions\InvalidPackageDefinitionException;
use YezzMedia\Foundation\Registry\OpsModuleRegistry;

it('stores ops modules and filters them by package', function (): void {
    $registry = new OpsModuleRegistry;

    $registry->register(new OpsModuleDefinition('redirects.loop-report', 'yezzmedia/laravel-ops', 'Loop report', 'page'));
    $registry->register(new OpsModuleDefinition('content.health', 'yezzmedia/laravel-content', 'Content health', 'widget'));

    expect($registry->all())->toHaveCount(2)
        ->and($registry->forPackage('yezzmedia/laravel-ops')->pluck('key')->all())->toBe([
            'redirects.loop-report',
        ]);
});

it('rejects duplicate ops module keys', function (): void {
    $registry = new OpsModuleRegistry;
    $module = new OpsModuleDefinition('redirects.loop-report', 'yezzmedia/laravel-ops', 'Loop report', 'page');

    $registry->register($module);
    $registry->register($module);
})->throws(InvalidPackageDefinitionException::class);

it('rejects empty ops module keys', function (): void {
    (new OpsModuleRegistry)->register(new OpsModuleDefinition('', 'yezzmedia/laravel-ops', 'Loop report', 'page'));
})->throws(InvalidPackageDefinitionException::class);

it('returns an empty collection for unknown packages', function (): void {
    $registry = new OpsModuleRegistry;

    expect($registry->forPackage('yezzmedia/laravel-missing'))->toHaveCount(0);
});
