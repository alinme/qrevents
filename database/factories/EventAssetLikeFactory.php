<?php

namespace Database\Factories;

use App\Models\EventAsset;
use App\Models\EventGuest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EventAssetLike>
 */
class EventAssetLikeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_asset_id' => EventAsset::factory(),
            'event_guest_id' => EventGuest::factory(),
        ];
    }
}
