<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class MessageRequest extends FormRequest
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
            'receiver_id' => 'required|exists:users,id|different:' . Auth::id(),
            'message' => 'required|string|max:1000|min:1',
        ];
    }

    /**
     * Get custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'receiver_id' => __('recipient'),
            'message' => __('message'),
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'receiver_id.required' => __('Please select a recipient'),
            'receiver_id.exists' => __('Selected recipient is invalid'),
            'receiver_id.different' => __('You cannot send a message to yourself'),
            'message.required' => __('Please enter a message'),
            'message.max' => __('Message must not exceed 1000 characters'),
            'message.min' => __('Message cannot be empty'),
        ];
    }
}
