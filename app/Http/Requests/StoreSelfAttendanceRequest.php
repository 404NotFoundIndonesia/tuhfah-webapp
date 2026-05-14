<?php

namespace App\Http\Requests;

use App\Enum\AttendanceStatus;
use App\Enum\Role;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSelfAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->isRole(Role::TEACHER);
    }

    /**
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'date' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    $exists = Attendance::where('attendable_type', User::class)
                        ->where('attendable_id', auth()->id())
                        ->whereDate('date', $value)
                        ->exists();

                    if ($exists) {
                        $fail(__('label.attendance_already_submitted'));
                    }
                },
            ],
            'status' => ['required', Rule::in(array_column(AttendanceStatus::cases(), 'value'))],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function attributes(): array
    {
        return [
            'date' => __('field.date'),
            'status' => __('field.status'),
            'notes' => __('field.notes'),
        ];
    }
}
