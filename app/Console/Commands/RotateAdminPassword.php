<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Rotate credentials for accounts still holding a known-default password.
 *
 * AdminSeeder used to hard-code admin@admin.com / 12345678 and runs on every
 * container boot, so any deployment seeded before that was fixed still has a
 * live admin account with publicly documented credentials. Changing the seeder
 * does NOT fix those accounts: the seeder short-circuits when the email already
 * exists, so the weak hash survives every subsequent deploy.
 *
 * This command exists so operators can rotate without hand-writing SQL against
 * production. Run it from the Render shell (or any environment with DB access):
 *
 *     php artisan nexsus:rotate-admin-password --audit
 *     php artisan nexsus:rotate-admin-password
 */
class RotateAdminPassword extends Command
{
    protected $signature = 'nexsus:rotate-admin-password
        {--email= : Account to rotate (default: ADMIN_EMAIL, else admin@admin.com)}
        {--password= : Use this password instead of generating one}
        {--all-weak : Rotate every account whose password matches a known default}
        {--audit : Report which accounts hold a known-default password, change nothing}';

    protected $description = 'Rotate admin passwords that still match a known seeded default';

    /**
     * Defaults this project (and upstream LinkStack) has shipped or documented.
     */
    private const KNOWN_DEFAULTS = ['12345678', 'password', 'admin', 'changeme'];

    public function handle(): int
    {
        if ($this->option('audit')) {
            return $this->audit();
        }

        return $this->option('all-weak') ? $this->rotateAllWeak() : $this->rotateOne();
    }

    private function audit(): int
    {
        $weak = $this->findWeakAccounts();

        if ($weak === []) {
            $this->info('No account is using a known-default password.');

            return self::SUCCESS;
        }

        $this->error('Accounts using a known-default password:');
        foreach ($weak as [$user, $password]) {
            $this->line(sprintf('  %-40s role=%-9s password=%s', $user->email, $user->role, $password));
        }
        $this->newLine();
        $this->warn('Rotate with: php artisan nexsus:rotate-admin-password --all-weak');

        // Non-zero so this can gate a deploy or health check.
        return self::FAILURE;
    }

    private function rotateOne(): int
    {
        $email = $this->option('email') ?: env('ADMIN_EMAIL', 'admin@admin.com');
        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("No user with email {$email}.");
            $this->line('Pass --email=<address>, or --audit to list weak accounts.');

            return self::FAILURE;
        }

        $this->apply($user);

        return self::SUCCESS;
    }

    private function rotateAllWeak(): int
    {
        $weak = $this->findWeakAccounts();

        if ($weak === []) {
            $this->info('No account is using a known-default password. Nothing to do.');

            return self::SUCCESS;
        }

        foreach ($weak as [$user, $_]) {
            $this->apply($user);
        }

        return self::SUCCESS;
    }

    /**
     * @return array<int, array{0: User, 1: string}>
     */
    private function findWeakAccounts(): array
    {
        $weak = [];

        // Chunked: bcrypt verification is deliberately slow, and an instance
        // may have many users.
        User::select('id', 'email', 'password', 'role')->chunkById(100, function ($users) use (&$weak) {
            foreach ($users as $user) {
                foreach (self::KNOWN_DEFAULTS as $candidate) {
                    if ($user->password && Hash::check($candidate, $user->password)) {
                        $weak[] = [$user, $candidate];
                        break;
                    }
                }
            }
        });

        return $weak;
    }

    private function apply(User $user): void
    {
        $password = $this->option('password') ?: Str::random(24);

        $user->forceFill(['password' => Hash::make($password)])->save();

        $this->newLine();
        $this->line(str_repeat('=', 58));
        $this->info(' Password rotated: ' . $user->email);
        $this->info(' New password: ' . $password);
        $this->line(' Store it now — it is not recoverable from the database.');
        $this->line(str_repeat('=', 58));
        $this->newLine();
    }
}
