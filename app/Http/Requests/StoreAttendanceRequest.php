<?php

namespace App\Http\Requests;

use App\Enum\AttendanceStatus;
use App\Enum\Role;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->isRole(Role::OWNER)
            || auth()->user()->isRole(Role::HEADMASTER)
            || auth()->user()->isRole(Role::ADMINISTRATOR)
            || auth()->user()->isRole(Role::TEACHER);
    }

    /**
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'records' => ['required', 'array'],
            'records.*.status' => ['required', Rule::in(array_column(AttendanceStatus::cases(), 'value'))],
            'records.*.notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function attributes(): array
    {
        return [
            'date' => __('field.date'),
            'records' => __('menu.attendance'),
            'records.*.status' => __('field.status'),
            'records.*.notes' => __('field.notes'),
        ];
    }
}
