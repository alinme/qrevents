<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'slug' => 'free',
                'name' => 'Free',
                'description' => 'Small events with a lightweight album and simple setup.',
                'currency' => 'EUR',
                'price_cents' => 0,
                'storage_limit_bytes' => 3221225472,
                'upload_limit' => 100,
                'retention_days' => 7,
                'grace_days' => 0,
                'upload_window_days' => 1,
                'customization_tier' => 'basic',
                'download_all_enabled' => false,
                'moderation_tools_enabled' => false,
                'remove_app_branding' => false,
                'video_max_duration_seconds' => 30,
                'photo_max_size_bytes' => 15728640,
                'video_max_size_bytes' => 314572800,
                'is_active' => true,
                'is_default' => true,
            ],
            [
                'slug' => 'plus',
                'name' => 'Plus',
                'description' => 'Mid-size celebrations with longer access and better customization.',
                'currency' => 'EUR',
                'price_cents' => 4900,
                'storage_limit_bytes' => 12884901888,
                'upload_limit' => 500,
                'retention_days' => 90,
                'grace_days' => 7,
                'upload_window_days' => 30,
                'customization_tier' => 'better',
                'download_all_enabled' => true,
                'moderation_tools_enabled' => false,
                'remove_app_branding' => false,
                'video_max_duration_seconds' => 45,
                'photo_max_size_bytes' => 26214400,
                'video_max_size_bytes' => 524288000,
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'slug' => 'pro',
                'name' => 'Pro',
                'description' => 'Large events with advanced branding, moderation, and longer access.',
                'currency' => 'EUR',
                'price_cents' => 9900,
                'storage_limit_bytes' => 32212254720,
                'upload_limit' => 1000000,
                'retention_days' => 365,
                'grace_days' => 14,
                'upload_window_days' => 90,
                'customization_tier' => 'advanced',
                'download_all_enabled' => true,
                'moderation_tools_enabled' => true,
                'remove_app_branding' => true,
                'video_max_duration_seconds' => 60,
                'photo_max_size_bytes' => 31457280,
                'video_max_size_bytes' => 1073741824,
                'is_active' => true,
                'is_default' => false,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::query()->updateOrCreate(
                ['slug' => $plan['slug']],
                $plan,
            );
        }

        Plan::query()
            ->whereIn('slug', ['starter-20-eur', 'starter-20-ron'])
            ->update(['is_active' => false, 'is_default' => false]);
    }
}
