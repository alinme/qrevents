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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->string('type', 64);
            $table->string('name');
            $table->date('event_date')->nullable();
            $table->string('timezone', 64)->default('Europe/Bucharest');
            $table->string('status', 32)->default('draft');
            $table->string('onboarding_step', 32)->default('created');
            $table->timestamp('onboarding_completed_at')->nullable();
            $table->char('currency', 3)->default('EUR');
            $table->boolean('is_paid')->default(false);
            $table->timestamp('payment_due_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('retention_ends_at')->nullable();
            $table->timestamp('renewal_grace_ends_at')->nullable();
            $table->timestamp('upload_window_starts_at')->nullable();
            $table->timestamp('upload_window_ends_at')->nullable();
            $table->timestamp('grace_ends_at')->nullable();
            $table->timestamp('hard_lock_at')->nullable();
            $table->unsignedBigInteger('storage_limit_bytes')->default(10737418240);
            $table->unsignedBigInteger('storage_used_bytes')->default(0);
            $table->unsignedInteger('upload_limit')->default(300);
            $table->unsignedInteger('upload_count')->default(0);
            $table->unsignedTinyInteger('video_max_duration_seconds')->default(30);
            $table->unsignedInteger('photo_max_size_bytes')->default(26214400);
            $table->unsignedInteger('video_max_size_bytes')->default(524288000);
            $table->boolean('album_public')->default(true);
            $table->boolean('moderation_enabled')->default(true);
            $table->boolean('auto_moderation_enabled')->default(true);
            $table->json('branding')->nullable();
            $table->string('share_token')->unique();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['status', 'event_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
