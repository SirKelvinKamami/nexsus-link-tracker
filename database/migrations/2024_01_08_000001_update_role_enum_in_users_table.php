<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // SQLite doesn't support ALTER COLUMN, so we need raw SQL
        // For SQLite, we'll just update existing data
        // The enum constraint is only enforced at application level in SQLite
        DB::table('users')->where('role', 'user')->update(['role' => 'viewer']);
    }

    public function down()
    {
        DB::table('users')->where('role', 'viewer')->update(['role' => 'user']);
    }
};
