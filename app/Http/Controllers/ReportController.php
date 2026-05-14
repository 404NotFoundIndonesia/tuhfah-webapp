<?php

namespace App\Http\Controllers;

use App\Enum\AttendanceStatus;
use App\Enum\PaymentStatus;
use App\Enum\Role;
use App\Enum\StudentStatus;
use App\Models\Attendance;
use App\Models\LearningProgress;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    private function authorizeAdmin(): void
    {
        abort_unless(
            auth()->user()->isRole(Role::OWNER)
                || auth()->user()->isRole(Role::HEADMASTER)
                || auth()->user()->isRole(Role::ADMINISTRATOR),
            403
        );
    }

    // ── T8.2 Attendance Report ─────────────────────────────────────────────────

    public function attendance(Request $request): View
    {
        $this->authorizeAdmin();

        $period = $request->input('period', 'monthly');
        $date = $request->input('date', now()->toDateString());
        $rows = [];

        if ($request->hasAny(['period', 'date'])) {
            $rows = $this->buildAttendanceReport($period, $date);
        }

        return view('pages.report.attendance', compact('period', 'date', 'rows'));
    }

    public function attendanceExport(Request $request): Response|BinaryFileResponse
    {
        $this->authorizeAdmin();

        $period = $request->input('period', 'monthly');
        $date = $request->input('date', now()->toDateString());
        $format = $request->input('format', 'xlsx');
        $rows = $this->buildAttendanceReport($period, $date);

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('exports.attendance-report-pdf', compact('rows', 'period', 'date'))
                ->setPaper('a4', 'landscape');

            return $pdf->download('attendance-report-'.$date.'.pdf');
        }

        return Excel::download(
            new \App\Exports\AttendanceReportExport($rows),
            'attendance-report-'.$date.'.xlsx'
        );
    }

    // ── T8.3 Financial Report ─────────────────────────────────────────────────

    public function finance(Request $request): View
    {
        $this->authorizeAdmin();

        $period = $request->input('period');
        $summary = $period ? $this->buildFinanceSummary($period) : null;

        return view('pages.report.finance', compact('period', 'summary'));
    }

    public function financeExport(Request $request): Response|BinaryFileResponse
    {
        $this->authorizeAdmin();

        $period = $request->input('period', now()->format('Y-m'));
        $format = $request->input('format', 'xlsx');
        $summary = $this->buildFinanceSummary($period);

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('exports.finance-report-pdf', compact('summary', 'period'));

            return $pdf->download('finance-report-'.$period.'.pdf');
        }

        return Excel::download(
            new \App\Exports\FinanceReportExport($summary, $period),
            'finance-report-'.$period.'.xlsx'
        );
    }

    // ── T8.4 Progress Report ──────────────────────────────────────────────────

    public function progress(Request $request): View
    {
        $this->authorizeProgressAccess();

        $studentId = $request->input('student_id');
        $from = $request->input('from');
        $to = $request->input('to');

        $students = $this->accessibleStudents();
        $records = collect();

        if ($studentId && $from && $to) {
            $records = $this->buildProgressReport($studentId, $from, $to);
        }

        $student = $studentId ? Student::find($studentId) : null;

        return view('pages.report.progress', compact('students', 'studentId', 'from', 'to', 'records', 'student'));
    }

    public function progressExport(Request $request): Response|BinaryFileResponse
    {
        $this->authorizeProgressAccess();

        $studentId = $request->input('student_id');
        $from = $request->input('from');
        $to = $request->input('to');

        abort_if(! $studentId || ! $from || ! $to, 422);

        $records = $this->buildProgressReport($studentId, $from, $to);
        $student = Student::findOrFail($studentId);

        $pdf = Pdf::loadView('exports.progress-report-pdf', compact('records', 'student', 'from', 'to'));

        return $pdf->download('progress-report-'.$student->name.'.pdf');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function buildAttendanceReport(string $period, string $date): array
    {
        $parsedDate = \Carbon\Carbon::parse($date);

        if ($period === 'weekly') {
            $start = $parsedDate->copy()->startOfWeek();
            $end = $parsedDate->copy()->endOfWeek();
        } else {
            $start = $parsedDate->copy()->startOfMonth();
            $end = $parsedDate->copy()->endOfMonth();
        }

        $students = Student::where('status', StudentStatus::ACTIVE->value)->orderBy('name')->get();

        $attendances = Attendance::where('attendable_type', Student::class)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('attendable_id, status, count(*) as cnt')
            ->groupBy('attendable_id', 'status')
            ->get()
            ->groupBy('attendable_id');

        $rows = [];
        foreach ($students as $student) {
            $records = $attendances->get($student->id, collect());
            $counts = [
                AttendanceStatus::PRESENT->value => 0,
                AttendanceStatus::ABSENT->value => 0,
                AttendanceStatus::SICK->value => 0,
                AttendanceStatus::PERMITTED->value => 0,
            ];
            foreach ($records as $r) {
                $counts[$r->status] = (int) $r->cnt;
            }
            $total = array_sum($counts);
            $rows[] = [
                'student' => $student,
                'present' => $counts[AttendanceStatus::PRESENT->value],
                'absent' => $counts[AttendanceStatus::ABSENT->value],
                'sick' => $counts[AttendanceStatus::SICK->value],
                'permitted' => $counts[AttendanceStatus::PERMITTED->value],
                'total' => $total,
                'percentage' => $total > 0
                    ? round($counts[AttendanceStatus::PRESENT->value] / $total * 100, 1)
                    : 0,
            ];
        }

        return $rows;
    }

    private function buildFinanceSummary(string $period): array
    {
        $payments = DB::table('payments')
            ->where('period', $period)
            ->selectRaw('status, count(*) as count, coalesce(sum(amount), 0) as total')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $paidAmount = (float) ($payments[PaymentStatus::PAID->value]->total ?? 0);
        $unpaidAmount = (float) ($payments[PaymentStatus::UNPAID->value]->total ?? 0);
        $overdueAmount = (float) ($payments[PaymentStatus::OVERDUE->value]->total ?? 0);

        $honorariumsPaid = (float) DB::table('honorariums')
            ->where('period', $period)
            ->where('status', PaymentStatus::PAID->value)
            ->sum('amount');

        return [
            'period' => $period,
            'total_collected' => $paidAmount,
            'total_outstanding' => $unpaidAmount,
            'total_overdue' => $overdueAmount,
            'total_honorariums_paid' => $honorariumsPaid,
            'net_income' => $paidAmount - $honorariumsPaid,
            'payment_counts' => [
                'paid' => (int) ($payments[PaymentStatus::PAID->value]->count ?? 0),
                'unpaid' => (int) ($payments[PaymentStatus::UNPAID->value]->count ?? 0),
                'overdue' => (int) ($payments[PaymentStatus::OVERDUE->value]->count ?? 0),
            ],
        ];
    }

    private function buildProgressReport(int|string $studentId, string $from, string $to)
    {
        return LearningProgress::where('student_id', $studentId)
            ->whereBetween('date', [$from, $to])
            ->with('teacher')
            ->orderBy('date')
            ->get();
    }

    private function authorizeProgressAccess(): void
    {
        abort_unless(
            auth()->user()->isRole(Role::OWNER)
                || auth()->user()->isRole(Role::HEADMASTER)
                || auth()->user()->isRole(Role::ADMINISTRATOR)
                || auth()->user()->isRole(Role::TEACHER),
            403
        );
    }

    private function accessibleStudents()
    {
        $user = auth()->user();

        if ($user->isRole(Role::TEACHER)) {
            return Student::whereHas('learningProgress', fn ($q) => $q->where('teacher_id', $user->id))
                ->where('status', StudentStatus::ACTIVE->value)
                ->orderBy('name')
                ->get();
        }

        return Student::where('status', StudentStatus::ACTIVE->value)->orderBy('name')->get();
    }
}
