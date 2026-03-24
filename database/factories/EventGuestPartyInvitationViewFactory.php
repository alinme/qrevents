<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventGuestParty;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EventGuestPartyInvitationView>
 */
class EventGuestPartyInvitationViewFactory extends Factory
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
            'event_guest_party_id' => EventGuestParty::factory(),
            'invitation_kind' => 'party',
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'opened_at' => now(),
        ];
    }
}
