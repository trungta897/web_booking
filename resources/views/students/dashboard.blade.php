<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('common.student_dashboard') }}
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
                            <h4 class="font-medium text-green-700">{{ __('common.total_tutors') }}</h4>
                            <p class="text-2xl font-bold text-green-600">{{ $totalTutors }}</p>
                        </div>
                        <div class="bg-purple-50 p-4 rounded-lg">
                            <h4 class="font-medium text-purple-700">{{ __('common.total_spent') }}</h4>
                            <p class="text-2xl font-bold text-purple-600">{{ number_format($totalSpent) }} VNĐ</p>
                        </div>
                        <div class="bg-yellow-50 p-4 rounded-lg">
                            <h4 class="font-medium text-yellow-700">{{ __('common.completed_sessions') }}</h4>
                            <p class="text-2xl font-bold text-yellow-600">{{ $completedBookings }}</p>
                        </div>
                    </div>
                </div>
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
                                                <p class="text-sm text-gray-600">{{ __('common.tutor') }}: {{ $session->tutor->user->name ?? 'N/A' }}</p>
                                                <p class="text-sm text-gray-500">
                                                    {{ Carbon\Carbon::parse($session->start_time)->format('d/m/Y H:i') }} -
                                                    {{ Carbon\Carbon::parse($session->end_time)->format('H:i') }}
                                                </p>
                                                <p class="text-sm font-medium text-gray-700">{{ number_format($session->price) }} VNĐ</p>
                                            </div>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ $session->status }}
                                            </span>
                                        </div>
                                        <div class="mt-3">
                                            <a href="{{ route('bookings.show', $session) }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                                                {{ __('common.view_details') }}
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 text-center py-4">{{ __('common.no_upcoming_sessions') }}</p>
                        @endif
                    </div>
                </div>

                <!-- Recent Reviews -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">{{ __('common.my_reviews') }}</h4>
                        @if($reviews->count() > 0)
                            <div class="space-y-3">
                                @foreach($reviews as $review)
                                    <div class="border border-gray-200 rounded-lg p-4">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <h5 class="font-medium text-gray-900">{{ $review->booking->subject->name ?? 'N/A' }}</h5>
                                                <p class="text-sm text-gray-600">{{ __('common.tutor') }}: {{ $review->tutor->user->name ?? 'N/A' }}</p>
                                                <div class="flex items-center mt-1">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        <svg class="w-4 h-4 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                        </svg>
                                                    @endfor
                                                    <span class="ml-1 text-sm text-gray-500">({{ $review->rating }}/5)</span>
                                                </div>
                                                <p class="text-sm text-gray-600 mt-2">{{ Str::limit($review->comment, 100) }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 text-center py-4">{{ __('common.no_reviews_yet') }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Learning History -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="text-lg font-medium text-gray-900">{{ __('common.learning_history') }}</h4>
                        <a href="{{ route('bookings.index') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            {{ __('common.view_all_bookings') }}
                        </a>
                    </div>

                    @if($completedSessions->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.subject') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.tutor') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.date_time') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.amount') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.status') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($completedSessions as $session)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $session->subject->name ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $session->tutor->user->name ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ Carbon\Carbon::parse($session->start_time)->format('d/m/Y H:i') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ number_format($session->price) }} VNĐ
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    {{ __('common.completed') }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                                <a href="{{ route('bookings.show', $session) }}" class="text-indigo-600 hover:text-indigo-900">
                                                    {{ __('common.view') }}
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-8">{{ __('common.no_completed_sessions_yet') }}</p>
                    @endif
                </div>
            </div>

            <!-- All Bookings -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="text-lg font-medium text-gray-900">{{ __('common.all_bookings') }}</h4>
                        <a href="{{ route('bookings.index') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            {{ __('common.view_all_bookings') }}
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.tutor') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.subject') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.date_time') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.status') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.price') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @php
                                    // Get all bookings (limited to 5 for dashboard)
                                    $allBookings = App\Models\Booking::where('student_id', Auth::id())
                                        ->with(['tutor.user', 'subject'])
                                        ->orderByDesc('created_at')
                                        ->limit(5)
                                        ->get();
                                @endphp
                                @if($allBookings->count() > 0)
                                    @foreach($allBookings as $booking)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-8 w-8">
                                                        <img class="h-8 w-8 rounded-full" src="{{ $booking->tutor->user->avatar ? asset('storage/' . $booking->tutor->user->avatar) : asset('images/default-avatar.png') }}" alt="{{ $booking->tutor->user->name }}">
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            {{ $booking->tutor->user->name }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $booking->subject->name ?? 'N/A' }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">
                                                    {{ Carbon\Carbon::parse($booking->start_time)->format('d/m/Y') }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ Carbon\Carbon::parse($booking->start_time)->format('H:i') }} - {{ Carbon\Carbon::parse($booking->end_time)->format('H:i') }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                    @if($booking->status === 'accepted') bg-green-100 text-green-800
                                                    @elseif($booking->status === 'pending') bg-yellow-100 text-yellow-800
                                                    @elseif($booking->status === 'rejected') bg-red-100 text-red-800
                                                    @elseif($booking->status === 'completed') bg-blue-100 text-blue-800
                                                    @else bg-gray-100 text-gray-800
                                                    @endif">
                                                    {{ ucfirst($booking->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ number_format($booking->price) }} VNĐ
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="{{ route('bookings.show', $booking) }}" class="text-indigo-600 hover:text-indigo-900">{{ __('common.view') }}</a>
                                                @if($booking->status === 'pending')
                                                    <form action="{{ route('bookings.destroy', $booking) }}" method="POST" class="inline-block ml-2">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('{{ __('common.confirm_cancel_booking') }}')">
                                                            {{ __('common.cancel') }}
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                            {{ __('common.no_bookings_yet') }}
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- All Bookings Summary -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="text-lg font-medium text-gray-900">{{ __('common.booking_summary') }}</h4>
                        <div class="flex space-x-2">
                            <span class="text-sm text-gray-600">{{ __('common.total') }}: {{ $totalBookings }}</span>
                            <span class="text-sm text-gray-600">|</span>
                            <span class="text-sm text-green-600">{{ __('common.completed') }}: {{ $completedBookings }}</span>
                            <span class="text-sm text-gray-600">|</span>
                            <span class="text-sm text-yellow-600">{{ __('common.pending') }}: {{ $pendingBookings }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-blue-50 p-4 rounded-lg text-center">
                            <h5 class="font-medium text-blue-700">{{ __('common.learning_time') }}</h5>
                            <p class="text-xl font-bold text-blue-600">{{ $completedBookings * 2 }} {{ __('common.hours') }}</p>
                            <p class="text-sm text-blue-500">{{ __('common.total_study_hours') }}</p>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg text-center">
                            <h5 class="font-medium text-green-700">{{ __('common.favorite_subject') }}</h5>
                            @php
                                $favoriteSubject = $completedSessions->groupBy('subject.name')->sortByDesc(function($group) {
                                    return $group->count();
                                })->keys()->first() ?? __('common.none');
                            @endphp
                            <p class="text-xl font-bold text-green-600">{{ $favoriteSubject }}</p>
                            <p class="text-sm text-green-500">{{ __('common.most_studied') }}</p>
                        </div>
                        <div class="bg-purple-50 p-4 rounded-lg text-center">
                            <h5 class="font-medium text-purple-700">{{ __('common.avg_rating_given') }}</h5>
                            @php
                                $avgRating = $reviews->avg('rating') ?? 0;
                            @endphp
                            <p class="text-xl font-bold text-purple-600">{{ number_format($avgRating, 1) }}/5</p>
                            <p class="text-sm text-purple-500">{{ __('common.your_reviews') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
