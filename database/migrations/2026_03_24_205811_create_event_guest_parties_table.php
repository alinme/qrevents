<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_guest_parties', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('phone', 40)->nullable();
            $table->unsignedSmallInteger('invited_attendees_count')->default(1);
            $table->unsignedSmallInteger('confirmed_attendees_count')->nullable();
            $table->string('attendance_status', 24)->default('pending');
            $table->text('notes')->nullable();
            $table->string('invitation_status', 24)->default('draft');
            $table->string('invitation_delivery_channel', 24)->nullable();
            $table->timestamp('invitation_delivered_at')->nullable();
            $table->string('invitation_token', 80)->nullable()->unique();
            $table->unsignedInteger('invitation_open_count')->default(0);
            $table->timestamp('invitation_first_opened_at')->nullable();
            $table->timestamp('invitation_last_opened_at')->nullable();
            $table->string('invitation_last_opened_ip', 45)->nullable();
            $table->text('invitation_last_opened_user_agent')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->string('gift_type', 16)->nullable();
            $table->string('gift_currency', 3)->nullable();
            $table->decimal('gift_amount', 10, 2)->nullable();
            $table->timestamps();

            $table->index(['event_id', 'attendance_status']);
            $table->index(['event_id', 'invitation_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_guest_parties');
    }
};
