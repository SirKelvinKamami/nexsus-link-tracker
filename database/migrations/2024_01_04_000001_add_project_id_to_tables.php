<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('links', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('user_id')->constrained()->onDelete('set null');
        });

        Schema::table('forms', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('user_id')->constrained()->onDelete('set null');
        });

        Schema::table('landing_pages', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('user_id')->constrained()->onDelete('set null');
        });

        Schema::table('link_clicks', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('user_id')->constrained()->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('links', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropColumn('project_id');
        });

        Schema::table('forms', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropColumn('project_id');
        });

        Schema::table('landing_pages', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropColumn('project_id');
        });

        Schema::table('link_clicks', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropColumn('project_id');
        });
    }
};
