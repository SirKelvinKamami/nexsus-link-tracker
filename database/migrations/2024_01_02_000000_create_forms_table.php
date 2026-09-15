<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('collect_email')->default(false);
            $table->boolean('one_response_per_ip')->default(false);
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('slug');
        });
    }

    public function down()
    {
        Schema::dropIfExists('forms');
    }
};
