<?php

namespace Database\Seeders\Dummy;

use App\Enum\Role;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $guardianIds = User::role(Role::STUDENT_GUARDIAN)->pluck('id')->toArray();

        foreach ($guardianIds as $id) {
            Student::factory(rand(0, 3))->create([
                'student_guardian_id' => $id,
            ]);
        }
    }
}
