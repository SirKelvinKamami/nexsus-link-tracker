<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // Role set the application actually uses (RegisteredUserController,
    // AdminController, Livewire UserTable): viewer / commenter / editor / admin.
    private const ROLES = ['viewer', 'commenter', 'editor', 'admin'];

    public function up()
    {
        // 1. Drop the legacy enum CHECK (user/vip/admin) FIRST - Postgres
        //    validates a new CHECK against existing rows on ADD, so legacy
        //    role values must be remapped while no constraint is present.
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');
        }

        // 2. Remap legacy data. 'vip' has no modern equivalent - closest
        //    capability tier is editor.
        if (Schema::hasColumn('users', 'role')) {
            DB::table('users')->where('role', 'user')->update(['role' => 'viewer']);
            DB::table('users')->where('role', 'vip')->update(['role' => 'editor']);
        }

        // 3. Add the CHECK matching the application's role set. SQLite
        //    enforces nothing at the DB level; skip there.
        if (DB::connection()->getDriverName() === 'pgsql') {
            $roles = implode(', ', array_map(fn ($r) => "'".$r."'", self::ROLES));
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role::text = ANY (ARRAY[$roles]::text[]))");
        }
    }

    public function down()
    {
        // Restore legacy values first (viewer/commenter/editor are not allowed
        // by the old constraint), then swap the CHECK back on Postgres.
        DB::table('users')->where('role', 'editor')->update(['role' => 'vip']);
        DB::table('users')->whereIn('role', ['viewer', 'commenter'])->update(['role' => 'user']);

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role::text = ANY (ARRAY['user'::text, 'vip'::text, 'admin'::text]))");
        }
    }
};
