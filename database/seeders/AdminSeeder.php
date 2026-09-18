<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

use App\Models\User;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = [
            [
                'name' => 'admin',
                'email' => 'admin@admin.com',
                'email_verified_at' => now(),
                'password' => Hash::make('12345678'),
                'role' => 'admin',
                'handle' => 'admin',
                'bio' => 'admin page',
            ]
        ];

        // Idempotent: safe to run on every container boot
        if (!User::where('email', 'admin@admin.com')->exists()) {
            User::insert($admin);
        }
    }
}
