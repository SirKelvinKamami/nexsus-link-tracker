<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Originally updated role data ('user' -> 'viewer'), but the enum
        // CHECK constraint still forbids 'viewer' at this point, so on
        // Postgres this fails on any non-empty users table. Data remapping
        // happens in 2024_01_08_000003_replace_role_check_constraint.php
        // AFTER the constraint is replaced. SQLite enforces nothing at the
        // DB level; Postgres does - see production incident 2026-09-18.
    }

    public function down()
    {
        //
    }
};
