<?php

namespace App\Http\Controllers;

use App\Enum\Role;
use App\Enum\StudentStatus;
use App\Http\Requests\StoreLearningProgressRequest;
use App\Http\Requests\UpdateLearningProgressRequest;
use App\Models\LearningProgress;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class LearningProgressController extends Controller
{
    private function authorizeRead(): void
    {
        abort_unless(
            auth()->user()->isRole(Role::OWNER)
                || auth()->user()->isRole(Role::HEADMASTER)
                || auth()->user()->isRole(Role::ADMINISTRATOR)
                || auth()->user()->isRole(Role::TEACHER),
            403
        );
    }

    private function authorizeRecord(LearningProgress $progress): void
    {
        $user = auth()->user();

        if ($user->isRole(Role::TEACHER)) {
            abort_unless($progress->teacher_id === $user->id, 403);
        } else {
            abort_unless(
                $user->isRole(Role::OWNER)
                    || $user->isRole(Role::HEADMASTER)
                    || $user->isRole(Role::ADMINISTRATOR),
                403
            );
        }
    }

    // ── Index (T3.2 + T3.3) ──────────────────────────────────────────────────

    public function index(Request $request): View|JsonResponse
    {
        $this->authorizeRead();

        $user = auth()->user();

        if ($request->ajax()) {
            $query = LearningProgress::with(['student', 'teacher'])
                ->when($user->isRole(Role::TEACHER), fn ($q) => $q->where('teacher_id', $user->id))
                ->orderByDesc('date');

            return DataTables::eloquent($query)
                ->addColumn('student_name', fn ($row) => optional($row->student)->name ?? '-')
                ->addColumn('teacher_name', fn ($row) => optional($row->teacher)->name ?? '-')
                ->editColumn('date', fn ($row) => $row->date->format('Y-m-d'))
                ->editColumn('score', fn ($row) => $row->score !== null ? number_format($row->score, 1) : '-')
                ->addColumn('action', function ($row) use ($user) {
                    $canEdit = $user->isRole(Role::OWNER)
                        || $user->isRole(Role::HEADMASTER)
                        || $user->isRole(Role::ADMINISTRATOR)
                        || ($user->isRole(Role::TEACHER) && $row->teacher_id === $user->id);

                    if (! $canEdit) {
                        return '';
                    }

                    return '<div class="dropdown">
                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="'.route('learning-progress.edit', $row).'">
                                <i class="bx bx-edit-alt me-1"></i>'.__('label.edit').'
                            </a>
                            <form action="'.route('learning-progress.destroy', $row).'" method="post" onsubmit="confirmSubmit(event, this)">
                                <input type="hidden" name="_method" value="DELETE">
                                <input type="hidden" name="_token" value="'.csrf_token().'" />
                                <button class="dropdown-item" type="submit">
                                    <i class="bx bx-trash me-1"></i>'.__('label.delete').'
                                </button>
                            </form>
                        </div>
                    </div>';
                })
                ->filterColumn('student_name', fn ($q, $k) => $q->whereHas('student', fn ($sq) => $sq->where('name', 'like', "%{$k}%")))
                ->filterColumn('teacher_name', fn ($q, $k) => $q->whereHas('teacher', fn ($sq) => $sq->where('name', 'like', "%{$k}%")))
                ->rawColumns(['action'])
                ->toJson();
        }

        $students = Student::where('status', StudentStatus::ACTIVE->value)->orderBy('name')->get();

        return view('pages.learning-progress.index', compact('students'));
    }

    // ── Create / Store ────────────────────────────────────────────────────────

    public function create(): View
    {
        $this->authorizeRead();

        $students = Student::where('status', StudentStatus::ACTIVE->value)->orderBy('name')->get();

        return view('pages.learning-progress.create', compact('students'));
    }

    public function store(StoreLearningProgressRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['teacher_id'] = auth()->id();

        LearningProgress::create($data);

        return redirect()->route('learning-progress.index')
            ->with('notification', $this->successNotification('notification.success_create', 'menu.learning_progress'));
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    public function show(LearningProgress $learningProgress): View
    {
        $this->authorizeRead();

        $user = auth()->user();
        if ($user->isRole(Role::TEACHER)) {
            abort_unless($learningProgress->teacher_id === $user->id, 403);
        }

        return view('pages.learning-progress.show', [
            'progress' => $learningProgress->load(['student', 'teacher']),
        ]);
    }

    // ── Edit / Update ─────────────────────────────────────────────────────────

    public function edit(LearningProgress $learningProgress): View
    {
        $this->authorizeRecord($learningProgress);

        $students = Student::where('status', StudentStatus::ACTIVE->value)->orderBy('name')->get();

        return view('pages.learning-progress.edit', [
            'progress' => $learningProgress->load(['student', 'teacher']),
            'students' => $students,
        ]);
    }

    public function update(UpdateLearningProgressRequest $request, LearningProgress $learningProgress): RedirectResponse
    {
        $this->authorizeRecord($learningProgress);

        $data = $request->validated();
        // teacher_id stays unchanged on update
        $learningProgress->update($data);

        return redirect()->route('learning-progress.index')
            ->with('notification', $this->successNotification('notification.success_update', 'menu.learning_progress'));
    }

    // ── Destroy ───────────────────────────────────────────────────────────────

    public function destroy(LearningProgress $learningProgress): RedirectResponse
    {
        $this->authorizeRecord($learningProgress);

        $learningProgress->delete();

        return back()
            ->with('notification', $this->successNotification('notification.success_delete', 'menu.learning_progress'));
    }

    // ── Guardian: View Child Progress (T3.4) ──────────────────────────────────

    public function guardianIndex(Request $request): View
    {
        abort_unless(auth()->user()->isRole(Role::STUDENT_GUARDIAN), 403);

        $studentIds = Student::where('student_guardian_id', auth()->id())->pluck('id');

        $records = LearningProgress::with(['student', 'teacher'])
            ->whereIn('student_id', $studentIds)
            ->when($request->query('subject'), fn ($q, $s) => $q->where('subject', $s))
            ->orderByDesc('date')
            ->paginate(20)
            ->withQueryString();

        $subjects = LearningProgress::whereIn('student_id', $studentIds)
            ->distinct()
            ->pluck('subject');

        return view('pages.learning-progress.guardian', compact('records', 'subjects'));
    }

    // ── Chart Data (T3.5) ────────────────────────────────────────────────────

    public function chartData(Request $request): JsonResponse
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
            'subject' => ['nullable', 'string'],
        ]);

        $student = Student::findOrFail($request->student_id);

        if ($user->isRole(Role::STUDENT_GUARDIAN)) {
            abort_unless($student->student_guardian_id === $user->id, 403);
        }

        $data = LearningProgress::where('student_id', $student->id)
            ->when($request->subject, fn ($q, $s) => $q->where('subject', $s))
            ->whereNotNull('score')
            ->orderBy('date')
            ->get(['date', 'score', 'subject'])
            ->map(fn ($r) => [
                'date' => $r->date->format('Y-m-d'),
                'score' => $r->score,
                'subject' => $r->subject,
            ]);

        return response()->json($data);
    }
}
