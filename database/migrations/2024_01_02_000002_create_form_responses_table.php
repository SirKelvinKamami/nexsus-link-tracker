<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('form_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained()->onDelete('cascade');
            $table->string('session_id')->nullable();
            $table->string('ip_hash')->nullable();
            $table->string('email')->nullable();
            $table->json('answers');
            $table->string('referrer')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
            
            $table->index('form_id');
            $table->index('session_id');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('form_responses');
    }
};
