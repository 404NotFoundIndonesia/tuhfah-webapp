<?php

namespace App\Http\Controllers;

use App\Enum\Role;
use App\Http\Requests\StoreStudentGuardianRequest;
use App\Http\Requests\UpdateStudentGuardianRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class StudentGuardianController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return DataTables::eloquent(User::role(Role::STUDENT_GUARDIAN))
                ->addColumn('action', function ($row) {
                    return '<div class="dropdown">
                                <button type="button"
                                        class="btn p-0 dropdown-toggle hide-arrow"
                                        data-bs-toggle="dropdown">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="'.route('student-guardian.edit', $row).'"><i class="bx bx-edit-alt me-1"></i>'.__('label.edit').'</a>
                                    <form action="'.route('student-guardian.destroy', $row).'" method="post" onsubmit="confirmSubmit(event, this)">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <input type="hidden" name="_token" value="'.csrf_token().'" />
                                        <button class="dropdown-item" type="submit"><i class="bx bx-trash me-1"></i>'.__('label.delete').'</button>
                                    </form>
                                </div>
                            </div>';
                })
                ->editColumn('image', fn ($row) => '<a data-fslightbox href="'.$row->image_url.'"><img src="'.$row->image_url.'" alt="user-avatar" class="d-block rounded" height="30" width="30"></a>')
                ->editColumn('gender', fn ($row) => __('label.'.$row->gender))
                ->filterColumn('gender', fn ($query, $keyword) => $query->where('gender', $keyword))
                ->rawColumns(['action', 'image'])
                ->toJson();
        }

        return view('pages.student-guardian.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('pages.student-guardian.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStudentGuardianRequest $request): RedirectResponse
    {
        try {
            $data = $request->validated();
            $data['role'] = Role::STUDENT_GUARDIAN;
            $data['password'] = bcrypt('password');

            if ($request->hasFile('image')) {
                $data['image'] = time().random_int(0, PHP_INT_MAX).'.'.$request->file('image')->extension();
                Storage::putFileAs('public', $request->file('image'), $data['image']);
            }

            User::create($data);

            return redirect()->route('student-guardian.index')->with('notification', $this->successNotification('notification.success_create', 'menu.student_guardian'));
        } catch (\Throwable $throwable) {
            Log::error($throwable->getMessage());

            return back()->with('notification', $this->successNotification('notification.fail_create', 'menu.student_guardian'));
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $student_guardian): View
    {
        return view('pages.student-guardian.show', [
            'guardian' => $student_guardian,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $student_guardian): View
    {
        return view('pages.student-guardian.edit', [
            'guardian' => $student_guardian,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStudentGuardianRequest $request, User $student_guardian): RedirectResponse
    {
        try {
            $data = $request->validated();

            if ($request->hasFile('image')) {
                if ($student_guardian->image) {
                    Storage::delete("public/{$student_guardian->image}");
                }

                $data['image'] = time().random_int(0, PHP_INT_MAX).'.'.$request->file('image')->extension();
                Storage::putFileAs('public', $request->file('image'), $data['image']);
            }

            $student_guardian->update($data);

            return back()->with('notification', $this->successNotification('notification.success_update', 'menu.student_guardian'));
        } catch (\Throwable $throwable) {
            Log::error($throwable->getMessage());

            return back()->with('notification', $this->successNotification('notification.fail_update', 'menu.student_guardian'));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $student_guardian): RedirectResponse
    {
        try {
            $student_guardian->delete();

            return back()->with('notification', $this->successNotification('notification.success_delete', 'menu.student_guardian'));
        } catch (\Throwable $throwable) {
            Log::error($throwable->getMessage());

            return back()->with('notification', $this->successNotification('notification.fail_delete', 'menu.student_guardian'));
        }
    }
}
