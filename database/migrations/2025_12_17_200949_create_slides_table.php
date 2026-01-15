<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('slides', function (Blueprint $table) {
            $table->id();
            $table->string('client'); // e.g., 'demo', 'acme'
            $table->unsignedBigInteger('slide_id'); // the id from API
            $table->string('name');
            $table->string('path');
            $table->string('type'); // folder, image, video, etc.
            $table->integer('duration')->default(0);
            $table->integer('hold')->default(0);
            $table->string('notbefore')->nullable();
            $table->string('notafter')->nullable();
            $table->boolean('deleted')->default(false);
            $table->json('raw_data')->nullable(); // full original record for future use
            $table->timestamps();

            $table->unique(['client', 'slide_id']);
            $table->index(['client', 'type']);
            $table->index('path');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slides');
    }
};