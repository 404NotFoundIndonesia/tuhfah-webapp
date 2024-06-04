<?php

namespace App\Http\Controllers;

use App\Enum\Role;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return DataTables::eloquent(Student::query())
                ->addColumn('action', function ($row) {
                    return '<div class="dropdown">
                                <button type="button"
                                        class="btn p-0 dropdown-toggle hide-arrow"
                                        data-bs-toggle="dropdown">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="'.route('student.edit', $row).'"><i class="bx bx-edit-alt me-1"></i>'.__('label.edit').'</a>
                                    <form action="'.route('student.destroy', $row).'" method="post" onsubmit="confirmSubmit(event, this)">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <input type="hidden" name="_token" value="'.csrf_token().'" />
                                        <button class="dropdown-item" type="submit"><i class="bx bx-trash me-1"></i>'.__('label.delete').'</button>
                                    </form>
                                </div>
                            </div>';
                })
                ->editColumn('image', fn ($row) => '<a data-fslightbox href="'.$row->image_url.'"><img src="'.$row->image_url.'" alt="user-avatar" class="d-block rounded" height="30" width="30"></a>')
                ->editColumn('gender', fn ($row) => __('label.'.$row->gender))
                ->editColumn('status', fn ($row) => __('label.'.$row->status))
                ->filterColumn('gender', fn ($query, $keyword) => $query->where('gender', $keyword))
                ->rawColumns(['action', 'image'])
                ->toJson();
        }

        return view('pages.student.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('pages.student.create', [
            'guardians' => User::role(Role::STUDENT_GUARDIAN)->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStudentRequest $request): RedirectResponse
    {
        try {
            $data = $request->validated();
            if ($request->hasFile('image')) {
                $data['image'] = time().random_int(0, PHP_INT_MAX).'.'.$request->file('image')->extension();
                Storage::putFileAs('public', $request->file('image'), $data['image']);
            }

            Student::create($data);

            return redirect()->route('student.index')->with('notification', $this->successNotification('notification.success_create', 'menu.student'));
        } catch (\Throwable $throwable) {
            Log::error($throwable->getMessage());

            return back()->with('notification', $this->successNotification('notification.fail_create', 'menu.student'));
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Student $student): View
    {
        return view('pages.student.show', [
            'student' => $student,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Student $student): View
    {
        return view('pages.student.edit', [
            'student' => $student,
            'guardians' => User::role(Role::STUDENT_GUARDIAN)->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStudentRequest $request, Student $student): RedirectResponse
    {
        try {
            $data = $request->validated();
            if ($request->hasFile('image')) {
                if ($student->image) {
                    Storage::delete("public/{$student->image}");
                }

                $data['image'] = time().random_int(0, PHP_INT_MAX).'.'.$request->file('image')->extension();
                Storage::putFileAs('public', $request->file('image'), $data['image']);
            }

            $student->update($data);

            return back()->with('notification', $this->successNotification('notification.success_update', 'menu.student'));
        } catch (\Throwable $throwable) {
            Log::error($throwable->getMessage());

            return back()->with('notification', $this->successNotification('notification.fail_update', 'menu.student'));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Student $student): RedirectResponse
    {
        try {
            $student->delete();

            return back()->with('notification', $this->successNotification('notification.success_delete', 'menu.student'));
        } catch (\Throwable $throwable) {
            Log::error($throwable->getMessage());

            return back()->with('notification', $this->successNotification('notification.fail_delete', 'menu.student'));
        }
    }
}
