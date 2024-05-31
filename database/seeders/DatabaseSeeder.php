<?php

namespace Database\Seeders;

use App\Enum\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Tuhfah Owner',
            'email' => 'owner@tuhfah.com',
            'role' => Role::OWNER,
        ]);
    }
}
