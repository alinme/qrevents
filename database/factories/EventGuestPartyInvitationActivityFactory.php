<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventGuestParty;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EventGuestPartyInvitationActivity>
 */
class EventGuestPartyInvitationActivityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'event_guest_party_id' => EventGuestParty::factory(),
            'actor_user_id' => User::factory(),
            'activity_type' => $this->faker->randomElement([
                'sent_online',
                'delivered_in_person',
                'reminded',
                'opened',
                'responded',
            ]),
            'delivery_channel' => $this->faker->randomElement([
                'in_person',
                'phone',
                'whatsapp',
                'facebook',
                'public_link',
                'other',
            ]),
            'meta' => null,
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
        ];
    }
}
