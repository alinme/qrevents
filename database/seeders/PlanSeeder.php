<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Plan::query()->updateOrCreate(
            ['slug' => 'starter-20-eur'],
            [
                'name' => 'Starter 20 EUR',
                'description' => '30 days retention, 10GB storage, 300 uploads.',
                'currency' => 'EUR',
                'price_cents' => 2000,
                'storage_limit_bytes' => 10737418240,
                'upload_limit' => 300,
                'retention_days' => 30,
                'grace_days' => 7,
                'video_max_duration_seconds' => 30,
                'photo_max_size_bytes' => 26214400,
                'video_max_size_bytes' => 524288000,
                'is_active' => true,
                'is_default' => true,
            ],
        );

        Plan::query()->updateOrCreate(
            ['slug' => 'starter-20-ron'],
            [
                'name' => 'Starter 99 RON',
                'description' => '30 days retention, 10GB storage, 300 uploads.',
                'currency' => 'RON',
                'price_cents' => 9900,
                'storage_limit_bytes' => 10737418240,
                'upload_limit' => 300,
                'retention_days' => 30,
                'grace_days' => 7,
                'video_max_duration_seconds' => 30,
                'photo_max_size_bytes' => 26214400,
                'video_max_size_bytes' => 524288000,
                'is_active' => true,
                'is_default' => false,
            ],
        );
    }
}
