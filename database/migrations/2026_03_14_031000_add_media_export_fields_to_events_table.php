<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            $table->string('media_export_status')->nullable()->after('share_token');
            $table->string('media_export_token')->nullable()->after('media_export_status');
            $table->string('media_export_disk')->nullable()->after('media_export_token');
            $table->string('media_export_path')->nullable()->after('media_export_disk');
            $table->timestamp('media_export_requested_at')->nullable()->after('media_export_path');
            $table->timestamp('media_export_started_at')->nullable()->after('media_export_requested_at');
            $table->timestamp('media_export_completed_at')->nullable()->after('media_export_started_at');
            $table->timestamp('media_export_failed_at')->nullable()->after('media_export_completed_at');
            $table->text('media_export_error')->nullable()->after('media_export_failed_at');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            $table->dropColumn([
                'media_export_status',
                'media_export_token',
                'media_export_disk',
                'media_export_path',
                'media_export_requested_at',
                'media_export_started_at',
                'media_export_completed_at',
                'media_export_failed_at',
                'media_export_error',
            ]);
        });
    }
};
