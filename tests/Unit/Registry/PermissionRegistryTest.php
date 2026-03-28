<?php

declare(strict_types=1);

use YezzMedia\Foundation\Data\PermissionDefinition;
use YezzMedia\Foundation\Exceptions\InvalidPackageDefinitionException;
use YezzMedia\Foundation\Registry\PermissionRegistry;

it('stores permissions and filters them by package', function (): void {
    $registry = new PermissionRegistry;

    $registry->register(new PermissionDefinition('content.pages.publish', 'yezzmedia/laravel-content', 'Publish pages'));
    $registry->register(new PermissionDefinition('ops.audit.view', 'yezzmedia/laravel-ops', 'View audit'));

    expect($registry->all())->toHaveCount(2)
        ->and($registry->forPackage('yezzmedia/laravel-content')->pluck('name')->all())->toBe([
            'content.pages.publish',
        ]);
});

it('rejects duplicate permission names', function (): void {
    $registry = new PermissionRegistry;
    $permission = new PermissionDefinition('content.pages.publish', 'yezzmedia/laravel-content', 'Publish pages');

    $registry->register($permission);
    $registry->register($permission);
})->throws(InvalidPackageDefinitionException::class);

it('rejects empty permission names', function (): void {
    (new PermissionRegistry)->register(new PermissionDefinition('', 'yezzmedia/laravel-content', 'Publish pages'));
})->throws(InvalidPackageDefinitionException::class);

it('returns an empty collection for unknown packages', function (): void {
    $registry = new PermissionRegistry;

    expect($registry->forPackage('yezzmedia/laravel-missing'))->toHaveCount(0);
});

it('rejects registration after the permission registry is sealed', function (): void {
    $registry = new PermissionRegistry;

    $registry->seal();

    $registry->register(new PermissionDefinition('content.pages.publish', 'yezzmedia/laravel-content', 'Publish pages'));
})->throws(InvalidPackageDefinitionException::class, 'Permission registry is sealed.');
