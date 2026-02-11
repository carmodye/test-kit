<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('proof_of_play_results', function (Blueprint $table) {
            $table->id();
            $table->string('client');
            $table->unsignedBigInteger('slide_id')->nullable();
            $table->string('slide_name')->nullable();
            $table->unsignedBigInteger('device_id')->nullable();
            $table->string('display_id')->nullable();
            $table->unsignedBigInteger('site_id')->nullable();
            $table->string('site_name')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->integer('play_count')->nullable();
            $table->dateTime('played_at')->nullable();
            $table->string('duration')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proof_of_play_results');
    }
};
