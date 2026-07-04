<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Consolidate to a two-tier consumer model — Free + one paid plan (Plus, €49) —
 * and peg the business wallet cost to €1 = 1 credit (a €49 event = 49 credits;
 * the business's discount lives in the wholesale credit purchase, not here).
 *
 * Pro is retired: deactivated and removed from business options. Existing events
 * keep their own copied limits and their plan_id row still exists, so nothing
 * about already-created events changes.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('plans')->where('slug', 'plus')->update([
            'business_credit_cost' => 49,
        ]);

        DB::table('plans')->where('slug', 'pro')->update([
            'is_active' => false,
            'business_enabled' => false,
        ]);
    }

    public function down(): void
    {
        DB::table('plans')->where('slug', 'plus')->update([
            'business_credit_cost' => 25,
        ]);

        DB::table('plans')->where('slug', 'pro')->update([
            'is_active' => true,
            'business_enabled' => true,
        ]);
    }
};
