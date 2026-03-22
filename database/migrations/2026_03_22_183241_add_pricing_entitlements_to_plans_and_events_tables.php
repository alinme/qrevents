<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->unsignedSmallInteger('upload_window_days')->default(1);
            $table->string('customization_tier', 16)->default('basic');
            $table->boolean('download_all_enabled')->default(false);
            $table->boolean('moderation_tools_enabled')->default(false);
            $table->boolean('remove_app_branding')->default(false);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->unsignedSmallInteger('upload_window_days')->default(1);
            $table->string('customization_tier', 16)->default('basic');
            $table->boolean('download_all_enabled')->default(false);
            $table->boolean('moderation_tools_enabled')->default(false);
            $table->boolean('remove_app_branding')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'upload_window_days',
                'customization_tier',
                'download_all_enabled',
                'moderation_tools_enabled',
                'remove_app_branding',
            ]);
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn([
                'upload_window_days',
                'customization_tier',
                'download_all_enabled',
                'moderation_tools_enabled',
                'remove_app_branding',
            ]);
        });
    }
};
