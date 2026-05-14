<?php

namespace App\Http\Requests;

use App\Enum\ItemCondition;
use App\Enum\Role;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->isRole(Role::ADMINISTRATOR);
    }

    /**
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:0'],
            'condition' => ['required', Rule::enum(ItemCondition::class)],
            'acquisition_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => __('field.name'),
            'quantity' => __('field.quantity'),
            'condition' => __('field.condition'),
            'acquisition_date' => __('field.acquisition_date'),
            'notes' => __('field.notes'),
        ];
    }
}
