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
        Schema::table('events', function (Blueprint $table) {
            $table->string('stripe_checkout_session_id')->nullable()->after('billing_note')->index();
            $table->string('stripe_payment_intent_id')->nullable()->after('stripe_checkout_session_id')->index();
            $table->unsignedInteger('stripe_paid_amount_cents')->nullable()->after('stripe_payment_intent_id');
            $table->string('stripe_paid_currency', 3)->nullable()->after('stripe_paid_amount_cents');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'stripe_checkout_session_id',
                'stripe_payment_intent_id',
                'stripe_paid_amount_cents',
                'stripe_paid_currency',
            ]);
        });
    }
};
