<?php

namespace App\Http\Requests;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class BookingRequest extends FormRequest
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
            'subject_id' => 'required|exists:subjects,id',
            'start_time' => [
                'required',
                'date',
                'after:now',
                function ($attribute, $value, $fail) {
                    $startTime = Carbon::parse($value, 'Asia/Ho_Chi_Minh');
                    $now = Carbon::now('Asia/Ho_Chi_Minh');

                    if ($startTime->lt($now->copy()->addMinutes(30))) {
                        $fail(__('booking.validation.booking_advance_notice'));
                    }
                },
            ],
            'end_time' => [
                'required',
                'date',
                'after:start_time',
                function ($attribute, $value, $fail) {
                    $startTime = Carbon::parse($this->start_time, 'Asia/Ho_Chi_Minh');
                    $endTime = Carbon::parse($value, 'Asia/Ho_Chi_Minh');
                    if ($endTime->diffInHours($startTime) > 4) {
                        $fail(__('booking.validation.max_duration'));
                    }
                },
            ],
            'notes' => 'nullable|string|max:500',
            'status' => 'sometimes|in:' . Booking::STATUS_ACCEPTED . ',' . Booking::STATUS_REJECTED . ',' . Booking::STATUS_CANCELLED,
            'meeting_link' => 'nullable|url|max:255',
            'rejection_reason' => 'nullable|string|max:100',
            'rejection_description' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'subject_id' => __('subject'),
            'start_time' => __('start time'),
            'end_time' => __('end time'),
            'notes' => __('notes'),
            'status' => __('status'),
            'meeting_link' => __('meeting link'),
            'rejection_reason' => __('rejection reason'),
            'rejection_description' => __('rejection description'),
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'subject_id.required' => __('Please select a subject'),
            'subject_id.exists' => __('Selected subject is invalid'),
            'start_time.required' => __('Start time is required'),
            'start_time.after' => __('Start time must be in the future'),
            'end_time.required' => __('End time is required'),
            'end_time.after' => __('End time must be after start time'),
            'notes.max' => __('Notes must not exceed 500 characters'),
            'meeting_link.url' => __('Meeting link must be a valid URL'),
            'meeting_link.max' => __('Meeting link must not exceed 255 characters'),
        ];
    }
}
