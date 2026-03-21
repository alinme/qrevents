<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_guests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('guest_token', 120);
            $table->string('name', 80);
            $table->string('email')->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('avatar_disk', 32)->nullable();
            $table->string('avatar_path')->nullable();
            $table->json('guest_fields')->nullable();
            $table->string('last_intent', 32)->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->unique(['event_id', 'guest_token']);
            $table->index(['event_id', 'updated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_guests');
    }
};
