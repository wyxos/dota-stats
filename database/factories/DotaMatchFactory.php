<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DotaMatch>
 */
class DotaMatchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'match_id' => $this->faker->unique()->numberBetween(1000000000, 9999999999),
            'player_slot' => $this->faker->numberBetween(0, 9),
            'radiant_win' => $this->faker->boolean,
            'duration' => $this->faker->numberBetween(600, 3600),
            'game_mode' => $this->faker->numberBetween(1, 22),
            'lobby_type' => $this->faker->numberBetween(0, 7),
            'hero_id' => $this->faker->numberBetween(1, 130),
            'start_time' => $this->faker->unixTime,
            'version' => (string) $this->faker->numberBetween(1, 10),
            'kills' => $this->faker->numberBetween(0, 30),
            'deaths' => $this->faker->numberBetween(0, 20),
            'assists' => $this->faker->numberBetween(0, 40),
            'average_rank' => $this->faker->optional()->numberBetween(10, 80),
            'leaver_status' => $this->faker->numberBetween(0, 2),
            'party_size' => $this->faker->optional()->numberBetween(1, 5),
            'hero_variant' => $this->faker->optional()->word,
            'details' => null,
            'is_parsed' => false,
        ];
    }
}
