<?php

namespace Database\Seeders\Dummy;

use App\Enum\Role;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LearningProgressSeeder extends Seeder
{
    public function run(): void
    {
        $students = Student::all();
        $teachers = User::role(Role::TEACHER)->get();

        if ($students->isEmpty() || $teachers->isEmpty()) {
            return;
        }

        $subjects = ['Iqra', 'Al-Quran', 'Tajwid', 'Hafalan', 'Fiqih', 'Aqidah'];
        $rows = [];
        $now = Carbon::today();

        foreach ($students as $student) {
            $teacher = $teachers->random();
            // 3 months × ~4 weeks = 12 weekly entries
            for ($week = 11; $week >= 0; $week--) {
                $date = $now->copy()->subWeeks($week)->toDateString();
                $rows[] = [
                    'student_id' => $student->id,
                    'teacher_id' => $teacher->id,
                    'date' => $date,
                    'subject' => $subjects[array_rand($subjects)],
                    'milestone' => fake()->sentence(4),
                    'score' => rand(0, 10) > 3 ? round(rand(600, 1000) / 10, 1) : null,
                    'notes' => rand(0, 3) === 0 ? fake()->sentence() : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('learning_progress')->insert($chunk);
        }
    }
}
