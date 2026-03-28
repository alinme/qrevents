<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_wallet_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('credits_purchased');
            $table->unsignedInteger('bonus_credits')->default(0);
            $table->unsignedInteger('total_credits');
            $table->unsignedInteger('base_amount_cents');
            $table->char('checkout_currency', 3);
            $table->unsignedInteger('localized_amount_cents');
            $table->decimal('locked_fx_rate', 18, 8);
            $table->string('status', 24)->default('pending');
            $table->string('stripe_checkout_session_id')->nullable()->index();
            $table->string('stripe_payment_intent_id')->nullable()->index();
            $table->timestamp('paid_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_wallet_purchases');
    }
};
