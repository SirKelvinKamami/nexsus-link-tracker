<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained()->onDelete('cascade');
            $table->string('label');
            $table->string('type'); // text, email, textarea, select, radio, checkbox, date, number, file, phone
            $table->json('options')->nullable(); // for select/radio/checkbox options
            $table->boolean('is_required')->default(false);
            $table->string('placeholder')->nullable();
            $table->string('default_value')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
            
            $table->index('form_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('form_fields');
    }
};
