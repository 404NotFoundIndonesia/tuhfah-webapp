<?php

namespace App\Http\Controllers;

use App\Enum\Role;
use App\Http\Requests\StoreTeacherRequest;
use App\Http\Requests\UpdateTeacherRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class TeacherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return DataTables::of(User::role(Role::TEACHER))
                ->addColumn('action', function ($row) {
                    return '<div class="dropdown">
                                <button type="button"
                                        class="btn p-0 dropdown-toggle hide-arrow"
                                        data-bs-toggle="dropdown">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="'.route('teacher.edit', $row).'"><i class="bx bx-edit-alt me-1"></i>'.__('label.edit').'</a>
                                    <form action="'.route('teacher.destroy', $row).'" method="post" onsubmit="confirmSubmit(event, this)">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <input type="hidden" name="_token" value="'.csrf_token().'" />
                                        <button class="dropdown-item" type="submit"><i class="bx bx-trash me-1"></i>'.__('label.delete').'</button>
                                    </form>
                                </div>
                            </div>';
                })
                ->editColumn('image', fn ($row) => '<a data-fslightbox href="'.$row->image_url.'"><img src="'.$row->image_url.'" alt="user-avatar" class="d-block rounded" height="30" width="30"></a>')
                ->editColumn('gender', fn ($row) => __('label.'.$row->gender))
                ->rawColumns(['action', 'image'])
                ->make();
        }

        return view('pages.teacher.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('pages.teacher.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTeacherRequest $request): RedirectResponse
    {
        try {
            $data = $request->validated();
            $data['role'] = Role::TEACHER;
            $data['password'] = bcrypt('password');

            if ($request->hasFile('image')) {
                $data['image'] = time().random_int(0, PHP_INT_MAX).'.'.$request->file('image')->extension();
                Storage::putFileAs('public', $request->file('image'), $data['image']);
            }

            User::create($data);

            return redirect()->route('teacher.index')->with('notification', $this->successNotification('notification.success_create', 'menu.teacher'));
        } catch (\Throwable $throwable) {
            Log::error($throwable->getMessage());

            return back()->with('notification', $this->successNotification('notification.fail_create', 'menu.teacher'));
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $teacher): View
    {
        return view('pages.teacher.show', [
            'teacher' => $teacher,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $teacher): View
    {
        return view('pages.teacher.edit', [
            'teacher' => $teacher,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTeacherRequest $request, User $teacher): RedirectResponse
    {
        try {
            $data = $request->validated();

            if ($request->hasFile('image')) {
                if ($teacher->image) {
                    Storage::delete("public/{$teacher->image}");
                }

                $data['image'] = time().random_int(0, PHP_INT_MAX).'.'.$request->file('image')->extension();
                Storage::putFileAs('public', $request->file('image'), $data['image']);
            }

            $teacher->update($data);

            return back()->with('notification', $this->successNotification('notification.success_update', 'menu.teacher'));
        } catch (\Throwable $throwable) {
            Log::error($throwable->getMessage());

            return back()->with('notification', $this->successNotification('notification.fail_update', 'menu.teacher'));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $teacher): RedirectResponse
    {
        try {
            $teacher->delete();

            return back()->with('notification', $this->successNotification('notification.success_delete', 'menu.teacher'));
        } catch (\Throwable $throwable) {
            Log::error($throwable->getMessage());

            return back()->with('notification', $this->successNotification('notification.fail_delete', 'menu.teacher'));
        }
    }
}
