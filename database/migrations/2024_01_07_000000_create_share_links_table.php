<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('share_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->string('type'); // dashboard, analytics, form, landing_page, custom
            $table->string('target_id')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('password')->nullable();
            $table->integer('max_uses')->nullable();
            $table->integer('use_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('share_links');
    }
};
