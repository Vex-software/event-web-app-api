<?php

namespace Database\Factories;
use App\Models\Club;

use Illuminate\Database\Eloquent\Factories\Factory;

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
        return [

            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(2),
            'start_time' => $this->faker->dateTimeBetween('now', '+1 year'),
            'end_time' => $this->faker->dateTimeBetween('+1 year', '+2 years'),
            'club_id' => Club::factory()->create()->id


        ];
    }
}
