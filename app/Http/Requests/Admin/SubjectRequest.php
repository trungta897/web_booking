<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SubjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $subjectId = $this->route('subject') ? $this->route('subject')->id : null;

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('subjects', 'name')->ignore($subjectId),
            ],
            'description' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'sometimes|boolean',
        ];
    }

    /**
     * Get custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'name' => __('subject name'),
            'description' => __('description'),
            'icon' => __('icon'),
            'is_active' => __('status'),
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => __('Subject name is required'),
            'name.unique' => __('Subject name already exists'),
            'name.max' => __('Subject name must not exceed 100 characters'),
            'description.max' => __('Description must not exceed 500 characters'),
            'icon.max' => __('Icon must not exceed 50 characters'),
        ];
    }
}
