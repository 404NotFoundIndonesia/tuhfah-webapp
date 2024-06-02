<?php

namespace Database\Seeders\Dummy;

use App\Enum\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
            'role' => Role::HEADMASTER,
        ]);

        User::factory()->create([
            'role' => Role::ADMINISTRATOR,
        ]);

        User::factory(10)->create([
            'role' => Role::TEACHER,
        ]);

        User::factory(100)->create([
            'role' => Role::STUDENT_GUARDIAN,
        ]);
    }
}
