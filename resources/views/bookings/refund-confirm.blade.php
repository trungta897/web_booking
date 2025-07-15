<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('booking.refund_to_student') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <!-- Booking Info -->
                    <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                        <h3 class="font-semibold text-lg mb-3">{{ __('booking.details') }}</h3>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="font-medium">{{ __('common.subject') }}:</span>
                                {{ $booking->subject->name }}
                            </div>
                            <div>
                                <span class="font-medium">{{ __('common.student') }}:</span>
                                {{ $booking->student->name }}
                            </div>
                            <div>
                                <span class="font-medium">{{ __('booking.start_time') }}:</span>
                                {{ $booking->start_time->format('d-m-Y H:i') }}
                            </div>
                            <div>
                                <span class="font-medium">{{ __('booking.end_time') }}:</span>
                                {{ $booking->end_time->format('d-m-Y H:i') }}
                            </div>
                            <div>
                                <span class="font-medium">{{ __('booking.price') }}:</span>
                                <span class="text-green-600 font-semibold">{{ number_format($booking->price, 0, ',', '.') }} VND</span>
                            </div>
                            <div>
                                <span class="font-medium">{{ __('booking.payment_status') }}:</span>
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">
                                    {{ ucfirst($booking->payment_status) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Refund Warning -->
                    <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-yellow-800">
                                    {{ __('booking.refund_warning_title') }}
                                </h3>
                                <div class="mt-2 text-sm text-yellow-700">
                                    <ul class="list-disc pl-5 space-y-1">
                                        <li>{{ __('booking.refund_warning_1') }}</li>
                                        <li>{{ __('booking.refund_warning_2') }}</li>
                                        <li>{{ __('booking.refund_warning_3') }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Refund Form -->
                    <form method="POST" action="{{ route('payments.refund', $booking) }}" id="refundForm">
                        @csrf

                        <!-- Refund Type Selection -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                {{ __('booking.refund_type') }}
                            </label>
                            <div class="space-y-3">
                                <label class="flex items-center">
                                    <input type="radio" name="refund_type" value="full" checked
                                           class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300"
                                           onchange="toggleRefundAmount()">
                                    <span class="ml-3">
                                        <span class="font-medium">{{ __('booking.full_refund') }}</span>
                                        <span class="text-gray-500 text-sm block">
                                            {{ __('booking.full_refund_desc') }} ({{ number_format($booking->price, 0, ',', '.') }} VND)
                                        </span>
                                    </span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="refund_type" value="partial"
                                           class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300"
                                           onchange="toggleRefundAmount()">
                                    <span class="ml-3">
                                        <span class="font-medium">{{ __('booking.partial_refund') }}</span>
                                        <span class="text-gray-500 text-sm block">
                                            {{ __('booking.partial_refund_desc') }}
                                        </span>
                                    </span>
                                </label>
                            </div>
                        </div>

                        <!-- Partial Refund Amount (hidden by default) -->
                        <div id="partialRefundSection" class="mb-6 hidden">
                            <label for="refund_amount" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('booking.refund_amount') }} (VND) <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="number"
                                       name="refund_amount"
                                       id="refund_amount"
                                       class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                       placeholder="Nhập số tiền hoàn..."
                                       min="1000"
                                       max="{{ $booking->price }}"
                                       step="1000">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">VND</span>
                                </div>
                            </div>
                            <p class="mt-1 text-sm text-gray-500">
                                {{ __('booking.refund_amount_help', ['min' => '1,000', 'max' => number_format($booking->price, 0, ',', '.')]) }}
                            </p>
                            @error('refund_amount')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Refund Reason -->
                        <div class="mb-6">
                            <label for="refund_reason" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('booking.refund_reason') }} <span class="text-red-500">*</span>
                            </label>
                            <select name="refund_reason" id="refund_reason" required
                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">{{ __('booking.select_reason') }}</option>
                                <option value="tutor_unavailable">{{ __('booking.reason_tutor_unavailable') }}</option>
                                <option value="emergency">{{ __('booking.reason_emergency') }}</option>
                                <option value="technical_issues">{{ __('booking.reason_technical_issues') }}</option>
                                <option value="schedule_conflict">{{ __('booking.reason_schedule_conflict') }}</option>
                                <option value="other">{{ __('booking.reason_other') }}</option>
                            </select>
                            @error('refund_reason')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Refund Description -->
                        <div class="mb-6">
                            <label for="refund_description" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('booking.refund_description') }}
                            </label>
                            <textarea name="refund_description" id="refund_description" rows="3"
                                      class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                      placeholder="{{ __('booking.refund_description_placeholder') }}"></textarea>
                            <p class="mt-1 text-sm text-gray-500">{{ __('booking.refund_description_help') }}</p>
                            @error('refund_description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center justify-between pt-6 border-t">
                            <a href="{{ route('bookings.show', $booking) }}"
                               class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-lg transition duration-200">
                                {{ __('common.cancel') }}
                            </a>
                            <button type="submit"
                                    class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-6 rounded-lg transition duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                                    onclick="return confirm('{{ __('booking.refund_confirm_message') }}')">
                                <i class="fas fa-undo mr-2"></i>
                                {{ __('booking.confirm_refund') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    

    @push('scripts')
        <script src="{{ asset('js/pages/bookings-refund-confirm.js') }}"></script>
    @endpush
</x-app-layout>
