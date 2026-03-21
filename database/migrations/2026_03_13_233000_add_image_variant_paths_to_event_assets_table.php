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
            $table->string('thumbnail_path')->nullable()->after('path');
            $table->string('preview_path')->nullable()->after('thumbnail_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_assets', function (Blueprint $table) {
            $table->dropColumn(['thumbnail_path', 'preview_path']);
        });
    }
};
