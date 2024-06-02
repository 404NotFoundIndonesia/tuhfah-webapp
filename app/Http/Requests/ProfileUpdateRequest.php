<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($this->user()->id)],
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
