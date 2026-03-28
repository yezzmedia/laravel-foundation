<?php

declare(strict_types=1);

use Tests\Fixtures\FakePlatformPackage;
use Tests\SealedFoundationTestCase;
use YezzMedia\Foundation\Exceptions\InvalidPackageDefinitionException;
use YezzMedia\Foundation\Support\PlatformPackageRegistrar;

uses(SealedFoundationTestCase::class);

it('rejects package registration after the foundation state has been sealed', function (): void {
    app(PlatformPackageRegistrar::class)->register(new FakePlatformPackage);
})->throws(InvalidPackageDefinitionException::class, 'Package registry is sealed.');
