<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLinkClicksTable extends Migration
{
    public function up()
    {
        Schema::create('link_clicks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('link_id')->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('session_id', 100)->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->string('referrer', 2048)->nullable();
            $table->string('utm_source', 255)->nullable()->index();
            $table->string('utm_medium', 255)->nullable()->index();
            $table->string('utm_campaign', 255)->nullable()->index();
            $table->string('utm_term', 255)->nullable();
            $table->string('utm_content', 255)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->string('device_type', 32)->nullable()->index();
            $table->string('browser', 64)->nullable()->index();
            $table->string('browser_version', 32)->nullable();
            $table->string('os', 64)->nullable()->index();
            $table->string('country', 8)->nullable()->index();
            $table->string('language', 8)->nullable();
            $table->timestamps();

            $table->foreign('link_id')->references('id')->on('links')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('link_clicks');
    }
}