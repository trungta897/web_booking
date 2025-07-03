@extends('layouts.admin')

@section('content')
    <!-- Header -->
    <div class="admin-page-header bg-white border-b border-gray-200 dark:bg-gray-800 dark:border-gray-700 px-6 py-4">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Tutor Details: ') }} {{ $user->name }}
        </h2>
    </div>

    <!-- Main Content -->
    <div class="px-6 py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Tutor Information Card -->
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Tutor Information</h3>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-8">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Name</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $user->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $user->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Account Status</dt>
                        <dd class="mt-1 text-sm">
                            <span @class([
                                'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                'bg-green-100 text-green-800' => $user->account_status === 'active',
                                'bg-yellow-100 text-yellow-800' => $user->account_status === 'suspended',
                                'bg-red-100 text-red-800' => $user->account_status === 'banned',
                                'dark:bg-green-700 dark:text-green-100' => $user->account_status === 'active',
                                'dark:bg-yellow-700 dark:text-yellow-100' => $user->account_status === 'suspended',
                                'dark:bg-red-700 dark:text-red-100' => $user->account_status === 'banned',
                            ])>
                                {{ ucfirst($user->account_status) }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Joined Date</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $user->created_at->format('M d, Y H:i A') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Phone Number</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $user->phone_number ?? 'N/A' }}</dd>
                    </div>
                     <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Address</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $user->address ?? 'N/A' }}</dd>
                    </div>
                    @if($user->tutor)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Hourly Rate</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">${{ number_format($user->tutor->hourly_rate, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Average Rating</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $averageRating ? number_format($averageRating, 1) . ' / 5' : 'N/A' }}</dd>
                        </div>
                        <div class="md:col-span-2">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Bio</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $user->tutor->bio ?? 'N/A' }}</dd>
                        </div>
                        <div class="md:col-span-2">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Subjects</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ $user->tutor->subjects->count() > 0 ? $user->tutor->subjects->pluck('name')->implode(', ') : 'No subjects specified' }}
                            </dd>
                        </div>
                    @endif
                </dl>
            </div>

            <!-- Tutor Bookings Card -->
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Bookings as Tutor ({{ $user->tutorBookings->count() }})</h3>
                @if($user->tutorBookings->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Student</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Subject</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date & Time</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Price</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($user->tutorBookings as $booking)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $booking->student->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $booking->subject->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ Carbon\Carbon::parse($booking->start_time)->format('M d, Y H:i') }} - {{ Carbon\Carbon::parse($booking->end_time)->format('H:i') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                             <span @class([
                                                'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                                'bg-green-100 text-green-800' => $booking->status === 'completed' || $booking->status === 'accepted',
                                                'bg-yellow-100 text-yellow-800' => $booking->status === 'pending',
                                                'bg-red-100 text-red-800' => $booking->status === 'cancelled' || $booking->status === 'rejected',
                                                'bg-gray-100 text-gray-800' => !in_array($booking->status, ['completed', 'accepted', 'pending', 'cancelled', 'rejected']),
                                                'dark:bg-green-700 dark:text-green-100' => $booking->status === 'completed' || $booking->status === 'accepted',
                                                'dark:bg-yellow-700 dark:text-yellow-100' => $booking->status === 'pending',
                                                'dark:bg-red-700 dark:text-red-100' => $booking->status === 'cancelled' || $booking->status === 'rejected',
                                                'dark:bg-gray-600 dark:text-gray-200' => !in_array($booking->status, ['completed', 'accepted', 'pending', 'cancelled', 'rejected']),
                                            ])>
                                                {{ ucfirst($booking->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">${{ number_format($booking->price, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-500 dark:text-gray-400">No bookings found for this tutor.</p>
                @endif
            </div>

            <!-- Tutor Reviews Card -->
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Reviews Received ({{ $user->reviewsReceived->count() }})</h3>
                @if($user->reviewsReceived->count() > 0)
                    <div class="space-y-4">
                        @foreach($user->reviewsReceived as $review)
                            <div class="p-4 border rounded-md dark:border-gray-700">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $review->reviewer->name ?? 'Anonymous' }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $review->created_at->format('M d, Y') }}</p>
                                </div>
                                <div class="flex items-center mt-1">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <svg class="h-5 w-5 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600' }}" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.97a1 1 0 00.95.69h4.174c.969 0 1.371 1.24.588 1.81l-3.378 2.452a1 1 0 00-.364 1.118l1.287 3.971c.3.921-.755 1.688-1.54 1.118l-3.378-2.452a1 1 0 00-1.175 0l-3.378 2.452c-.784.57-1.838-.197-1.539-1.118l1.286-3.971a1 1 0 00-.364-1.118L2.04 9.398c-.783-.57-.38-1.81.588-1.81h4.174a1 1 0 00.95-.69l1.286-3.97z" />
                                        </svg>
                                    @endfor
                                </div>
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">{{ $review->comment }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 dark:text-gray-400">No reviews found for this tutor.</p>
                @endif
            </div>

            <div class="mt-6">
                <a href="{{ route('admin.tutors') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    Back to Tutors List
                </a>
            </div>
        </div>
    </div>
@endsection
