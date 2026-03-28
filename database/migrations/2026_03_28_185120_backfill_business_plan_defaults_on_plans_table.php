<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('plans')
            ->where('slug', 'free')
            ->update([
                'business_enabled' => false,
                'business_credit_cost' => null,
            ]);

        DB::table('plans')
            ->where('slug', 'plus')
            ->update([
                'business_enabled' => true,
                'business_credit_cost' => 25,
            ]);

        DB::table('plans')
            ->where('slug', 'pro')
            ->update([
                'business_enabled' => true,
                'business_credit_cost' => 50,
            ]);
    }

    public function down(): void
    {
        DB::table('plans')
            ->whereIn('slug', ['plus', 'pro'])
            ->update([
                'business_enabled' => false,
                'business_credit_cost' => null,
            ]);
    }
};
