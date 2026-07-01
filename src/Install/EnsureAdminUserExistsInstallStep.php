<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Install;

use YezzMedia\Foundation\Data\InstallContext;

use function sprintf;

final class EnsureAdminUserExistsInstallStep implements InstallStep, OptionalInstallStep
{
    public function key(): string
    {
        return 'ensure_admin_user_exists';
    }

    public function package(): string
    {
        return 'yezzmedia/laravel-foundation';
    }

    public function priority(): int
    {
        return 110;
    }

    public function shouldRun(InstallContext $context): bool
    {
        if (! $context->checkAdminUser) {
            return false;
        }

        if (! $this->usersTableExists()) {
            return false;
        }

        return $this->userCount() === 0;
    }

    public function handle(InstallContext $context): void
    {
        fwrite(
            STDERR,
            sprintf(
                "\n  \033[33;1mWARNING\033[39;22m  No users found in the database. You need to create an account.\n".
                "         Run the following command to create an admin user:\n".
                "           php artisan tinker --execute '\$u = \App\Models\User::create([\"name\" => \"Admin\", \"email\" => \"admin@example.com\", \"password\" => bcrypt(\"password\")]); \$u->markEmailAsVerified();'\n\n"
            )
        );
    }

    public function isOptional(): bool
    {
        return true;
    }

    private function usersTableExists(): bool
    {
        try {
            return app('db')->getSchemaBuilder()->hasTable('users');
        } catch (\Throwable) {
            return false;
        }
    }

    private function userCount(): int
    {
        try {
            $userModel = config('auth.providers.users.model');

            if ($userModel === null || ! class_exists($userModel)) {
                return -1;
            }

            return (int) $userModel::query()->count();
        } catch (\Throwable) {
            return -1;
        }
    }
}
