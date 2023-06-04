<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory(10)->create();

        // User::create([
        //     'name' => 'John Doe',
        //     'email' => 'johndoe@example.com',
        //     'email_verified_at' => Carbon::now(),
        //     'password' => Hash::make('password'),
        //     'remember_token' => Str::random(10),
        //     'role' => 'admin',
        // ]);

    }
}
