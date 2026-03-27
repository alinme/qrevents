<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EventTable>
 */
class EventTableFactory extends Factory
{
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'name' => 'Table '.$this->faker->numberBetween(1, 20),
            'seats_count' => $this->faker->numberBetween(6, 12),
            'sort_order' => $this->faker->numberBetween(1, 20),
        ];
    }
}
