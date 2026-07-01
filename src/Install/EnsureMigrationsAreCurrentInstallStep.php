<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Install;

use Illuminate\Support\Facades\Artisan;
use YezzMedia\Foundation\Data\InstallContext;

use function sprintf;

final class EnsureMigrationsAreCurrentInstallStep implements InstallStep, OptionalInstallStep
{
    public function key(): string
    {
        return 'ensure_migrations_are_current';
    }

    public function package(): string
    {
        return 'yezzmedia/laravel-foundation';
    }

    public function priority(): int
    {
        return 90;
    }

    public function shouldRun(InstallContext $context): bool
    {
        return $context->checkMigrations && $this->hasPendingMigrations();
    }

    public function handle(InstallContext $context): void
    {
        if ($context->allowMigrations) {
            try {
                Artisan::call('migrate', ['--force' => true]);
            } catch (\Throwable $e) {
                fwrite(
                    STDERR,
                    sprintf(
                        "\n  \033[31;1mERROR\033[39;22m  Migration failed: %s\n\n",
                        $e->getMessage()
                    )
                );

                throw $e;
            }

            if ($this->hasPendingMigrations()) {
                fwrite(
                    STDERR,
                    sprintf(
                        "\n  \033[33;1mWARNING\033[39;22m  Some migrations could not be applied. Check your database configuration.\n\n"
                    )
                );
            }

            return;
        }

        fwrite(
            STDERR,
            sprintf(
                "\n  \033[33;1mWARNING\033[39;22m  Pending migrations detected but [--migrate] was not passed.\n".
                "         Run the following command to apply them:\n".
                "           php artisan website:install --migrate\n\n"
            )
        );
    }

    public function isOptional(): bool
    {
        return true;
    }

    private function hasPendingMigrations(): bool
    {
        try {
            $files = app('migrator')->getMigrationFiles(app()->databasePath('migrations'));

            $ran = app('migrator')->getRepository()->getRan();

            $pending = array_values(array_diff(array_keys($files), $ran));

            return $pending !== [];
        } catch (\Throwable) {
            return false;
        }
    }
}
