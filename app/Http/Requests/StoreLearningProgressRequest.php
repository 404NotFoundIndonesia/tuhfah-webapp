<?php

namespace App\Http\Requests;

use App\Enum\Role;
use App\Enum\StudentStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLearningProgressRequest extends FormRequest
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
            'student_id' => [
                'required',
                Rule::exists('students', 'id')->where('status', StudentStatus::ACTIVE->value),
            ],
            'date' => ['required', 'date'],
            'subject' => ['required', 'string', 'max:255'],
            'milestone' => ['required', 'string', 'max:500'],
            'score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'student_id' => __('field.student_id'),
            'date' => __('field.date'),
            'subject' => __('field.subject'),
            'milestone' => __('field.milestone'),
            'score' => __('field.score'),
            'notes' => __('field.notes'),
        ];
    }
}
