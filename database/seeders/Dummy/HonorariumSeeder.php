<?php

namespace Database\Seeders\Dummy;

use App\Enum\PaymentStatus;
use App\Enum\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class HonorariumSeeder extends Seeder
{
    public function run(): void
    {
        $teachers = User::role(Role::TEACHER)->get();
        $admin = User::role(Role::ADMINISTRATOR)->first();

        if (! $admin || $teachers->isEmpty()) {
            return;
        }

        $amounts = [500000, 750000, 1000000, 1500000];
        $statusPool = [
            PaymentStatus::PAID->value,
            PaymentStatus::PAID->value,
            PaymentStatus::PAID->value,
            PaymentStatus::UNPAID->value,
            PaymentStatus::OVERDUE->value,
        ];

        $rows = [];
        $now = Carbon::now();

        foreach ($teachers as $teacher) {
            for ($monthsAgo = 5; $monthsAgo >= 0; $monthsAgo--) {
                $period = $now->copy()->subMonths($monthsAgo)->format('Y-m');
                $status = $statusPool[array_rand($statusPool)];

                $rows[] = [
                    'teacher_id' => $teacher->id,
                    'period' => $period,
                    'amount' => $amounts[array_rand($amounts)],
                    'status' => $status,
                    'paid_at' => $status === PaymentStatus::PAID->value ? $now->copy()->subMonths($monthsAgo)->day(20)->toDateTimeString() : null,
                    'recorded_by' => $admin->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('honorariums')->insert($chunk);
        }
    }
}
