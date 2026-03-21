<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_assets', function (Blueprint $table): void {
            $table->string('video_thumbnail_path')->nullable()->after('watermarked_download_path');
            $table->string('watermarked_video_thumbnail_path')->nullable()->after('video_thumbnail_path');
        });
    }

    public function down(): void
    {
        Schema::table('event_assets', function (Blueprint $table): void {
            $table->dropColumn([
                'video_thumbnail_path',
                'watermarked_video_thumbnail_path',
            ]);
        });
    }
};
