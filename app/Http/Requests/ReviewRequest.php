<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ReviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:500',
            'booking_id' => 'required|exists:bookings,id',
        ];
    }

    /**
     * Get custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'rating' => __('rating'),
            'comment' => __('comment'),
            'booking_id' => __('booking'),
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'rating.required' => __('Please provide a rating'),
            'rating.integer' => __('Rating must be a number'),
            'rating.min' => __('Rating must be at least 1'),
            'rating.max' => __('Rating cannot exceed 5'),
            'comment.required' => __('Please write a comment'),
            'comment.max' => __('Comment must not exceed 500 characters'),
            'booking_id.required' => __('Booking ID is required'),
            'booking_id.exists' => __('Selected booking is invalid'),
        ];
    }
}
