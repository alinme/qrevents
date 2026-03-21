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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->char('currency', 3);
            $table->unsignedInteger('price_cents');
            $table->unsignedBigInteger('storage_limit_bytes');
            $table->unsignedInteger('upload_limit');
            $table->unsignedSmallInteger('retention_days');
            $table->unsignedSmallInteger('grace_days')->default(7);
            $table->unsignedTinyInteger('video_max_duration_seconds')->default(30);
            $table->unsignedInteger('photo_max_size_bytes')->default(26214400);
            $table->unsignedInteger('video_max_size_bytes')->default(524288000);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
