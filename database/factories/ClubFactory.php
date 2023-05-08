<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Club>
 */
class ClubFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $manager = User::factory()->create(['role' => 'club_manager']);
        return [

            'name' => $this->faker->name(),
            'title' => $this->faker->title(),
            'description' => $this->faker->paragraph(),
            'logo' => $this->faker->imageUrl(),
            'email' => $this->faker->companyEmail(),
            'phone_number' => $this->faker->unique()->numerify('+90-5##-###-##-##'),
            'website' => $this->faker->url(),
            'founded_year' => $this->faker->year(),
            'social_media_links' => json_encode([
                'facebook' => $this->faker->url(),
                'twitter' => $this->faker->url(),
                'instagram' => $this->faker->url(),
            ]),

            'manager_id' => $manager->id,
        ];
    }
}
