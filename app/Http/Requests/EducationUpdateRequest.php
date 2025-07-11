<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EducationUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only allow authenticated tutors to make this request
        return $this->user()->role === 'tutor' && $this->user()->tutor;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'education' => ['nullable', 'array'],
            'education.*.degree' => ['required', 'string', 'max:255'],
            'education.*.institution' => ['required', 'string', 'max:255'],
            'education.*.year' => ['nullable', 'string', 'max:50'],
            'education.*.image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'], // 5MB
        ];
    }
}
