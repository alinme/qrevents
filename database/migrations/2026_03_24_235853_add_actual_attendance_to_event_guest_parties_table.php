<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_guest_parties', function (Blueprint $table): void {
            $table->string('actual_attendance_status', 24)->default('unknown')->after('attendance_status');
            $table->unsignedSmallInteger('actual_attendees_count')->nullable()->after('confirmed_attendees_count');
            $table->timestamp('actual_attendance_recorded_at')->nullable()->after('responded_at');

            $table->index(['event_id', 'actual_attendance_status']);
        });
    }

    public function down(): void
    {
        Schema::table('event_guest_parties', function (Blueprint $table): void {
            $table->dropIndex(['event_id', 'actual_attendance_status']);
            $table->dropColumn([
                'actual_attendance_status',
                'actual_attendees_count',
                'actual_attendance_recorded_at',
            ]);
        });
    }
};
