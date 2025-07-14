<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('common.student_profile') }}: {{ $student->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center">
                            <img class="h-20 w-20 rounded-full" src="{{ $student->profile_photo_url }}" alt="{{ $student->name }}">
                            <div class="ml-6">
                                <h3 class="text-2xl font-bold text-gray-900">{{ $student->name }}</h3>
                                <p class="text-gray-600">{{ $student->email }}</p>
                                <p class="text-sm text-gray-500">
                                    {{ __('common.member_since') }} {{ $student->created_at->format('F Y') }}
                                </p>
                            </div>
                        </div>
                        <a href="{{ route('bookings.show', $booking) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:ring focus:ring-blue-200 active:text-gray-800 active:bg-gray-50 disabled:opacity-25 transition">
                            {{ __('common.back_to_booking') }}
                        </a>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-500 mb-2">{{ __('common.total_sessions') }}</h4>
                            <p class="text-2xl font-bold text-blue-600">{{ $allBookings->count() }}</p>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-500 mb-2">{{ __('common.completed_sessions') }}</h4>
                            <p class="text-2xl font-bold text-green-600">{{ $allBookings->where('is_completed', true)->count() }}</p>
                        </div>
                        <div class="bg-yellow-50 p-4 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-500 mb-2">{{ __('common.pending_sessions') }}</h4>
                            <p class="text-2xl font-bold text-yellow-600">{{ $allBookings->where('is_confirmed', false)->where('is_cancelled', false)->where('is_completed', false)->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Booking History -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('common.booking_history') }}</h3>
                    @if($allBookings->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('common.subject') }}
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('common.date_time') }}
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('common.status') }}
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('common.price') }}
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('common.actions') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($allBookings as $bookingItem)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ translateSubjectName($bookingItem->subject->name) }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $bookingItem->start_time->format('M d, Y') }}</div>
                                                <div class="text-sm text-gray-500">{{ $bookingItem->start_time->format('H:i') }} - {{ $bookingItem->end_time->format('H:i') }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @php
                                                    $statusBadge = getBookingStatusBadge($bookingItem->status);
                                                @endphp
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                    @if($bookingItem->status === 'accepted') bg-green-100 text-green-800
                                                    @elseif($bookingItem->status === 'pending') bg-yellow-100 text-yellow-800
                                                    @elseif($bookingItem->status === 'rejected') bg-red-100 text-red-800
                                                    @elseif($bookingItem->status === 'completed') bg-blue-100 text-blue-800
                                                    @else bg-gray-100 text-gray-800
                                                    @endif">
                                                    {{ $statusBadge['text'] }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $bookingItem->display_amount }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="{{ route('bookings.show', $bookingItem) }}" class="text-indigo-600 hover:text-indigo-900">
                                                    {{ __('common.view') }}
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500">{{ __('common.no_bookings_yet') }}</p>
                    @endif
                </div>
            </div>

            <!-- Reviews -->
            @if($reviews->count() > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('common.reviews_from_student') }}</h3>
                        <div class="space-y-4">
                            @foreach($reviews as $review)
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center">
                                            <div class="flex items-center">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <svg class="w-4 h-4 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                    </svg>
                                                @endfor
                                                <span class="ml-2 text-sm text-gray-600">{{ $review->rating }}/5</span>
                                            </div>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $review->booking->subject->name }} - {{ $review->created_at->format('M d, Y') }}
                                        </div>
                                    </div>
                                    @if($review->comment)
                                        <p class="text-gray-700">{{ $review->comment }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
