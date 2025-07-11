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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'], // Increased to 5MB
        ];

                // Add tutor and education validation for tutors
        if ($this->user()->role === 'tutor' && $this->user()->tutor) {
            // Tutor profile fields - remove 'sometimes' to always validate when present
            $rules['hourly_rate'] = ['nullable', 'numeric', 'min:0', 'max:9999999'];
            $rules['experience_years'] = ['nullable', 'integer', 'min:0', 'max:50'];
            $rules['bio'] = ['nullable', 'string', 'max:1000'];
            $rules['subjects'] = ['nullable', 'array'];
            $rules['subjects.*'] = ['integer', 'exists:subjects,id'];

            // Education validation
            $rules['education'] = ['nullable', 'array'];
            $rules['education.*.id'] = ['nullable', 'integer', 'exists:education,id'];
            $rules['education.*.degree'] = ['required', 'string', 'max:255'];
            $rules['education.*.institution'] = ['required', 'string', 'max:255'];
            $rules['education.*.year'] = ['nullable', 'string', 'max:50'];
            $rules['education.*.new_images'] = ['nullable', 'array'];
            $rules['education.*.new_images.*'] = ['image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120']; // 5MB per image
        }

        return $rules;
    }
}
