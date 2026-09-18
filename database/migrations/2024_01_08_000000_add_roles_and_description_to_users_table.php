<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Update existing 'user' role to 'viewer' (old default)
        DB::table('users')->where('role', 'user')->update(['role' => 'viewer']);
        
        // Add description column if not exists
        if (!Schema::hasColumn('users', 'description')) {
            Schema::table('users', function (Blueprint $table) {
                $table->text('description')->nullable()->after('littlelink_name');
            });
        }
    }

    public function down()
    {
        DB::table('users')->where('role', 'viewer')->update(['role' => 'user']);
    }
};
