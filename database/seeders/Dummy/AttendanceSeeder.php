<?php

namespace Database\Seeders\Dummy;

use App\Enum\AttendanceStatus;
use App\Enum\Role;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $students = Student::all();
        $admin = User::role(Role::ADMINISTRATOR)->first();

        if (! $admin || $students->isEmpty()) {
            return;
        }

        $statuses = AttendanceStatus::cases();
        $weights = [
            AttendanceStatus::PRESENT->value => 70,
            AttendanceStatus::ABSENT->value => 10,
            AttendanceStatus::SICK->value => 10,
            AttendanceStatus::PERMITTED->value => 10,
        ];

        $rows = [];
        $now = Carbon::today();

        foreach ($students as $student) {
            for ($i = 29; $i >= 0; $i--) {
                $date = $now->copy()->subDays($i)->toDateString();
                $rand = rand(1, 100);
                $cumulative = 0;
                $status = AttendanceStatus::PRESENT->value;
                foreach ($weights as $s => $w) {
                    $cumulative += $w;
                    if ($rand <= $cumulative) {
                        $status = $s;
                        break;
                    }
                }

                $rows[] = [
                    'attendable_type' => Student::class,
                    'attendable_id' => $student->id,
                    'date' => $date,
                    'status' => $status,
                    'notes' => null,
                    'recorded_by' => $admin->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Insert in chunks to avoid memory issues; ignore duplicates safely
        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('attendances')->insertOrIgnore($chunk);
        }
    }
}
