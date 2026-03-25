<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_guest_party_invitation_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_guest_party_id');
            $table->foreignId('actor_user_id')->nullable();
            $table->string('activity_type', 40);
            $table->string('delivery_channel')->nullable();
            $table->json('meta')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->foreign('event_guest_party_id', 'egpia_guest_party_fk')
                ->references('id')
                ->on('event_guest_parties')
                ->cascadeOnDelete();
            $table->foreign('actor_user_id', 'egpia_actor_user_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
            $table->index(['event_guest_party_id', 'created_at'], 'egpia_party_created_idx');
            $table->index(['event_id', 'activity_type'], 'egpia_event_type_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_guest_party_invitation_activities');
    }
};
