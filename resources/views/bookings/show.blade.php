<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Booking Details') }}
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
                                <h3 class="text-lg font-medium text-gray-900">Session Information</h3>
                                <p class="mt-1 text-sm text-gray-600">Booking #{{ $booking->id }}</p>
                            </div>
                            <span class="px-3 py-1 text-sm font-semibold rounded-full
                                @if($booking->status === 'accepted') bg-green-100 text-green-800
                                @elseif($booking->status === 'pending') bg-yellow-100 text-yellow-800
                                @elseif($booking->status === 'rejected') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst($booking->status) }}
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">Tutor</h4>
                            <div class="mt-2 flex items-center">
                                <img class="h-10 w-10 rounded-full" src="{{ $booking->tutor->user->profile_photo_url }}" alt="{{ $booking->tutor->user->name }}">
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-900">{{ $booking->tutor->user->name }}</p>
                                    <p class="text-sm text-gray-500">{{ $booking->subject->name }}</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-sm font-medium text-gray-500">Student</h4>
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
                            <h4 class="text-sm font-medium text-gray-500">Date & Time</h4>
                            <p class="mt-2 text-sm text-gray-900">
                                {{ $booking->start_time->format('F d, Y') }}
                            </p>
                            <p class="text-sm text-gray-500">
                                {{ $booking->start_time->format('H:i') }} - {{ $booking->end_time->format('H:i') }}
                            </p>
                        </div>

                        <div>
                            <h4 class="text-sm font-medium text-gray-500">Price</h4>
                            <p class="mt-2 text-sm text-gray-900">${{ number_format($booking->price, 2) }}</p>
                        </div>

                        @if($booking->notes)
                            <div class="md:col-span-2">
                                <h4 class="text-sm font-medium text-gray-500">Notes</h4>
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
                                <h4 class="text-sm font-medium text-gray-500">Meeting Link</h4>
                                <a href="{{ $booking->meeting_link }}" target="_blank" class="mt-2 text-sm text-indigo-600 hover:text-indigo-900">
                                    {{ $booking->meeting_link }}
                                </a>
                            </div>
                        @endif

                        <!-- Transaction History -->
                        @if($booking->transactions()->count() > 0)
                            <div class="md:col-span-2">
                                <h4 class="text-sm font-medium text-gray-500">{{ __('Payment History') }}</h4>
                                <div class="mt-2 flex items-center space-x-4">
                                    <span class="text-sm text-gray-900">
                                        {{ __('Payment Method') }}: {{ $booking->payment_method_display ?? 'N/A' }}
                                    </span>
                                    <a href="{{ route('payments.transactions.view', $booking) }}"
                                       class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-900">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        {{ __('View Transaction History') }}
                                    </a>
                                </div>
                            </div>
                        @endif

                        @if($booking->status === 'accepted' && auth()->user()->id === $booking->student_id)
                            <div class="md:col-span-2">
                                @if($booking->payment_status === 'paid')
                                    <!-- Already paid - show success message -->
                                    <h4 class="text-sm font-medium text-green-600">{{ __('booking.payment_completed') }}</h4>
                                    <div class="mt-2">
                                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                            <div class="flex items-center">
                                                <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                                <span class="text-sm text-green-800 font-medium">{{ __('booking.payment_completed_successfully') }}</span>
                                            </div>
                                            <p class="text-sm text-green-700 mt-2">
                                                {{ __('booking.payment_amount_was') }}: <span class="font-semibold">{{ $booking->display_amount }}</span>
                                            </p>
                                            <div class="mt-3">
                                                <a href="{{ route('bookings.transactions', $booking) }}"
                                                   class="inline-flex items-center px-3 py-2 bg-green-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 transition-all duration-200">
                                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    {{ __('booking.view_transaction_history') }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <!-- Needs payment - show payment button -->
                                    <h4 class="text-sm font-medium text-gray-500">{{ __('Payment Required') }}</h4>
                                    <div class="mt-2">
                                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                            <div class="flex items-center">
                                                <svg class="w-5 h-5 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                                </svg>
                                                <span class="text-sm text-blue-800">{{ __('This booking requires payment to confirm.') }}</span>
                                            </div>
                                            <div class="mt-3">
                                                <a href="{{ route('bookings.payment', $booking) }}"
                                                   class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:from-blue-700 hover:to-purple-700 transition-all duration-200">
                                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/>
                                                        <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/>
                                                    </svg>
                                                    {{ __('Complete Payment') }} {{ $booking->display_amount }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif

                        <!-- Refund section for tutors -->
                        @if($booking->status === 'accepted' && $booking->payment_status === 'paid' && auth()->user()->role === 'tutor' && auth()->user()->tutor && $booking->tutor_id === auth()->user()->tutor->id)
                            <div class="md:col-span-2">
                                <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-orange-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="text-sm text-orange-800 font-medium">Quản lý hoàn tiền</span>
                                    </div>
                                    <p class="mt-2 text-sm text-orange-700">
                                        Nếu bạn không thể dạy buổi học này, bạn có thể hoàn tiền cho học viên.
                                    </p>
                                    <div class="mt-3">
                                        <button type="button" onclick="openRefundModal()"
                                                class="inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700 transition-all duration-200">
                                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/>
                                                <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/>
                                            </svg>
                                            Hoàn tiền cho học viên
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

                            <!-- Reject Modal -->
                            <div id="rejectModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeRejectModal()"></div>
                                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                                    <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                        <form action="{{ route('bookings.update', $booking) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="rejected">
                                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                                <div class="sm:flex sm:items-start">
                                                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                                        <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                                        </svg>
                                                    </div>
                                                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                                            {{ __('common.reject_booking') }}
                                                        </h3>
                                                        <div class="mt-4 space-y-4">
                                                            <div>
                                                                <label for="reject_reason" class="block text-sm font-medium text-gray-700">
                                                                    {{ __('common.rejection_reason_select') }} <span class="text-red-500">*</span>
                                                                </label>
                                                                <select id="reject_reason" name="rejection_reason" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                                    <option value="">{{ __('common.select_reason') }}</option>
                                                                    <option value="schedule_conflict">{{ __('common.schedule_conflict') }}</option>
                                                                    <option value="not_qualified">{{ __('common.not_qualified') }}</option>
                                                                    <option value="overbooked">{{ __('common.overbooked') }}</option>
                                                                    <option value="inappropriate_request">{{ __('common.inappropriate_request') }}</option>
                                                                    <option value="other">{{ __('common.other') }}</option>
                                                                </select>
                                                            </div>
                                                            <div>
                                                                <label for="rejection_description" class="block text-sm font-medium text-gray-700">
                                                                    {{ __('common.description') }} ({{ __('common.optional') }})
                                                                </label>
                                                                <textarea id="rejection_description" name="rejection_description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="{{ __('common.rejection_description_placeholder') }}"></textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                                <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                                                    {{ __('common.reject_booking') }}
                                                </button>
                                                <button type="button" onclick="closeRejectModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                                    {{ __('common.cancel') }}
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif

                    @if($booking->status === 'pending' && auth()->user()->id === $booking->student_id)
                        <div class="mt-6 flex justify-end">
                            <button type="button" onclick="openCancelModal()" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-red-700 uppercase tracking-widest shadow-sm hover:text-red-500 focus:outline-none focus:border-red-300 focus:ring focus:ring-red-200 active:text-red-800 active:bg-gray-50 disabled:opacity-25 transition">
                                {{ __('common.cancel_booking') }}
                            </button>
                        </div>

                        <!-- Cancel Modal for Students -->
                        <div id="cancelModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeCancelModal()"></div>
                                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                    <form action="{{ route('bookings.destroy', $booking) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                            <div class="sm:flex sm:items-start">
                                                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                                    <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                                    </svg>
                                                </div>
                                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                                        {{ __('common.cancel_booking') }}
                                                    </h3>
                                                    <div class="mt-4 space-y-4">
                                                        <div>
                                                            <label for="cancel_reason" class="block text-sm font-medium text-gray-700">
                                                                {{ __('common.cancellation_reason') }} <span class="text-red-500">*</span>
                                                            </label>
                                                            <select id="cancel_reason" name="cancellation_reason" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                                <option value="">{{ __('common.select_reason') }}</option>
                                                                <option value="schedule_conflict">{{ __('common.schedule_conflict') }}</option>
                                                                <option value="found_another_tutor">{{ __('common.found_another_tutor') }}</option>
                                                                <option value="personal_reason">{{ __('common.personal_reason') }}</option>
                                                                <option value="financial_reason">{{ __('common.financial_reason') }}</option>
                                                                <option value="other">{{ __('common.other') }}</option>
                                                            </select>
                                                        </div>
                                                        <div>
                                                            <label for="cancel_description" class="block text-sm font-medium text-gray-700">
                                                                {{ __('common.description') }} ({{ __('common.optional') }})
                                                            </label>
                                                            <textarea id="cancel_description" name="cancellation_description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="{{ __('common.cancellation_description_placeholder') }}"></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                                                {{ __('common.cancel_booking') }}
                                            </button>
                                            <button type="button" onclick="closeCancelModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                                {{ __('common.cancel') }}
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($booking->status === 'accepted' && $booking->payment_status !== 'paid' && auth()->user()->id === $booking->student_id)
        @push('scripts')
        <script src="https://js.stripe.com/v3/"></script>
        <script>
            const stripe = Stripe('{{ config('services.stripe.key') }}');
            const elements = stripe.elements();
            const paymentElement = elements.create('payment');
            paymentElement.mount('#payment-element');

            const form = document.getElementById('payment-form');
            const submitButton = document.getElementById('submit-payment');
            const messageDiv = document.getElementById('payment-message');

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                submitButton.disabled = true;

                try {
                    const response = await fetch('{{ route('payments.create-intent', $booking) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });

                    const data = await response.json();

                    if (data.error) {
                        throw new Error(data.error);
                    }

                    const { error } = await stripe.confirmPayment({
                        elements,
                        clientSecret: data.clientSecret,
                        confirmParams: {
                            return_url: '{{ route('bookings.show', $booking) }}',
                        }
                    });

                    if (error) {
                        throw error;
                    }
                } catch (error) {
                    messageDiv.textContent = error.message;
                    messageDiv.classList.remove('hidden');
                    submitButton.disabled = false;
                }
            });
        </script>
        @endpush
    @endif

    @if($booking->status === 'pending' && (auth()->user()->id === $booking->tutor->user->id || auth()->user()->id === $booking->student_id))
        @push('scripts')
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

            // Close modal when pressing escape key
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    closeRejectModal();
                    closeCancelModal();
                }
            });
        </script>
        @endpush
    @endif

    <!-- Refund Modal for Tutors -->
    @if($booking->status === 'accepted' && $booking->payment_status === 'paid' && auth()->user()->role === 'tutor' && auth()->user()->tutor && $booking->tutor_id === auth()->user()->tutor->id)
        <div id="refundModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeRefundModal()"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form action="{{ route('payments.refund', $booking) }}" method="POST">
                        @csrf
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-orange-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <svg class="h-6 h-6 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/>
                                        <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                        {{ __('Hoàn tiền cho học viên') }}
                                    </h3>
                                    <div class="mt-4 space-y-4">
                                        <div class="bg-orange-50 border border-orange-200 rounded-lg p-3">
                                            <p class="text-sm text-orange-800">
                                                <strong>Số tiền hoàn:</strong> {{ $booking->display_amount }}
                                            </p>
                                            <p class="text-sm text-orange-700 mt-1">
                                                Học viên sẽ nhận được tiền hoàn trong vòng 3-5 ngày làm việc.
                                            </p>
                                        </div>

                                        <div>
                                            <label for="refund_reason" class="block text-sm font-medium text-gray-700">
                                                {{ __('Lý do hoàn tiền') }} <span class="text-red-500">*</span>
                                            </label>
                                            <select id="refund_reason" name="refund_reason" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                                                <option value="">{{ __('Chọn lý do') }}</option>
                                                <option value="tutor_sick">Gia sư bị ốm</option>
                                                <option value="tutor_emergency">Gia sư có việc khẩn cấp</option>
                                                <option value="schedule_conflict">Xung đột lịch học</option>
                                                <option value="technical_issues">Sự cố kỹ thuật</option>
                                                <option value="student_request">Yêu cầu từ học viên</option>
                                                <option value="other">Lý do khác</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label for="refund_description" class="block text-sm font-medium text-gray-700">
                                                {{ __('Mô tả chi tiết') }} ({{ __('Tùy chọn') }})
                                            </label>
                                            <textarea id="refund_description" name="refund_description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500" placeholder="Mô tả thêm về lý do hoàn tiền..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-orange-600 text-base font-medium text-white hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 sm:ml-3 sm:w-auto sm:text-sm">
                                {{ __('Xác nhận hoàn tiền') }}
                            </button>
                            <button type="button" onclick="closeRefundModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                {{ __('Hủy bỏ') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @push('scripts')
        <script>
            function openRefundModal() {
                document.getElementById('refundModal').classList.remove('hidden');
            }

            function closeRefundModal() {
                document.getElementById('refundModal').classList.add('hidden');
            }

            // Close modal when pressing escape key
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    closeRefundModal();
                }
            });
        </script>
        @endpush
    @endif
</x-app-layout>
