<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * users.role still defaults to the legacy value 'user', but
     * 2024_01_08_000003 narrowed users_role_check to
     * viewer/commenter/editor/admin. Any INSERT that omits role therefore
     * fails the constraint outright -- which breaks social login signup and
     * the installer's first-admin creation.
     *
     * Align the default with the constraint and repair any surviving legacy
     * rows.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'role')) {
            return;
        }

        $driver = DB::connection()->getDriverName();

        // Repair legacy values before touching the default.
        DB::table('users')->where('role', 'user')->update(['role' => 'viewer']);
        DB::table('users')->where('role', 'vip')->update(['role' => 'editor']);

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE users ALTER COLUMN role SET DEFAULT 'viewer'");
        } elseif ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement("ALTER TABLE users ALTER COLUMN role SET DEFAULT 'viewer'");
        }
        // SQLite cannot alter a column default and enforces no CHECK here;
        // application code always supplies an explicit role.
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'role')) {
            return;
        }

        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql' || $driver === 'mysql' || $driver === 'mariadb') {
            DB::statement("ALTER TABLE users ALTER COLUMN role SET DEFAULT 'user'");
        }
    }
};
