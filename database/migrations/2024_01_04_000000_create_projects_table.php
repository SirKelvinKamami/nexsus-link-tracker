<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->json('settings')->nullable(); // colors, fonts, ga4, gtm, etc.
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('slug');
        });
    }

    public function down()
    {
        Schema::dropIfExists('projects');
    }
};
