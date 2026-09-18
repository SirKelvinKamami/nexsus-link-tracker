<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('littlelink_name', 'handle');
            $table->renameColumn('littlelink_description', 'bio');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('handle', 'littlelink_name');
            $table->renameColumn('bio', 'littlelink_description');
        });
    }
};
