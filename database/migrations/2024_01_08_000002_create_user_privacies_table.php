<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_privacies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->boolean('profile_visible')->default(true);
            $table->boolean('email_visible')->default(false);
            $table->boolean('links_visible')->default(true);
            $table->boolean('analytics_visible')->default(false);
            $table->boolean('allow_comments')->default(false);
            $table->string('default_link_permission')->default('private'); // private, viewer, commenter, editor
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_privacies');
    }
};
