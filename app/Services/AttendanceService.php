<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Student;

class AttendanceService
{
    public function monthlySummary(Student $student, int $year, int $month): array
    {
        $records = Attendance::where('attendable_type', Student::class)
            ->where('attendable_id', $student->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get();

        $summary = [
            'present' => 0,
            'absent' => 0,
            'sick' => 0,
            'permitted' => 0,
            'total_days' => $records->count(),
        ];

        foreach ($records as $record) {
            $key = $record->status->value;
            if (array_key_exists($key, $summary)) {
                $summary[$key]++;
            }
        }

        return $summary;
    }
}
