<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Doctor;

use YezzMedia\Foundation\Data\DoctorResult;

final readonly class FoundationHttpMiddlewareBridgeConfiguredCheck implements DoctorCheck
{
    private const KEY = 'foundation_http_middleware_bridge_configured';

    private const PACKAGE = 'yezzmedia/laravel-foundation';

    public function __construct(private ?string $bootstrapPath = null) {}

    public function key(): string
    {
        return self::KEY;
    }

    public function package(): string
    {
        return self::PACKAGE;
    }

    public function run(): DoctorResult
    {
        $path = $this->bootstrapPath ?? base_path('bootstrap/app.php');

        if (! is_file($path) || ! is_readable($path)) {
            return $this->result(
                status: 'skipped',
                message: 'Foundation HTTP middleware bridge could not be verified because bootstrap/app.php is not available in this runtime.',
                context: [
                    'bootstrap_path' => $path,
                    'configured' => false,
                ],
            );
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            return $this->result(
                status: 'warning',
                message: 'Foundation HTTP middleware bridge could not be verified because bootstrap/app.php could not be read.',
                context: [
                    'bootstrap_path' => $path,
                    'configured' => false,
                ],
            );
        }

        $configured = str_contains($contents, 'FoundationHttpMiddlewareBridge::apply($middleware);');

        if ($configured) {
            return $this->result(
                status: 'passed',
                message: 'Foundation HTTP middleware bridge is configured in bootstrap/app.php.',
                context: [
                    'bootstrap_path' => $path,
                    'configured' => true,
                ],
            );
        }

        return $this->result(
            status: 'warning',
            message: 'Foundation HTTP middleware bridge is not configured in bootstrap/app.php.',
            context: [
                'bootstrap_path' => $path,
                'configured' => false,
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function result(string $status, string $message, array $context): DoctorResult
    {
        return new DoctorResult(
            key: $this->key(),
            package: $this->package(),
            status: $status,
            message: $message,
            isBlocking: false,
            context: $context,
        );
    }
}
