<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EventAsset>
 */
class EventAssetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'user_id' => User::factory(),
            'kind' => $this->faker->randomElement(['photo', 'video']),
            'disk' => 'public',
            'path' => 'events/'.$this->faker->uuid().'.jpg',
            'original_filename' => $this->faker->word().'.jpg',
            'mime_type' => 'image/jpeg',
            'size_bytes' => 204800,
            'width' => 1920,
            'height' => 1080,
            'duration_seconds' => null,
            'moderation_status' => 'approved',
            'moderation_score' => null,
            'is_watermarked' => false,
            'metadata' => null,
            'reviewed_at' => null,
        ];
    }
}
