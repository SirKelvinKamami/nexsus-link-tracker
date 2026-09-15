<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('webhooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('url');
            $table->string('secret')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('events')->nullable(); // ['click', 'form_submit', 'page_visit']
            $table->json('last_delivery')->nullable();
            $table->integer('failure_count')->default(0);
            $table->timestamps();
        });

        Schema::create('webhook_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('webhook_id')->constrained()->onDelete('cascade');
            $table->string('event');
            $table->json('payload');
            $table->boolean('success')->default(false);
            $table->integer('status_code')->nullable();
            $table->text('response_body')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('delivered_at');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('webhook_deliveries');
        Schema::dropIfExists('webhooks');
    }
};
