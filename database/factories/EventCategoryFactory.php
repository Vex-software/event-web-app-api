<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EventCategory>
 */
class EventCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categoryNames = [
            'Kitaplar',
            'Filmler',
            'Müzik',
            'Spor',
            'Yiyecek ve İçecek',
            'Evcil Hayvanlar',
            'Teknoloji',
            'Moda',
            'Seyahat',
            'Oyunlar',
            'Sanat',
            'Güzellik ve Bakım',
            'Eğlence',
            'Otomobil',
            'Çocuklar',
            'Sağlık',
            'Ev ve Bahçe',
            'Diğer',
        ];

        $name = $this->faker->unique()->randomElement($categoryNames);

        return [
            'name' => $name,
        ];
    }
}
