<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Status::create([
            'status_name' => 'Active',
            'status_description' => 'Active',
            'status_color' => 'green',
            'status_icon' => 'check',
            'status_slug' => 'active',
            'status_order' => 1,
        ]);

        Status::create([
            'status_name' => 'Passive',
            'status_description' => 'Passive',
            'status_color' => 'red',
            'status_icon' => 'times',
            'status_slug' => 'passive',
            'status_order' => 2,
        ]);

        Status::create([
            'status_name' => 'Pending',
            'status_description' => 'Pending',
            'status_color' => 'yellow',
            'status_icon' => 'clock',
            'status_slug' => 'pending',
            'status_order' => 3,
        ]);

        Status::create([
            'status_name' => 'Deleted',
            'status_description' => 'Deleted',
            'status_color' => 'red',
            'status_icon' => 'trash',
            'status_slug' => 'deleted',
            'status_order' => 4,
        ]);

        Status::create([
            'status_name' => 'Banned',
            'status_description' => 'Banned',
            'status_color' => 'red',
            'status_icon' => 'ban',
            'status_slug' => 'banned',
            'status_order' => 5,
        ]);

        Status::create([
            'status_name' => 'Draft',
            'status_description' => 'Draft',
            'status_color' => 'yellow',
            'status_icon' => 'pencil',
            'status_slug' => 'draft',
            'status_order' => 6,
        ]);

        Status::create([
            'status_name' => 'Published',
            'status_description' => 'Published',
            'status_color' => 'green',
            'status_icon' => 'check',
            'status_slug' => 'published',
            'status_order' => 7,
        ]);

        Status::create([
            'status_name' => 'Unpublished',
            'status_description' => 'Unpublished',
            'status_color' => 'red',
            'status_icon' => 'times',
            'status_slug' => 'unpublished',
            'status_order' => 8,
        ]);

        Status::create([
            'status_name' => 'Email Not Verified',
            'status_description' => 'Email Not Verified',
            'status_color' => 'red',
            'status_icon' => 'times',
            'status_slug' => 'email-not-verified',
            'status_order' => 9,
        ]);
    }
}
