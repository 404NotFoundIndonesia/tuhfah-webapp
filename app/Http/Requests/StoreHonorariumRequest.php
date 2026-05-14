<?php

namespace App\Http\Requests;

use App\Enum\Role;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreHonorariumRequest extends FormRequest
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
            'teacher_id' => [
                'required',
                Rule::exists('users', 'id')->where('role', Role::TEACHER->value),
            ],
            'period' => ['required', 'string', 'regex:/^\d{4}-\d{2}$/'],
            'amount' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    public function attributes(): array
    {
        return [
            'teacher_id' => __('field.teacher_id'),
            'period' => __('field.period'),
            'amount' => __('field.amount'),
        ];
    }
}
