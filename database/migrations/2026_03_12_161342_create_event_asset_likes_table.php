<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_asset_likes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('event_asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_guest_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['event_asset_id', 'event_guest_id']);
            $table->index(['event_guest_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_asset_likes');
    }
};
