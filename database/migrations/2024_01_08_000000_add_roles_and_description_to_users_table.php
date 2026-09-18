<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // NOTE: this migration originally did
        //   DB::table('users')->where('role', 'user')->update(['role' => 'viewer']);
        // but the users.role CHECK constraint (users_role_check, created by the
        // original enum() column) still only allows user/vip/admin at that
        // point, so on Postgres with a NON-empty users table this UPDATE
        // violates the constraint and the migration fails - which wedged
        // production boot migrations and 500'd registration (role='viewer'
        // insert). The constraint is replaced and legacy roles remapped in
        // 2024_01_08_000003_replace_role_check_constraint.php instead.
    }

    public function down()
    {
        //
    }
};
