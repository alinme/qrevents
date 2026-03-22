<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Plan;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $eventDate = CarbonImmutable::instance($this->faker->dateTimeBetween('+2 days', '+12 months'));
        $uploadWindowStartsAt = $eventDate->startOfDay();
        $uploadWindowEndsAt = $eventDate->addDays(29)->endOfDay();
        $graceEndsAt = $eventDate->addDays(36)->endOfDay();

        return [
            'user_id' => User::factory(),
            'plan_id' => Plan::factory(),
            'type' => $this->faker->randomElement([
                'wedding',
                'party',
                'birthday',
                'engagement',
                'baptism',
                'other',
            ]),
            'name' => $this->faker->sentence(3),
            'venue_address' => $this->faker->streetAddress().', '.$this->faker->city(),
            'event_date' => $eventDate->toDateString(),
            'event_dates' => [
                [
                    'label' => 'Main event day',
                    'date' => $eventDate->toDateString(),
                ],
            ],
            'sub_events' => null,
            'timezone' => 'Europe/Bucharest',
            'attendee_estimate' => $this->faker->numberBetween(40, 220),
            'status' => Event::STATUS_SCHEDULED,
            'onboarding_step' => 'completed',
            'onboarding_completed_at' => now(),
            'currency' => 'EUR',
            'is_paid' => false,
            'payment_due_at' => $uploadWindowStartsAt,
            'upload_window_starts_at' => $uploadWindowStartsAt,
            'upload_window_ends_at' => $uploadWindowEndsAt,
            'grace_ends_at' => $graceEndsAt,
            'hard_lock_at' => $graceEndsAt,
            'storage_limit_bytes' => 10737418240,
            'storage_used_bytes' => 0,
            'upload_limit' => 300,
            'upload_count' => 0,
            'upload_window_days' => 30,
            'customization_tier' => 'advanced',
            'download_all_enabled' => true,
            'moderation_tools_enabled' => true,
            'remove_app_branding' => false,
            'video_max_duration_seconds' => 30,
            'photo_max_size_bytes' => 26214400,
            'video_max_size_bytes' => 524288000,
            'album_public' => true,
            'moderation_enabled' => true,
            'auto_moderation_enabled' => true,
            'branding' => null,
            'share_token' => Str::random(32),
        ];
    }
}
