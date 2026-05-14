<?php

namespace App\Http\Requests;

use App\Enum\Role;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LogInventoryRequest extends FormRequest
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
            'type' => ['required', Rule::in(['usage', 'disposal'])],
            'quantity_changed' => [
                'required',
                'integer',
                'min:1',
                'max:'.$this->route('inventory')->quantity,
            ],
            'reason' => ['required', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'type' => __('field.type'),
            'quantity_changed' => __('field.quantity_changed'),
            'reason' => __('field.reason'),
        ];
    }
}
