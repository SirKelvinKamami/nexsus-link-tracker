<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled', 'snoozed'])->default('pending');
            $table->tinyInteger('priority')->default(3); // 1=low, 5=high
            $table->timestamp('due_datetime')->nullable();
            $table->integer('estimated_duration')->nullable(); // minutes
            $table->timestamp('scheduled_for')->nullable();
            $table->string('project')->nullable();
            $table->unsignedBigInteger('parent_task_id')->nullable();
            $table->foreign('parent_task_id')->references('id')->on('tasks')->onDelete('set null');
            $table->text('recurring_rule')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
