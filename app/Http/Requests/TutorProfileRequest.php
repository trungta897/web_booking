<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class TutorProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->role === 'tutor';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'hourly_rate' => 'required|numeric|min:0.01|max:1000',
            'experience_years' => 'required|integer|min:0|max:50',
            'bio' => 'required|string|max:1000|min:50',
            'specialization' => 'nullable|string|max:255',
            'is_available' => 'sometimes|boolean',
            'subjects' => 'required|array|min:1',
            'subjects.*' => 'exists:subjects,id',
            'education' => 'sometimes|array',
            'education.*.degree' => 'required_with:education|string|max:255',
            'education.*.institution' => 'required_with:education|string|max:255',
            'education.*.year' => 'nullable|string|max:50',
        ];
    }

    /**
     * Get custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'hourly_rate' => __('hourly rate'),
            'experience_years' => __('years of experience'),
            'bio' => __('biography'),
            'specialization' => __('specialization'),
            'is_available' => __('availability status'),
            'subjects' => __('subjects'),
            'education.*.degree' => __('degree'),
            'education.*.institution' => __('institution'),
            'education.*.field_of_study' => __('field of study'),
            'education.*.start_year' => __('start year'),
            'education.*.end_year' => __('end year'),
            'education.*.description' => __('description'),
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'hourly_rate.required' => __('Please enter your hourly rate'),
            'hourly_rate.numeric' => __('Hourly rate must be a number'),
            'hourly_rate.min' => __('Hourly rate must be at least 0.01'),
            'hourly_rate.max' => __('Hourly rate cannot exceed 1000'),
            'experience_years.required' => __('Please enter your years of experience'),
            'experience_years.integer' => __('Years of experience must be a whole number'),
            'experience_years.min' => __('Years of experience cannot be negative'),
            'experience_years.max' => __('Years of experience cannot exceed 50'),
            'bio.required' => __('Please write a biography'),
            'bio.min' => __('Biography must be at least 50 characters'),
            'bio.max' => __('Biography must not exceed 1000 characters'),
            'subjects.required' => __('Please select at least one subject'),
            'subjects.min' => __('Please select at least one subject'),
            'subjects.*.exists' => __('Selected subject is invalid'),
            'education.*.degree.required_with' => __('Degree is required when education is provided'),
            'education.*.institution.required_with' => __('Institution is required when education is provided'),
            'education.*.start_year.required_with' => __('Start year is required when education is provided'),
        ];
    }
}
