<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Install;

use RuntimeException;
use YezzMedia\Foundation\Data\InstallContext;

final class ConfigureHttpMiddlewareBridgeInstallStep implements InstallStep, OptionalInstallStep
{
    public function __construct(private readonly ?string $bootstrapPath = null) {}

    public function key(): string
    {
        return 'configure_http_middleware_bridge';
    }

    public function package(): string
    {
        return 'yezzmedia/laravel-foundation';
    }

    public function priority(): int
    {
        return 5;
    }

    public function shouldRun(InstallContext $context): bool
    {
        return $context->configureHttpMiddlewareBridge;
    }

    public function handle(InstallContext $context): void
    {
        $path = $this->bootstrapPath ?? base_path('bootstrap/app.php');

        if (! is_file($path) || ! is_readable($path)) {
            throw new RuntimeException('The host bootstrap/app.php file could not be read for HTTP middleware bridge configuration.');
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new RuntimeException('The host bootstrap/app.php file could not be loaded for HTTP middleware bridge configuration.');
        }

        $bridgeCall = 'FoundationHttpMiddlewareBridge::apply($middleware);';

        if (str_contains($contents, $bridgeCall)) {
            return;
        }

        $endOfLine = str_contains($contents, "\r\n") ? "\r\n" : "\n";
        $injectedLine = '        \\YezzMedia\\Foundation\\Support\\FoundationHttpMiddlewareBridge::apply($middleware);';
        $pattern = '/(->withMiddleware\(function \(Middleware \$middleware\): void \{\R)(.*?)(^\s*\}\))/ms';

        $updated = preg_replace_callback($pattern, static function (array $matches) use ($endOfLine, $injectedLine): string {
            $body = rtrim($matches[2], "\r\n");

            if ($body === '') {
                $body = $injectedLine;
            } else {
                $body .= $endOfLine.$injectedLine;
            }

            return $matches[1].$body.$endOfLine.$matches[3];
        }, $contents, 1);

        if ($updated === null || $updated === $contents) {
            throw new RuntimeException('The host bootstrap/app.php file does not match the expected Laravel 13 withMiddleware bootstrap pattern. Please add the foundation bridge manually.');
        }

        if (! is_writable($path)) {
            throw new RuntimeException('The host bootstrap/app.php file is not writable for HTTP middleware bridge configuration.');
        }

        if (file_put_contents($path, $updated) === false) {
            throw new RuntimeException('The host bootstrap/app.php file could not be updated for HTTP middleware bridge configuration.');
        }
    }

    public function isOptional(): bool
    {
        return true;
    }
}
