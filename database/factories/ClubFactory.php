<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Role;
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
        $manager = User::factory()->create([
            'role_id' => Role::where('slug', 'club_manager')->first()->id
        ]);



        return [
            'name' => $this->faker->name(),
            'title' => $this->faker->title(),
            'description' => $this->faker->paragraph(),
            'logo' => $this->faker->imageUrl(),
            'email' => $this->faker->companyEmail(),
            'phone_number' => $this->faker->unique()->numerify('+90-5##-###-##-##'),
            'website' => $this->faker->url(),
            'founded_year' => $this->faker->dateTime(),
          

            'manager_id' => $manager->id,
        ];
    }
}
