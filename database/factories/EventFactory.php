<?php

namespace Database\Factories;

use App\Models\Club;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\EventCategory;

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
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(1),
            'start_time' => $this->faker->dateTimeBetween('now', '+1 year'),
            'end_time' => $this->faker->dateTimeBetween('+1 year', '+2 years'),
            'location' => $this->faker->address,
            'image' => $this->faker->imageUrl(640, 480, 'animals', true),
            'club_id' => Club::factory()->create()->id,
            'category_id' => EventCategory::factory()->create()->id
        ];
    }
}
