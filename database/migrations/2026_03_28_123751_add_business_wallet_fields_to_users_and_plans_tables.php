<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('business_onboarded_at')->nullable()->after('account_type');
            $table->json('business_profile')->nullable()->after('business_onboarded_at');
            $table->unsignedInteger('business_wallet_credits')->default(0)->after('business_profile');
            $table->char('business_wallet_currency', 3)->default('EUR')->after('business_wallet_credits');
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->boolean('business_enabled')->default(false)->after('is_default');
            $table->unsignedInteger('business_credit_cost')->nullable()->after('business_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn([
                'business_enabled',
                'business_credit_cost',
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'business_onboarded_at',
                'business_profile',
                'business_wallet_credits',
                'business_wallet_currency',
            ]);
        });
    }
};
