<?php

namespace App\Services;

use App\Enum\AttendanceStatus;
use App\Enum\PaymentStatus;
use App\Enum\Role;
use App\Enum\StudentStatus;
use App\Models\Attendance;
use App\Models\LearningProgress;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;

class DashboardService
{
    public function stats(User $user): array
    {
        $stats = [
            'total_active_students' => Student::where('status', StudentStatus::ACTIVE->value)->count(),
        ];

        match (Role::tryFrom($user->role)) {
            Role::OWNER, Role::HEADMASTER, Role::ADMINISTRATOR => $stats += $this->adminStats(),
            Role::TEACHER => $stats += $this->teacherStats($user),
            Role::STUDENT_GUARDIAN => $stats += $this->guardianStats($user),
            default => null,
        };

        return $stats;
    }

    private function adminStats(): array
    {
        $today = now()->toDateString();

        $todayStudentAttendances = Attendance::where('attendable_type', Student::class)
            ->whereDate('date', $today)
            ->selectRaw('status, count(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status');

        $presentToday = (int) ($todayStudentAttendances[AttendanceStatus::PRESENT->value] ?? 0);
        $totalToday = $todayStudentAttendances->sum();
        $attendanceRateToday = $totalToday > 0 ? round($presentToday / $totalToday * 100, 1) : null;

        return [
            'attendance_rate_today' => $attendanceRateToday,
            'total_recorded_today' => $totalToday,
            'total_unpaid_payments' => Payment::where('status', PaymentStatus::UNPAID->value)->count(),
            'total_overdue_payments' => Payment::where('status', PaymentStatus::OVERDUE->value)->count(),
        ];
    }

    private function teacherStats(User $teacher): array
    {
        $progressStudentsThisMonth = LearningProgress::where('teacher_id', $teacher->id)
            ->whereYear('date', now()->year)
            ->whereMonth('date', now()->month)
            ->distinct('student_id')
            ->count('student_id');

        return [
            'progress_students_this_month' => $progressStudentsThisMonth,
        ];
    }

    private function guardianStats(User $guardian): array
    {
        $student = Student::where('student_guardian_id', $guardian->id)->first();

        if (! $student) {
            return [
                'child_attendance_rate_this_month' => null,
                'outstanding_payment_count' => 0,
            ];
        }

        $records = Attendance::where('attendable_type', Student::class)
            ->where('attendable_id', $student->id)
            ->whereYear('date', now()->year)
            ->whereMonth('date', now()->month)
            ->selectRaw('status, count(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status');

        $present = (int) ($records[AttendanceStatus::PRESENT->value] ?? 0);
        $total = $records->sum();
        $rate = $total > 0 ? round($present / $total * 100, 1) : null;

        $outstanding = Payment::where('student_id', $student->id)
            ->whereIn('status', [PaymentStatus::UNPAID->value, PaymentStatus::OVERDUE->value])
            ->count();

        return [
            'child_attendance_rate_this_month' => $rate,
            'outstanding_payment_count' => $outstanding,
        ];
    }
}
