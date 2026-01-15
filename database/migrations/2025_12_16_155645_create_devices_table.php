<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('client');
            $table->string('device_id');
            $table->string('display_id');
            $table->string('site_name');
            $table->string('app_name');
            $table->string('site_id');
            $table->json('other_data')->nullable(); // For admin drill-down
            $table->timestamps();

            $table->unique(['client', 'device_id', 'display_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};