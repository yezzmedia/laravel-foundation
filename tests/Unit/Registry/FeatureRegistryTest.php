<?php

declare(strict_types=1);

use YezzMedia\Foundation\Data\FeatureDefinition;
use YezzMedia\Foundation\Exceptions\InvalidPackageDefinitionException;
use YezzMedia\Foundation\Registry\FeatureRegistry;

it('stores features and supports lookup helpers', function (): void {
    $registry = new FeatureRegistry;

    $registry->register(new FeatureDefinition('content.pages', 'yezzmedia/laravel-content', 'Content pages'));
    $registry->register(new FeatureDefinition('ops.audit', 'yezzmedia/laravel-ops', 'Audit'));

    expect($registry->all())->toHaveCount(2)
        ->and($registry->has('content.pages'))->toBeTrue()
        ->and($registry->forPackage('yezzmedia/laravel-content')->pluck('name')->all())->toBe([
            'content.pages',
        ])
        ->and($registry->forPackage('yezzmedia/laravel-missing'))->toHaveCount(0);
});

it('rejects duplicate feature names', function (): void {
    $registry = new FeatureRegistry;
    $feature = new FeatureDefinition('content.pages', 'yezzmedia/laravel-content', 'Content pages');

    $registry->register($feature);
    $registry->register($feature);
})->throws(InvalidPackageDefinitionException::class);

it('rejects empty feature names', function (): void {
    (new FeatureRegistry)->register(new FeatureDefinition('', 'yezzmedia/laravel-content', 'Content pages'));
})->throws(InvalidPackageDefinitionException::class);

it('rejects registration after the feature registry is sealed', function (): void {
    $registry = new FeatureRegistry;

    $registry->seal();

    $registry->register(new FeatureDefinition('content.pages', 'yezzmedia/laravel-content', 'Content pages'));
})->throws(InvalidPackageDefinitionException::class, 'Feature registry is sealed.');
