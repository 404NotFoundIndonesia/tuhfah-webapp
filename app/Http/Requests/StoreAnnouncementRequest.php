<?php

namespace App\Http\Requests;

use App\Enum\AnnouncementScope;
use App\Enum\Role;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAnnouncementRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'scope' => ['required', Rule::in(array_column(AnnouncementScope::cases(), 'value'))],
            'published_at' => ['nullable', 'date'],
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => __('field.title'),
            'body' => __('field.body'),
            'scope' => __('field.scope'),
            'published_at' => __('field.published_at'),
        ];
    }
}
