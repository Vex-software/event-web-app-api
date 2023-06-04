<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class EventCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\EventCategory::factory()->count(10)->create();
    }
}
