<?php

namespace Database\Seeders\Dummy;

use App\Enum\PaymentStatus;
use App\Enum\Role;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $students = Student::all();
        $admin = User::role(Role::ADMINISTRATOR)->first();

        if (! $admin || $students->isEmpty()) {
            return;
        }

        $amounts = [150000, 200000, 250000, 300000];
        $statusPool = [
            PaymentStatus::PAID->value,
            PaymentStatus::PAID->value,
            PaymentStatus::PAID->value,
            PaymentStatus::UNPAID->value,
            PaymentStatus::OVERDUE->value,
        ];

        $rows = [];
        $now = Carbon::now();

        foreach ($students as $student) {
            for ($monthsAgo = 5; $monthsAgo >= 0; $monthsAgo--) {
                $period = $now->copy()->subMonths($monthsAgo)->format('Y-m');
                $status = $statusPool[array_rand($statusPool)];
                $dueDate = $now->copy()->subMonths($monthsAgo)->endOfMonth()->toDateString();

                $rows[] = [
                    'student_id' => $student->id,
                    'period' => $period,
                    'amount' => $amounts[array_rand($amounts)],
                    'status' => $status,
                    'due_date' => $dueDate,
                    'paid_at' => $status === PaymentStatus::PAID->value ? $now->copy()->subMonths($monthsAgo)->day(15)->toDateTimeString() : null,
                    'recorded_by' => $admin->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('payments')->insert($chunk);
        }
    }
}
