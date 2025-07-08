<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('common.tutor_dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Welcome Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium mb-4">{{ __('common.welcome') }}, {{ Auth::user()->name }}!</h3>

                    <!-- Stats Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h4 class="font-medium text-blue-700">{{ __('common.upcoming_sessions') }}</h4>
                            <p class="text-2xl font-bold text-blue-600">{{ $upcomingBookings }}</p>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg">
                            <h4 class="font-medium text-green-700">{{ __('common.total_students') }}</h4>
                            <p class="text-2xl font-bold text-green-600">{{ $totalStudents }}</p>
                        </div>
                        <div class="bg-purple-50 p-4 rounded-lg">
                            <h4 class="font-medium text-purple-700">{{ __('common.total_earnings') }}</h4>
                            <p class="text-2xl font-bold text-purple-600">{{ formatCurrency($totalEarnings) }}</p>
                        </div>
                        <div class="bg-yellow-50 p-4 rounded-lg">
                            <h4 class="font-medium text-yellow-700">{{ __('common.completed_sessions') }}</h4>
                            <p class="text-2xl font-bold text-yellow-600">{{ $completedBookings }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Teaching Schedule Calendar -->
            <div class="mb-6">
                <x-tutor-calendar :calendarData="$calendarData" />
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Upcoming Sessions -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">{{ __('common.upcoming_sessions') }}</h4>
                        @if($upcomingSessions->count() > 0)
                            <div class="space-y-3">
                                @foreach($upcomingSessions as $session)
                                    <div class="border border-gray-200 rounded-lg p-4">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <h5 class="font-medium text-gray-900">{{ $session->subject->name ?? 'N/A' }}</h5>
                                                <p class="text-sm text-gray-600">{{ __('common.student') }}: {{ $session->student->name ?? 'N/A' }}</p>
                                                <p class="text-sm text-gray-500">
                                                    {{ Carbon\Carbon::parse($session->start_time)->format('d/m/Y H:i') }} -
                                                    {{ Carbon\Carbon::parse($session->end_time)->format('H:i') }}
                                                </p>
                                            </div>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ $session->status }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 text-center py-4">{{ __('common.no_upcoming_sessions') }}</p>
                        @endif
                    </div>
                </div>

                <!-- Recent Bookings -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">{{ __('common.recent_bookings') }}</h4>
                        @if($recentBookings->count() > 0)
                            <div class="space-y-3">
                                @foreach($recentBookings as $booking)
                                    <div class="border border-gray-200 rounded-lg p-4">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <h5 class="font-medium text-gray-900">{{ $booking->subject->name ?? 'N/A' }}</h5>
                                                <p class="text-sm text-gray-600">{{ __('common.student') }}: {{ $booking->student->name ?? 'N/A' }}</p>
                                                <p class="text-sm text-gray-500">
                                                    {{ Carbon\Carbon::parse($booking->start_time)->format('d/m/Y H:i') }}
                                                </p>
                                                <p class="text-sm font-medium text-gray-700">{{ $booking->display_amount }}</p>
                                            </div>
                                            <div class="text-right">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    @if($booking->status === 'completed') bg-green-100 text-green-800
                                                    @elseif($booking->status === 'confirmed') bg-blue-100 text-blue-800
                                                    @elseif($booking->status === 'pending') bg-yellow-100 text-yellow-800
                                                    @else bg-gray-100 text-gray-800 @endif">
                                                    {{ ucfirst($booking->status) }}
                                                </span>
                                                <div class="mt-2">
                                                    <a href="{{ route('bookings.show', $booking) }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                                                        {{ __('common.view_details') }}
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 text-center py-4">{{ __('common.no_bookings_yet') }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Full Booking List -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="text-lg font-medium text-gray-900">{{ __('common.all_bookings') }}</h4>
                        <div class="flex space-x-2">
                            <span class="text-sm text-gray-600">{{ __('common.total') }}: {{ $totalBookings }}</span>
                            <span class="text-sm text-gray-600">|</span>
                            <span class="text-sm text-green-600">{{ __('common.completed') }}: {{ $completedBookings }}</span>
                            <span class="text-sm text-gray-600">|</span>
                            <span class="text-sm text-yellow-600">{{ __('common.pending') }}: {{ $pendingBookings }}</span>
                        </div>
                    </div>

                    @if($recentBookings->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.subject') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.student') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.date_time') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.amount') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.status') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($recentBookings as $booking)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $booking->subject->name ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $booking->student->name ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ Carbon\Carbon::parse($booking->start_time)->format('d/m/Y H:i') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $booking->display_amount }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    @if($booking->status === 'completed') bg-green-100 text-green-800
                                                    @elseif($booking->status === 'confirmed') bg-blue-100 text-blue-800
                                                    @elseif($booking->status === 'pending') bg-yellow-100 text-yellow-800
                                                    @else bg-gray-100 text-gray-800 @endif">
                                                    {{ ucfirst($booking->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="{{ route('bookings.show', $booking) }}" class="text-indigo-600 hover:text-indigo-900">
                                                    {{ __('common.view') }}
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-8">{{ __('common.no_bookings_yet') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
