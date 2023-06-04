<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'User',
                'slug' => 'user',
            ],
            [
                'name' => 'Club Manager',
                'slug' => 'club_manager',
            ],
            [
                'name' => 'Admin',
                'slug' => 'admin',
            ],
        ];

        foreach ($roles as $role) {
            if (! Role::where('slug', $role['slug'])->exists()) {
                Role::create([
                    'name' => $role['name'],
                    'slug' => $role['slug'],
                ]);
            }
        }
    }
}
