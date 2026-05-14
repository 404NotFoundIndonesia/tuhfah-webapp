<?php

namespace App\Http\Requests;

use App\Enum\Role;
use App\Enum\StudentStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->isRole(Role::OWNER)
            || auth()->user()->isRole(Role::HEADMASTER)
            || auth()->user()->isRole(Role::ADMINISTRATOR);
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
            'period' => ['required', 'string', 'regex:/^\d{4}-\d{2}$/'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'due_date' => ['required', 'date'],
        ];
    }

    public function attributes(): array
    {
        return [
            'student_id' => __('field.student_id'),
            'period' => __('field.period'),
            'amount' => __('field.amount'),
            'due_date' => __('field.due_date'),
        ];
    }
}
