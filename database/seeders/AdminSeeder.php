<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
    /**
     * Seed the initial administrator.
     *
     * docker/entrypoint.sh runs `php artisan db:seed --force` on EVERY boot, so
     * this seeder runs in production. It previously hard-coded
     * admin@admin.com / 12345678 -- publicly documented credentials for a live
     * admin account on every deployment. Credentials now come from
     * ADMIN_EMAIL / ADMIN_PASSWORD; when no password is supplied in production
     * a random one is generated and printed once to the deploy log, so the
     * account is never reachable with a guessable password.
     */
    public function run()
    {
        $email = env('ADMIN_EMAIL', 'admin@admin.com');
        $handle = env('ADMIN_HANDLE', 'admin');

        if (User::where('email', $email)->exists()) {
            return;
        }

        $password = env('ADMIN_PASSWORD');
        $generated = false;

        if (empty($password)) {
            if (app()->environment('production')) {
                $password = Str::random(24);
                $generated = true;
            } else {
                // Local/CI convenience only - never reached in production.
                $password = 'password';
            }
        }

        // Model::create (not insert) so the boot() id logic and the
        // handle/bio column mutators are applied.
        User::create([
            'name' => 'admin',
            'email' => $email,
            'email_verified_at' => now(),
            'password' => Hash::make($password),
            'role' => 'admin',
            'block' => 'no',
            'handle' => $handle,
            'bio' => 'admin page',
        ]);

        if ($generated) {
            $this->command?->warn('==================================================');
            $this->command?->warn(' Admin account created: ' . $email);
            $this->command?->warn(' Generated password: ' . $password);
            $this->command?->warn(' Store it now and change it after first login.');
            $this->command?->warn(' Set ADMIN_PASSWORD to control this yourself.');
            $this->command?->warn('==================================================');
        }
    }
}
