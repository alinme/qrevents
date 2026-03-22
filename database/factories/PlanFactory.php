<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Plan>
 */
class PlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Starter 20 EUR',
            'slug' => Str::slug($this->faker->unique()->words(3, true)),
            'description' => '30 days retention, 10GB storage, 300 uploads.',
            'currency' => 'EUR',
            'price_cents' => 2000,
            'storage_limit_bytes' => 10737418240,
            'upload_limit' => 300,
            'retention_days' => 30,
            'grace_days' => 7,
            'upload_window_days' => 30,
            'customization_tier' => 'basic',
            'download_all_enabled' => false,
            'moderation_tools_enabled' => false,
            'remove_app_branding' => false,
            'video_max_duration_seconds' => 30,
            'photo_max_size_bytes' => 26214400,
            'video_max_size_bytes' => 524288000,
            'is_active' => true,
            'is_default' => true,
        ];
    }
}
