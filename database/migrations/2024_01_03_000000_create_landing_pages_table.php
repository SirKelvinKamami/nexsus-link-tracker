<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('landing_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->json('content')->nullable(); // blocks: hero, text, image, form, cta, etc.
            $table->json('settings')->nullable(); // colors, fonts, meta, etc.
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('og_image')->nullable();
            $table->boolean('is_published')->default(false);
            $table->boolean('collect_emails')->default(false);
            $table->string('form_id')->nullable(); // embedded form
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('slug');
            $table->index('is_published');
        });
    }

    public function down()
    {
        Schema::dropIfExists('landing_pages');
    }
};
