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
        Schema::create('event_guest_ledger_reminder_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('reminder_key', 40);
            $table->timestamp('retention_ends_at');
            $table->timestamp('sent_at');
            $table->timestamps();

            $table->unique(['event_id', 'reminder_key', 'retention_ends_at'], 'event_guest_ledger_reminders_unique');
            $table->index(['event_id', 'sent_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_guest_ledger_reminder_logs');
    }
};
