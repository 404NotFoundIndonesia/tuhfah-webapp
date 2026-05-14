<?php

namespace App\Http\Controllers;

use App\Enum\AttendanceStatus;
use App\Enum\Role;
use App\Enum\StudentStatus;
use App\Http\Requests\StoreAttendanceRequest;
use App\Http\Requests\StoreSelfAttendanceRequest;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\User;
use App\Notifications\StudentAbsentNotification;
use App\Services\AttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class AttendanceController extends Controller
{
    public function __construct(private AttendanceService $service)
    {
    }

    private function authorizeAdmin(): void
    {
        abort_unless(
            auth()->user()->isRole(Role::OWNER)
                || auth()->user()->isRole(Role::HEADMASTER)
                || auth()->user()->isRole(Role::ADMINISTRATOR)
                || auth()->user()->isRole(Role::TEACHER),
            403
        );
    }

    // ── Student Attendance ────────────────────────────────────────────────────

    public function index(Request $request): View|JsonResponse
    {
        $this->authorizeAdmin();

        if ($request->ajax()) {
            $query = Attendance::with(['attendable', 'recordedBy'])
                ->where('attendable_type', Student::class)
                ->orderByDesc('date');

            return DataTables::eloquent($query)
                ->addColumn('student_name', fn ($row) => optional($row->attendable)->name ?? '-')
                ->editColumn('date', fn ($row) => $row->date->format('Y-m-d'))
                ->editColumn('status', fn ($row) => __('label.'.$row->status->value))
                ->addColumn('recorder', fn ($row) => optional($row->recordedBy)->name ?? '-')
                ->addColumn('action', fn ($row) => '
                    <a href="'.route('attendance.show', $row->id).'" class="btn btn-sm btn-icon btn-outline-secondary" title="'.__('label.detail').'">
                        <i class="bx bx-show"></i>
                    </a>
                ')
                ->filterColumn('status', fn ($query, $keyword) => $query->where('status', $keyword))
                ->rawColumns(['action'])
                ->toJson();
        }

        return view('pages.attendance.index', [
            'statuses' => AttendanceStatus::cases(),
        ]);
    }

    public function create(Request $request): View
    {
        abort_unless(
            auth()->user()->isRole(Role::OWNER)
                || auth()->user()->isRole(Role::HEADMASTER)
                || auth()->user()->isRole(Role::ADMINISTRATOR)
                || auth()->user()->isRole(Role::TEACHER),
            403
        );

        $date = $request->query('date', today()->toDateString());
        $students = Student::where('status', StudentStatus::ACTIVE->value)->orderBy('name')->get();

        $existing = Attendance::where('attendable_type', Student::class)
            ->whereIn('attendable_id', $students->pluck('id'))
            ->whereDate('date', $date)
            ->get()
            ->keyBy('attendable_id');

        return view('pages.attendance.create', [
            'date' => $date,
            'students' => $students,
            'existing' => $existing,
            'statuses' => AttendanceStatus::cases(),
        ]);
    }

    public function store(StoreAttendanceRequest $request): RedirectResponse
    {
        $recorder = auth()->user();
        $nonPresentStatuses = [
            AttendanceStatus::ABSENT->value,
            AttendanceStatus::SICK->value,
            AttendanceStatus::PERMITTED->value,
        ];

        foreach ($request->records as $studentId => $record) {
            Attendance::updateOrCreate(
                [
                    'attendable_type' => Student::class,
                    'attendable_id' => (int) $studentId,
                    'date' => $request->date,
                ],
                [
                    'status' => $record['status'],
                    'notes' => $record['notes'] ?? null,
                    'recorded_by' => $recorder->id,
                ]
            );

            if (in_array($record['status'], $nonPresentStatuses)) {
                $student = Student::find((int) $studentId);
                if ($student && $student->student_guardian_id) {
                    $guardian = User::find($student->student_guardian_id);
                    if ($guardian) {
                        $guardian->notify(new StudentAbsentNotification(
                            studentName: $student->name,
                            date: $request->date,
                            status: $record['status'],
                            recordedBy: $recorder->name,
                        ));
                    }
                }
            }
        }

        return redirect()->route('attendance.index')
            ->with('notification', $this->successNotification('notification.success_create', 'menu.attendance'));
    }

    public function show(Attendance $attendance): View
    {
        $this->authorizeAdmin();

        return view('pages.attendance.show', [
            'attendance' => $attendance->load(['attendable', 'recordedBy']),
        ]);
    }

    // ── Teacher Self-Attendance ───────────────────────────────────────────────

    public function selfCreate(): View
    {
        abort_unless(auth()->user()->isRole(Role::TEACHER), 403);

        $today = today()->toDateString();
        $existing = Attendance::where('attendable_type', User::class)
            ->where('attendable_id', auth()->id())
            ->whereDate('date', $today)
            ->first();

        return view('pages.attendance.self', [
            'today' => $today,
            'existing' => $existing,
            'statuses' => AttendanceStatus::cases(),
        ]);
    }

    public function selfStore(StoreSelfAttendanceRequest $request): RedirectResponse
    {
        Attendance::create([
            'attendable_type' => User::class,
            'attendable_id' => auth()->id(),
            'date' => $request->date,
            'status' => $request->status,
            'notes' => $request->notes,
            'recorded_by' => auth()->id(),
        ]);

        return redirect()->route('attendance.self')
            ->with('notification', $this->successNotification('notification.success_create', 'menu.self_attendance'));
    }

    // ── Guardian: Child Attendance ────────────────────────────────────────────

    public function guardianIndex(Request $request): View
    {
        abort_unless(auth()->user()->isRole(Role::STUDENT_GUARDIAN), 403);

        $students = Student::where('student_guardian_id', auth()->id())->get();
        $studentIds = $students->pluck('id');

        $year = (int) $request->query('year', today()->year);
        $month = (int) $request->query('month', today()->month);

        $attendances = Attendance::where('attendable_type', Student::class)
            ->whereIn('attendable_id', $studentIds)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->with('attendable')
            ->orderByDesc('date')
            ->get();

        return view('pages.attendance.guardian', [
            'attendances' => $attendances,
            'students' => $students,
            'year' => $year,
            'month' => $month,
        ]);
    }

    // ── Monthly Summary ───────────────────────────────────────────────────────

    public function summary(Request $request): JsonResponse
    {
        $user = auth()->user();

        abort_unless(
            $user->isRole(Role::OWNER)
                || $user->isRole(Role::HEADMASTER)
                || $user->isRole(Role::ADMINISTRATOR)
                || $user->isRole(Role::TEACHER)
                || $user->isRole(Role::STUDENT_GUARDIAN),
            403
        );

        $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        $student = Student::findOrFail($request->student_id);

        // Guardian may only see their own child
        if ($user->isRole(Role::STUDENT_GUARDIAN)) {
            abort_unless($student->student_guardian_id === $user->id, 403);
        }

        $summary = $this->service->monthlySummary($student, (int) $request->year, (int) $request->month);

        return response()->json($summary);
    }
}
