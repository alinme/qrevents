<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EventGuestParty>
 */
class EventGuestPartyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'name' => $this->faker->randomElement([
                'Familia Popescu',
                'Familia Ionescu',
                'James Webb',
                'Maria si Andrei',
            ]),
            'phone' => '07'.$this->faker->numerify('########'),
            'invited_attendees_count' => $this->faker->numberBetween(1, 6),
            'confirmed_attendees_count' => null,
            'attendance_status' => 'pending',
            'notes' => $this->faker->optional()->sentence(),
            'invitation_status' => 'draft',
            'invitation_delivery_channel' => null,
            'invitation_delivered_at' => null,
            'invitation_token' => Str::lower((string) Str::uuid()),
            'invitation_open_count' => 0,
            'invitation_first_opened_at' => null,
            'invitation_last_opened_at' => null,
            'invitation_last_opened_ip' => null,
            'invitation_last_opened_user_agent' => null,
            'responded_at' => null,
            'gift_type' => null,
            'gift_currency' => null,
            'gift_amount' => null,
        ];
    }
}
