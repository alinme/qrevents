<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_guest_party_invitation_views', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_guest_party_id')->nullable()->constrained()->nullOnDelete();
            $table->string('invitation_kind', 16)->default('party');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('opened_at');
            $table->timestamps();

            $table->index(['event_id', 'invitation_kind', 'opened_at']);
            $table->index(['event_guest_party_id', 'opened_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_guest_party_invitation_views');
    }
};
