<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('admin.booking_details') }}: #{{ $booking->id }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Booking Information Card -->
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{ __('admin.booking_information') }}</h3>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-8">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('admin.booking_id') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $booking->id }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('admin.student') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            <a href="{{ route('admin.students.show', $booking->student) }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">
                                {{ $booking->student->name ?? __('admin.na') }}
                            </a>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('admin.tutor') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                             <a href="{{ $booking->tutor && $booking->tutor->user ? route('admin.tutors.show', $booking->tutor->user) : '#' }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">
                                {{ $booking->tutor->user->name ?? __('admin.na') }}
                            </a>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('admin.subject') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $booking->subject->name ?? __('admin.na') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('admin.scheduled_time') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            {{ Carbon\Carbon::parse($booking->start_time)->format('D, M d, Y ') }}
                                                                    {{ __('admin.from') }} {{ Carbon\Carbon::parse($booking->start_time)->format('H:i') }}
                                        {{ __('admin.to') }} {{ Carbon\Carbon::parse($booking->end_time)->format('H:i') }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('admin.price') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">${{ number_format($booking->price, 2) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('admin.booking_status') }}</dt>
                        <dd class="mt-1 text-sm">
                            <span @class([
                                'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-700 dark:text-yellow-100' => $booking->status === 'pending',
                                'bg-blue-100 text-blue-800 dark:bg-blue-700 dark:text-blue-100' => $booking->status === 'accepted',
                                'bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100' => $booking->status === 'completed',
                                'bg-red-100 text-red-800 dark:bg-red-700 dark:text-red-100' => $booking->status === 'cancelled' || $booking->status === 'rejected',
                                'bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-200' => !in_array($booking->status, ['pending', 'accepted', 'completed', 'cancelled', 'rejected']),
                            ])>
                                {{ __('admin.' . $booking->status) }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('admin.payment_status') }}</dt>
                        <dd class="mt-1 text-sm">
                            <span @class([
                                'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-700 dark:text-yellow-100' => $booking->payment_status === 'pending',
                                'bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100' => $booking->payment_status === 'paid',
                                'bg-red-100 text-red-800 dark:bg-red-700 dark:text-red-100' => $booking->payment_status === 'failed',
                                'bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-200' => !in_array($booking->payment_status, ['pending', 'paid', 'failed']),
                            ])>
                                {{ __('admin.' . $booking->payment_status) }}
                            </span>
                        </dd>
                    </div>
                    <div class="md:col-span-2">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('admin.notes') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $booking->notes ?? __('admin.na') }}</dd>
                    </div>
                    <div class="md:col-span-2">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('admin.meeting_link') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            @if($booking->meeting_link)
                                <a href="{{ $booking->meeting_link }}" target="_blank" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">
                                    {{ $booking->meeting_link }}
                                </a>
                            @else
                                {{ __('admin.na') }}
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Review Information (if exists) -->
            @if($booking->review)
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{ __('admin.review') }}</h3>
                 <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-8">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('admin.reviewer') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $booking->review->reviewer->name ?? __('admin.na') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('admin.rating') }}</dt>
                        <dd class="mt-1 flex items-center">
                            @for ($i = 1; $i <= 5; $i++)
                                <svg aria-hidden="true" class="h-5 w-5 {{ $i <= $booking->review->rating ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.97a1 1 0 00.95.69h4.174c.969 0 1.371 1.24.588 1.81l-3.378 2.452a1 1 0 00-.364 1.118l1.287 3.971c.3.921-.755 1.688-1.54 1.118l-3.378-2.452a1 1 0 00-1.175 0l-3.378 2.452c-.784.57-1.838-.197-1.539-1.118l1.286-3.971a1 1 0 00-.364-1.118L2.04 9.398c-.783-.57-.38-1.81.588-1.81h4.174a1 1 0 00.95-.69l1.286-3.97z" />
                                </svg>
                            @endfor
                            <span class="ml-2 text-sm text-gray-600 dark:text-gray-300"> ({{ $booking->review->rating }}/5)</span>
                        </dd>
                    </div>
                    <div class="md:col-span-2">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('admin.comment') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $booking->review->comment ?? __('admin.na') }}</dd>
                    </div>
                </dl>
            </div>
            @endif

            <div class="mt-6 flex justify-between items-center">
                <a href="{{ route('admin.bookings') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    {{ __('admin.back_to_bookings_list') }}
                </a>
                {{-- Add other actions here like Cancel Booking, Mark as Completed etc. --}}
            </div>
        </div>
    </div>
</x-admin-layout>
