<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'surname' => $this->faker->lastName(),
            'phone_number' => $this->faker->unique()->numerify('+90-5##-###-##-##'),
            // 'phone_number' => $this->faker->unique()->regexify('\+90-[1-9]{3}-[1-9]{3}-[0-9]{2}-[0-9]{2}'),
            'email' => $this->faker->unique()->safeEmail(),

            // 'role_id' => $this->faker->numberBetween(1,3),
            // 'role_id' => Role::inRandomOrder()->first()->id,

            'profile_photo_path' => $this->faker->imageUrl(640, 480, 'people', true),
            'address' => $this->faker->address(),
            'city_id' => $this->faker->numberBetween(1,81),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            // 'remember_token' => Str::random(20),
        ];
    }

    // /**
    //  * Configure the model factory.
    //  *
    //  * @return $this
    //  */
    // public function configure()
    // {
    //     return $this->afterCreating(function (User $user) {
    //         $roles = Role::inRandomOrder()->take(rand(1,3))->get();
    //         $user->roles()->sync($roles->pluck('id'));
    //     });
    // }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
