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
        Schema::table('event_assets', function (Blueprint $table) {
            $table->string('watermarked_thumbnail_path')->nullable()->after('preview_path');
            $table->string('watermarked_preview_path')->nullable()->after('watermarked_thumbnail_path');
            $table->string('watermarked_download_path')->nullable()->after('watermarked_preview_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_assets', function (Blueprint $table) {
            $table->dropColumn([
                'watermarked_thumbnail_path',
                'watermarked_preview_path',
                'watermarked_download_path',
            ]);
        });
    }
};
