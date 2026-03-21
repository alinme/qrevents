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
        Schema::table('events', function (Blueprint $table): void {
            $table->text('venue_address')->nullable()->after('name');
            $table->unsignedInteger('attendee_estimate')->nullable()->after('timezone');
            $table->json('event_dates')->nullable()->after('event_date');
            $table->json('sub_events')->nullable()->after('event_dates');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            $table->dropColumn([
                'venue_address',
                'attendee_estimate',
                'event_dates',
                'sub_events',
            ]);
        });
    }
};
