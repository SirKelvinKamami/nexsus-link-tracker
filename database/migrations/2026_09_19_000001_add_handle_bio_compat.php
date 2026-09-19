<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'littlelink_name') && !Schema::hasColumn('users', 'handle')) {
            try {
                DB::statement('ALTER TABLE users RENAME COLUMN littlelink_name TO handle');
            } catch (\Throwable $e) {
                Schema::table('users', function (Blueprint $table) {
                    $table->string('handle')->nullable()->unique();
                });
                DB::statement('UPDATE users SET handle = littlelink_name WHERE handle IS NULL');
            }
        } elseif (!Schema::hasColumn('users', 'handle')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('handle')->nullable()->unique();
            });
        }

        if (Schema::hasColumn('users', 'littlelink_description') && !Schema::hasColumn('users', 'bio')) {
            try {
                DB::statement('ALTER TABLE users RENAME COLUMN littlelink_description TO bio');
            } catch (\Throwable $e) {
                Schema::table('users', function (Blueprint $table) {
                    $table->text('bio')->nullable();
                });
                DB::statement('UPDATE users SET bio = littlelink_description WHERE bio IS NULL');
            }
        } elseif (!Schema::hasColumn('users', 'bio')) {
            Schema::table('users', function (Blueprint $table) {
                $table->text('bio')->nullable();
            });
        }

        if (Schema::hasColumn('users', 'littlelink_name') && Schema::hasColumn('users', 'handle')) {
            try { DB::statement('UPDATE users SET handle = littlelink_name WHERE handle IS NULL AND littlelink_name IS NOT NULL'); } catch (\Throwable $e) {}
        }
        if (Schema::hasColumn('users', 'littlelink_description') && Schema::hasColumn('users', 'bio')) {
            try { DB::statement('UPDATE users SET bio = littlelink_description WHERE bio IS NULL AND littlelink_description IS NOT NULL'); } catch (\Throwable $e) {}
        }
    }

    public function down(): void
    {
    }
};
