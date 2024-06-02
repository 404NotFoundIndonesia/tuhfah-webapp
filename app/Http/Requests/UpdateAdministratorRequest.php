<?php

namespace App\Http\Requests;

use App\Enum\Role;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAdministratorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->isRole(Role::OWNER) || auth()->user()->isRole(Role::HEADMASTER);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($this->id)],
            'image' => ['nullable', 'image', 'max:2048'],
            'phone' => ['required'],
            'address' => ['nullable'],
            'marital_status' => ['nullable'],
            'gender' => ['required'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => __('field.name'),
            'email' => __('field.email'),
            'image' => __('field.image'),
            'phone' => __('field.phone'),
            'address' => __('field.address'),
            'marital_status' => __('field.marital_status'),
            'gender' => __('field.gender'),
        ];
    }
}
