<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EventGuest>
 */
class EventGuestFactory extends Factory
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
            'guest_token' => $this->faker->uuid(),
            'name' => $this->faker->name(),
            'email' => $this->faker->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'avatar_disk' => null,
            'avatar_path' => null,
            'guest_fields' => null,
            'last_intent' => 'browse_gallery',
            'last_seen_at' => now(),
        ];
    }
}
