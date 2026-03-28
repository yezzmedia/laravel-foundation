<?php

declare(strict_types=1);

use YezzMedia\Foundation\Data\RateLimitDefinition;
use YezzMedia\Foundation\Exceptions\InvalidPackageDefinitionException;
use YezzMedia\Foundation\Support\RateLimitKeyFactory;

it('builds ip user rate limit keys', function (): void {
    $definition = new RateLimitDefinition(
        key: 'ops.login',
        package: 'yezzmedia/laravel-ops',
        description: 'Protect admin logins.',
        maxAttempts: 5,
        decaySeconds: 60,
        scope: 'ip_user',
        keyStrategy: 'ip-and-user',
    );

    $key = (new RateLimitKeyFactory)->make($definition, [
        'ip' => '203.0.113.42',
        'user' => 'admin@example.com',
    ]);

    expect($key)->toBe('ops.login:ip_user:203.0.113.42:admin@example.com');
});

it('builds ip scoped rate limit keys', function (): void {
    $definition = new RateLimitDefinition(
        key: 'forms.submit',
        package: 'yezzmedia/laravel-forms',
        description: 'Protect public form submissions.',
        maxAttempts: 10,
        decaySeconds: 60,
        scope: 'ip',
        keyStrategy: 'ip-only',
    );

    $key = (new RateLimitKeyFactory)->make($definition, [
        'ip' => '203.0.113.42',
    ]);

    expect($key)->toBe('forms.submit:ip:203.0.113.42');
});

it('builds user scoped rate limit keys', function (): void {
    $definition = new RateLimitDefinition(
        key: 'usercenter.privacy.export',
        package: 'yezzmedia/laravel-account',
        description: 'Protect privacy exports.',
        maxAttempts: 3,
        decaySeconds: 300,
        scope: 'user',
        keyStrategy: 'user-only',
    );

    $key = (new RateLimitKeyFactory)->make($definition, [
        'user' => '42',
    ]);

    expect($key)->toBe('usercenter.privacy.export:user:42');
});

it('builds custom rate limit keys from segments', function (): void {
    $definition = new RateLimitDefinition(
        key: 'forms.submit',
        package: 'yezzmedia/laravel-forms',
        description: 'Protect public form submissions.',
        maxAttempts: 10,
        decaySeconds: 60,
        scope: 'custom',
        keyStrategy: 'route-and-locale',
    );

    $key = (new RateLimitKeyFactory)->make($definition, [
        'segments' => ['contact', 'de'],
    ]);

    expect($key)->toBe('forms.submit:custom:contact:de');
});

it('builds custom rate limit keys from a custom context value', function (): void {
    $definition = new RateLimitDefinition(
        key: 'forms.submit',
        package: 'yezzmedia/laravel-forms',
        description: 'Protect public form submissions.',
        maxAttempts: 10,
        decaySeconds: 60,
        scope: 'custom',
        keyStrategy: 'custom',
    );

    $key = (new RateLimitKeyFactory)->make($definition, [
        'custom' => 'contact:de',
    ]);

    expect($key)->toBe('forms.submit:custom:contact%3Ade');
});

it('escapes separator characters in dynamic rate limit segments', function (): void {
    $definition = new RateLimitDefinition(
        key: 'ops:login',
        package: 'yezzmedia/laravel-ops',
        description: 'Protect admin logins.',
        maxAttempts: 5,
        decaySeconds: 60,
        scope: 'ip_user',
        keyStrategy: 'ip-and-user',
    );

    $key = (new RateLimitKeyFactory)->make($definition, [
        'ip' => '203.0.113.42:edge',
        'user' => 'admin%example.com',
    ]);

    expect($key)->toBe('ops%3Alogin:ip_user:203.0.113.42%3Aedge:admin%25example.com');
});

it('rejects missing required rate limit context', function (): void {
    $definition = new RateLimitDefinition(
        key: 'ops.login',
        package: 'yezzmedia/laravel-ops',
        description: 'Protect admin logins.',
        maxAttempts: 5,
        decaySeconds: 60,
        scope: 'ip',
        keyStrategy: 'ip-only',
    );

    (new RateLimitKeyFactory)->make($definition, []);
})->throws(InvalidPackageDefinitionException::class);

it('rejects empty required rate limit context values', function (string $scope, array $context): void {
    $definition = new RateLimitDefinition(
        key: 'ops.login',
        package: 'yezzmedia/laravel-ops',
        description: 'Protect admin logins.',
        maxAttempts: 5,
        decaySeconds: 60,
        scope: $scope,
        keyStrategy: 'test',
    );

    (new RateLimitKeyFactory)->make($definition, $context);
})->with([
    ['ip', ['ip' => '   ']],
    ['user', ['user' => '   ']],
    ['custom', ['custom' => '   ']],
])->throws(InvalidPackageDefinitionException::class);

it('rejects empty custom segments', function (): void {
    $definition = new RateLimitDefinition(
        key: 'forms.submit',
        package: 'yezzmedia/laravel-forms',
        description: 'Protect public form submissions.',
        maxAttempts: 10,
        decaySeconds: 60,
        scope: 'custom',
        keyStrategy: 'segments',
    );

    (new RateLimitKeyFactory)->make($definition, [
        'segments' => [' ', ''],
    ]);
})->throws(InvalidPackageDefinitionException::class);

it('rejects unsupported scopes at runtime', function (): void {
    $definition = new RateLimitDefinition(
        key: 'ops.login',
        package: 'yezzmedia/laravel-ops',
        description: 'Protect admin logins.',
        maxAttempts: 5,
        decaySeconds: 60,
        scope: 'tenant',
        keyStrategy: 'tenant-only',
    );

    (new RateLimitKeyFactory)->make($definition, ['custom' => 'tenant-1']);
})->throws(InvalidPackageDefinitionException::class);
