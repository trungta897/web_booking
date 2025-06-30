<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('common.Booking Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Session Messages -->
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif
            @if (session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif
            @if (session('info'))
                <div class="mb-4 bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('info') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="mb-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">{{ __('common.Session Information') }}</h3>
                                <p class="mt-1 text-sm text-gray-600">{{ __('common.booking_id') }} #{{ $booking->id }}</p>
                            </div>
                            <span class="px-3 py-1 text-sm font-semibold rounded-full
                                @if($booking->status === 'accepted') bg-green-100 text-green-800
                                @elseif($booking->status === 'pending') bg-yellow-100 text-yellow-800
                                @elseif($booking->status === 'rejected') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                @switch($booking->status)
                                    @case('accepted')
                                        {{ __('common.accepted') }}
                                        @break
                                    @case('pending')
                                        {{ __('common.pending') }}
                                        @break
                                    @case('rejected')
                                        {{ __('common.rejected') }}
                                        @break
                                    @case('cancelled')
                                        {{ __('common.cancelled') }}
                                        @break
                                    @case('completed')
                                        {{ __('common.completed') }}
                                        @break
                                    @default
                                        {{ ucfirst($booking->status) }}
                                @endswitch
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">{{ __('common.tutor') }}</h4>
                            <div class="mt-2 flex items-center">
                                <img class="h-10 w-10 rounded-full" src="{{ $booking->tutor->user->profile_photo_url }}" alt="{{ $booking->tutor->user->name }}">
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-900">{{ $booking->tutor->user->name }}</p>
                                    <p class="text-sm text-gray-500">{{ translateSubjectName($booking->subject->name) }}</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-sm font-medium text-gray-500">{{ __('common.student') }}</h4>
                            <div class="mt-2 flex items-center justify-between">
                                <div class="flex items-center">
                                    <img class="h-10 w-10 rounded-full" src="{{ $booking->student->profile_photo_url }}" alt="{{ $booking->student->name }}">
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-gray-900">{{ $booking->student->name }}</p>
                                    </div>
                                </div>
                                @if(auth()->user()->role === 'tutor' && auth()->user()->id === $booking->tutor->user->id)
                                    <a href="{{ route('bookings.student-profile', $booking) }}" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-indigo-600 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        {{ __('common.view_profile') }}
                                    </a>
                                @endif
                            </div>
                        </div>

                        <div>
                            <h4 class="text-sm font-medium text-gray-500">{{ __('common.date_time') }}</h4>
                            <p class="mt-2 text-sm text-gray-900">
                                {{ $booking->start_time->format('F d, Y') }}
                            </p>
                            <p class="text-sm text-gray-500">
                                {{ $booking->start_time->format('H:i') }} - {{ $booking->end_time->format('H:i') }}
                            </p>
                        </div>

                        <div>
                            <h4 class="text-sm font-medium text-gray-500">{{ __('common.price') }}</h4>
                            <p class="mt-2 text-sm text-gray-900">{{ $booking->display_amount }}</p>
                        </div>

                        @if($booking->notes)
                            <div class="md:col-span-2">
                                <h4 class="text-sm font-medium text-gray-500">{{ __('common.notes') }}</h4>
                                <p class="mt-2 text-sm text-gray-900">{{ $booking->notes }}</p>
                            </div>
                        @endif

                        @if($booking->rejection_reason && $booking->status === 'rejected')
                            <div class="md:col-span-2">
                                <h4 class="text-sm font-medium text-gray-500">{{ __('common.rejection_reason') }}</h4>
                                <p class="mt-2 text-sm text-red-600">{{ $booking->rejection_reason }}</p>
                                @if($booking->rejection_description)
                                    <p class="mt-1 text-sm text-gray-600">{{ $booking->rejection_description }}</p>
                                @endif
                            </div>
                        @endif

                        @if($booking->cancellation_reason && $booking->status === 'cancelled')
                            <div class="md:col-span-2">
                                <h4 class="text-sm font-medium text-gray-500">{{ __('common.cancellation_reason') }}</h4>
                                <p class="mt-2 text-sm text-red-600">{{ $booking->cancellation_reason }}</p>
                                @if($booking->cancellation_description)
                                    <p class="mt-1 text-sm text-gray-600">{{ $booking->cancellation_description }}</p>
                                @endif
                            </div>
                        @endif

                        @if($booking->meeting_link)
                            <div class="md:col-span-2">
                                <h4 class="text-sm font-medium text-gray-500">{{ __('common.meeting_link') }}</h4>
                                <a href="{{ $booking->meeting_link }}" target="_blank" class="mt-2 text-sm text-indigo-600 hover:text-indigo-900">
                                    {{ $booking->meeting_link }}
                                </a>
                            </div>
                        @endif

                        <!-- ========== UNIFIED PAYMENT/TRANSACTION HISTORY SECTION START ========== -->
                        <div class="md:col-span-2">
                            @if($booking->status === 'accepted' && auth()->user()->id === $booking->student_id)
                                {{-- This block is for the student viewing their own accepted booking --}}
                                                                                                @php
                                    // Ensure we have fresh transaction data
                                    $booking->load('transactions');

                                    // Check multiple conditions for payment status
                                    $paymentStatusIsPaid = $booking->payment_status === 'paid';
                                    $paymentStatusIsPending = $booking->payment_status === 'pending';
                                    $hasCompletedTransactions = $booking->completedTransactions()->exists();
                                    $hasSuccessfulPaymentTransactions = $booking->transactions()
                                        ->where('type', 'payment')
                                        ->where('status', 'completed')
                                        ->exists();

                                    // Use comprehensive check for paid status
                                    $isAlreadyPaid = $booking->isFullyPaid();

                                    // Check if payment is ready to be made
                                    $canMakePayment = $booking->status === 'accepted' &&
                                                     !$isAlreadyPaid &&
                                                     (!$paymentStatusIsPending || $booking->vnpay_txn_ref === null);

                                    $completedTransactionsCount = $booking->completedTransactions()->count();
                                    $allTransactionsCount = $booking->transactions()->count();

                                    // Double check: if payment_status is not 'paid' but we have completed transactions,
                                    // update the payment_status to sync
                                    if (!$paymentStatusIsPaid && $hasSuccessfulPaymentTransactions) {
                                        $booking->update(['payment_status' => 'paid']);
                                        $paymentStatusIsPaid = true;
                                        $isAlreadyPaid = true;
                                        $canMakePayment = false;
                                    }
                                @endphp


                                @if($isAlreadyPaid)
                                    <!-- Case 1: Already Paid - Hide payment button completely -->
                                    <h4 class="text-sm font-medium text-gray-500">{{ __('common.Payment History') }}</h4>
                                    <div class="mt-2">
                                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                            <div class="flex items-center">
                                                <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                                <span class="text-sm text-green-800 font-medium">{{ __('booking.info.payment_completed_successfully') }}</span>
                                            </div>
                                            <p class="text-sm text-green-700 mt-2">
                                                {{ __('booking.info.payment_amount_was') }}: <span class="font-semibold">{{ $booking->display_amount }}</span>
                                            </p>

                                            @php
                                                $completedTransaction = $booking->completedTransactions()->latest()->first();
                                            @endphp

                                            @if($completedTransaction)
                                                <div class="mt-3 pt-3 border-t border-green-200">
                                                    <div class="grid grid-cols-2 gap-2 text-xs">
                                                        <div>
                                                            <span class="text-green-600">{{ __('booking.info.payment_method') }}:</span>
                                                            <span class="font-medium text-green-800">{{ $completedTransaction->payment_method_name }}</span>
                                                        </div>
                                                        <div>
                                                            <span class="text-green-600">{{ __('booking.info.paid_at') }}:</span>
                                                            <span class="font-medium text-green-800">{{ $completedTransaction->processed_at ? $completedTransaction->processed_at->format('d/m/Y H:i') : $completedTransaction->created_at->format('d/m/Y H:i') }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif

                                            <div class="mt-4">
                                                <a href="{{ route('bookings.transactions', $booking) }}"
                                                   class="inline-flex items-center px-3 py-2 bg-green-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 transition-all duration-200">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                    {{ __('booking.info.view_transaction_history') }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @elseif($canMakePayment)
                                    <!-- Case 2: Needs Payment - Only show if can make payment -->
                                    <h4 class="text-sm font-medium text-gray-500">{{ __('common.Payment Required') }}</h4>
                                    <div class="mt-2">
                                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                            <div class="flex items-center">
                                                <svg class="w-5 h-5 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                                </svg>
                                                <span class="text-sm text-blue-800">{{ __('common.This booking requires payment to confirm.') }}</span>
                                            </div>
                                            <div class="mt-3">
                                                <a href="{{ route('bookings.payment', $booking) }}"
                                                   class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:from-blue-700 hover:to-purple-700 transition-all duration-200">
                                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/>
                                                        <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/>
                                                    </svg>
                                                    {{ __('common.Complete Payment') }} {{ $booking->display_amount }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @elseif($paymentStatusIsPending && !$canMakePayment)
                                    <!-- Case 3: Payment In Progress - Show status message -->
                                    <h4 class="text-sm font-medium text-gray-500">{{ __('booking.payment_status') }}</h4>
                                    <div class="mt-2">
                                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                            <div class="flex items-center">
                                                <svg class="w-5 h-5 text-yellow-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                                </svg>
                                                <span class="text-sm text-yellow-800 font-medium">{{ __('booking.payment_processing') }}</span>
                                            </div>
                                            <p class="text-sm text-yellow-700 mt-2">
                                                {{ __('booking.payment_processing_message') }}
                                            </p>
                                            @if($booking->vnpay_txn_ref)
                                                <p class="text-xs text-yellow-600 mt-2 font-mono">
                                                    {{ __('booking.transaction_code') }}: {{ $booking->vnpay_txn_ref }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            @elseif($booking->transactions()->count() > 0)
                                {{-- This block is for other roles (like tutor) viewing the booking --}}
                                <h4 class="text-sm font-medium text-gray-500">{{ __('common.Payment History') }}</h4>
                                <div class="mt-2 flex items-center space-x-4">
                                    <span class="text-sm text-gray-900">
                                        {{ __('common.payment_method') }}: {{ $booking->payment_method_display ?? 'N/A' }}
                                    </span>
                                    <a href="{{ route('bookings.transactions', $booking) }}"
                                       class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-900">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        {{ __('common.View Transaction History') }}
                                    </a>
                                </div>
                            @endif
                        </div>
                        <!-- ========== UNIFIED PAYMENT/TRANSACTION HISTORY SECTION END ========== -->

                        <!-- Refund section for tutors -->
                        @if($booking->status === 'accepted' && ($booking->payment_status === 'paid' || $booking->completedTransactions()->exists()) && auth()->user()->role === 'tutor' && auth()->user()->tutor && $booking->tutor_id === auth()->user()->tutor->id)
                            <div class="md:col-span-2">
                                <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-orange-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.667-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="text-sm text-orange-800 font-medium">{{ __('booking.refund_management') }}</span>
                                    </div>
                                    <p class="mt-2 text-sm text-orange-700">
                                        {{ __('booking.refund_description') }}
                                    </p>
                                    <div class="mt-3">
                                        <button type="button" onclick="openRefundModal()"
                                                class="inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700 transition-all duration-200">
                                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/>
                                                <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/>
                                            </svg>
                                            {{ __('booking.refund_to_student') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    @if($booking->status === 'pending')
                        @php
                            $isBookingTutor = auth()->user()->id === $booking->tutor->user->id;
                            $userRole = auth()->user()->role;
                        @endphp
                        @if($isBookingTutor && $userRole === 'tutor')
                            <div class="mt-6 flex justify-end space-x-3">
                                <button type="button" onclick="openRejectModal()" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:ring focus:ring-blue-200 active:text-gray-800 active:bg-gray-50 disabled:opacity-25 transition">
                                    {{ __('common.reject') }}
                                </button>
                                <form action="{{ route('bookings.update', $booking) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="accepted">
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition">
                                        {{ __('common.accept') }}
                                    </button>
                                </form>
                            </div>
                        @endif
                    @endif

                    @if($booking->status === 'pending' && auth()->user()->id === $booking->student_id)
                        <div class="mt-6 flex justify-end">
                            <button type="button" onclick="openCancelModal()" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-red-700 uppercase tracking-widest shadow-sm hover:text-red-500 focus:outline-none focus:border-red-300 focus:ring focus:ring-red-200 active:text-red-800 active:bg-gray-50 disabled:opacity-25 transition">
                                {{ __('common.cancel_booking') }}
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('common.reject_booking') }}</h3>
                <form action="{{ route('bookings.update', $booking) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" value="rejected">

                    <div class="mb-4">
                        <label for="rejection_reason" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('common.rejection_reason') }}
                        </label>
                        <select name="rejection_reason" id="rejection_reason" class="w-full rounded-md border-gray-300">
                            <option value="">{{ __('common.select_reason') }}</option>
                            <option value="schedule_conflict">{{ __('common.schedule_conflict') }}</option>
                            <option value="not_qualified">{{ __('common.not_qualified') }}</option>
                            <option value="overbooked">{{ __('common.overbooked') }}</option>
                            <option value="inappropriate_request">{{ __('common.inappropriate_request') }}</option>
                            <option value="other">{{ __('common.other') }}</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="rejection_description" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('common.description') }} ({{ __('common.optional') }})
                        </label>
                        <textarea name="rejection_description" id="rejection_description" rows="3"
                                class="w-full rounded-md border-gray-300"
                                placeholder="{{ __('common.rejection_description_placeholder') }}"></textarea>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeRejectModal()"
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                            {{ __('common.cancel') }}
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                            {{ __('common.reject') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Cancel Modal -->
    <div id="cancelModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('common.cancel_booking') }}</h3>
                <form action="{{ route('bookings.destroy', $booking) }}" method="POST">
                    @csrf
                    @method('DELETE')

                    <div class="mb-4">
                        <label for="cancellation_reason" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('common.cancellation_reason') }}
                        </label>
                        <select name="cancellation_reason" id="cancellation_reason" class="w-full rounded-md border-gray-300">
                            <option value="">{{ __('common.select_reason') }}</option>
                            <option value="schedule_conflict">{{ __('common.schedule_conflict') }}</option>
                            <option value="found_another_tutor">{{ __('common.found_another_tutor') }}</option>
                            <option value="personal_reason">{{ __('common.personal_reason') }}</option>
                            <option value="financial_reason">{{ __('common.financial_reason') }}</option>
                            <option value="other">{{ __('common.other') }}</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="cancellation_description" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('common.description') }} ({{ __('common.optional') }})
                        </label>
                        <textarea name="cancellation_description" id="cancellation_description" rows="3"
                                class="w-full rounded-md border-gray-300"
                                placeholder="{{ __('common.cancellation_description_placeholder') }}"></textarea>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeCancelModal()"
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                            {{ __('common.cancel') }}
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                            {{ __('common.cancel_booking') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Refund Modal -->
    <div id="refundModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Hoàn tiền cho học viên</h3>
                <form action="{{ route('payments.refund', $booking) }}" method="POST">
                    @csrf

                    <div class="mb-4 p-4 bg-orange-50 rounded-lg">
                        <p class="text-sm text-orange-700">
                            <strong>Lưu ý:</strong> Việc hoàn tiền sẽ hủy buổi học và không thể hoàn tác.
                            Học viên sẽ nhận được tiền hoàn trong vòng 3-5 ngày làm việc.
                        </p>
                        <p class="text-sm text-orange-700 mt-2">
                            <strong>Số tiền hoàn:</strong> {{ $booking->display_amount }}
                        </p>
                    </div>

                    <div class="mb-4">
                        <label for="refund_reason" class="block text-sm font-medium text-gray-700 mb-2">
                            Lý do hoàn tiền <span class="text-red-500">*</span>
                        </label>
                        <select name="refund_reason" id="refund_reason" required class="w-full rounded-md border-gray-300">
                            <option value="">Chọn lý do</option>
                            <option value="tutor_unavailable">Gia sư không thể dạy</option>
                            <option value="emergency">Tình huống khẩn cấp</option>
                            <option value="technical_issues">Vấn đề kỹ thuật</option>
                            <option value="other">Lý do khác</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="refund_description" class="block text-sm font-medium text-gray-700 mb-2">
                            Mô tả chi tiết (tùy chọn)
                        </label>
                        <textarea name="refund_description" id="refund_description" rows="3"
                                class="w-full rounded-md border-gray-300"
                                placeholder="Nhập mô tả chi tiết (tùy chọn)..."></textarea>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeRefundModal()"
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                            Hủy
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-orange-600 text-white rounded-md hover:bg-orange-700">
                            Xác nhận hoàn tiền
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openRejectModal() {
            document.getElementById('rejectModal').classList.remove('hidden');
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').classList.add('hidden');
        }

        function openCancelModal() {
            document.getElementById('cancelModal').classList.remove('hidden');
        }

        function closeCancelModal() {
            document.getElementById('cancelModal').classList.add('hidden');
        }

        function openRefundModal() {
            document.getElementById('refundModal').classList.remove('hidden');
        }

        function closeRefundModal() {
            document.getElementById('refundModal').classList.add('hidden');
        }

        // Close modals when clicking outside
        document.addEventListener('click', function(event) {
            const modals = ['rejectModal', 'cancelModal', 'refundModal'];
            modals.forEach(modalId => {
                const modal = document.getElementById(modalId);
                if (event.target === modal) {
                    modal.classList.add('hidden');
                }
            });
        });
    </script>
</x-app-layout>
