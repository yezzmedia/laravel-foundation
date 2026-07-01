<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Install;

use YezzMedia\Foundation\Data\InstallContext;

use function sprintf;

final class EnsureTailwindIsBuiltInstallStep implements InstallStep, OptionalInstallStep
{
    public function key(): string
    {
        return 'ensure_tailwind_is_built';
    }

    public function package(): string
    {
        return 'yezzmedia/laravel-foundation';
    }

    public function priority(): int
    {
        return 100;
    }

    public function shouldRun(InstallContext $context): bool
    {
        return $context->checkFrontendAssets && ! file_exists(public_path('build/manifest.json'));
    }

    public function handle(InstallContext $context): void
    {
        fwrite(
            STDERR,
            sprintf(
                "\n  \033[33;1mWARNING\033[39;22m  Frontend assets are not built. CSS and JavaScript will not load.\n".
                "         Run the following commands to build them:\n".
                "           npm install\n".
                "           npm run build\n\n"
            )
        );
    }

    public function isOptional(): bool
    {
        return true;
    }
}
