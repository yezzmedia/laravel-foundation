<?php

declare(strict_types=1);

use Tests\Fixtures\FakePlatformPackage;
use YezzMedia\Foundation\Data\PackageMetadata;
use YezzMedia\Foundation\Exceptions\InvalidPackageDefinitionException;
use YezzMedia\Foundation\Registry\PackageRegistry;

it('stores packages and supports lookup helpers', function (): void {
    $registry = new PackageRegistry;

    $registry->register(new PackageMetadata(
        name: 'yezzmedia/laravel-content',
        vendor: 'yezzmedia',
        description: 'Content package.',
        packageClass: FakePlatformPackage::class,
    ));

    expect($registry->all())->toHaveCount(1)
        ->and($registry->has('yezzmedia/laravel-content'))->toBeTrue()
        ->and($registry->find('yezzmedia/laravel-content')?->description)->toBe('Content package.')
        ->and($registry->find('yezzmedia/laravel-missing'))->toBeNull();
});

it('rejects duplicate package names', function (): void {
    $registry = new PackageRegistry;
    $package = new PackageMetadata(
        name: 'yezzmedia/laravel-content',
        vendor: 'yezzmedia',
        description: 'Content package.',
        packageClass: FakePlatformPackage::class,
    );

    $registry->register($package);
    $registry->register($package);
})->throws(InvalidPackageDefinitionException::class);

it('rejects empty package names', function (): void {
    (new PackageRegistry)->register(new PackageMetadata(
        name: '',
        vendor: 'yezzmedia',
        description: 'Invalid package.',
        packageClass: FakePlatformPackage::class,
    ));
})->throws(InvalidPackageDefinitionException::class);
