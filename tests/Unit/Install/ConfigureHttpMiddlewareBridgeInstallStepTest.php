<?php

declare(strict_types=1);

use YezzMedia\Foundation\Data\InstallContext;
use YezzMedia\Foundation\Install\ConfigureHttpMiddlewareBridgeInstallStep;

it('patches the standard laravel 13 middleware bootstrap block', function (): void {
    $path = sys_get_temp_dir().'/foundation-http-middleware-bridge-'.uniqid().'.php';

    file_put_contents($path, <<<'PHP'
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->create();
PHP);

    $step = new ConfigureHttpMiddlewareBridgeInstallStep($path);
    $step->handle(new InstallContext(configureHttpMiddlewareBridge: true));

    $updated = file_get_contents($path);

    expect($updated)->toContain('\\YezzMedia\\Foundation\\Support\\FoundationHttpMiddlewareBridge::apply($middleware);');

    unlink($path);
});

it('is idempotent when the bridge is already configured', function (): void {
    $path = sys_get_temp_dir().'/foundation-http-middleware-bridge-'.uniqid().'.php';

    file_put_contents($path, <<<'PHP'
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withMiddleware(function (Middleware $middleware): void {
        \YezzMedia\Foundation\Support\FoundationHttpMiddlewareBridge::apply($middleware);
    })
    ->create();
PHP);

    $step = new ConfigureHttpMiddlewareBridgeInstallStep($path);
    $before = file_get_contents($path);

    $step->handle(new InstallContext(configureHttpMiddlewareBridge: true));

    expect(file_get_contents($path))->toBe($before);

    unlink($path);
});

it('fails when the bootstrap file does not match the expected middleware pattern', function (): void {
    $path = sys_get_temp_dir().'/foundation-http-middleware-bridge-'.uniqid().'.php';
    file_put_contents($path, '<?php return [];');

    try {
        (new ConfigureHttpMiddlewareBridgeInstallStep($path))
            ->handle(new InstallContext(configureHttpMiddlewareBridge: true));
    } finally {
        unlink($path);
    }
})->throws(RuntimeException::class, 'does not match the expected Laravel 13 withMiddleware bootstrap pattern');
