<?php

namespace App\Http\Requests;

use App\Enum\Role;
use App\Enum\StudentStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->isRole(Role::OWNER) || auth()->user()->isRole(Role::HEADMASTER) || auth()->user()->isRole(Role::ADMINISTRATOR);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'student_id_number' => ['nullable', Rule::unique('students', 'student_id_number')],
            'name' => ['required'],
            'nickname' => ['nullable'],
            'birthplace' => ['nullable'],
            'birthdate' => ['required', 'date'],
            'gender' => ['required'],
            'status' => ['required'],
            'admission_date' => ['required', 'date'],
            'departure_date' => ['nullable', Rule::requiredIf(StudentStatus::isIncluded($this->status, StudentStatus::EXPELLED, StudentStatus::QUIT, StudentStatus::ON_LEAVE, StudentStatus::GRADUATED)), 'after:admission_date'],
            'image' => ['nullable', 'image'],
            'student_guardian_id' => ['required'],
        ];
    }

    public function attributes(): array
    {
        return [
            'student_id_number' => __('field.student_id_number'),
            'name' => __('field.name'),
            'nickname' => __('field.nickname'),
            'birthplace' => __('field.birthplace'),
            'birthdate' => __('field.birthdate'),
            'gender' => __('field.gender'),
            'status' => __('field.status'),
            'admission_date' => __('field.admission_date'),
            'departure_date' => __('field.departure_date'),
            'image' => __('field.image'),
            'student_guardian_id' => __('field.student_guardian_id'),
        ];
    }
}
