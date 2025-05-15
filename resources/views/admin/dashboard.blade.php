<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="admin-page py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Stats Overview -->
            <div class="admin-dashboard-grid mb-8">
                <!-- Total Tutors -->
                <div class="admin-stat-card">
                    <div class="admin-stat-icon bg-indigo-500 text-white">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div class="admin-stat-content">
                        <div class="admin-stat-label">Total Tutors</div>
                        <div class="admin-stat-value">{{ $stats['tutors_count'] }}</div>
                    </div>
                </div>

                <!-- Total Students -->
                <div class="admin-stat-card">
                    <div class="admin-stat-icon bg-green-500 text-white">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path d="M12 14l9-5-9-5-9 5 9 5z" />
                            <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                        </svg>
                    </div>
                    <div class="admin-stat-content">
                        <div class="admin-stat-label">Total Students</div>
                        <div class="admin-stat-value">{{ $stats['students_count'] }}</div>
                    </div>
                </div>

                <!-- Total Bookings -->
                <div class="admin-stat-card">
                    <div class="admin-stat-icon bg-yellow-500 text-white">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="admin-stat-content">
                        <div class="admin-stat-label">Total Bookings</div>
                        <div class="admin-stat-value">{{ $stats['bookings_count'] }}</div>
                    </div>
                </div>

                <!-- Total Revenue -->
                <div class="admin-stat-card">
                    <div class="admin-stat-icon bg-purple-500 text-white">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="admin-stat-content">
                        <div class="admin-stat-label">Total Revenue</div>
                        <div class="admin-stat-value">${{ number_format($stats['revenue'], 2) }}</div>
                    </div>
                </div>
            </div>

            <!-- Quick Access -->
            <div class="admin-card mb-8">
                <div class="admin-card-header">
                    <h3 class="text-lg font-medium text-gray-900">Quick Access</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <a href="{{ route('admin.tutors') }}" class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-900">Manage Tutors</span>
                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                    <a href="{{ route('admin.students') }}" class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-900">Manage Students</span>
                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                    <a href="{{ route('admin.bookings') }}" class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-900">Manage Bookings</span>
                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                    <a href="{{ route('admin.subjects') }}" class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-900">Manage Subjects</span>
                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                    <a href="{{ route('admin.reports') }}" class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-900">View Reports</span>
                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Recent Bookings -->
            <div class="admin-card mb-8">
                <div class="admin-card-header">
                    <h3 class="text-lg font-medium text-gray-900">Recent Bookings</h3>
                    <a href="{{ route('admin.bookings') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">View All</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Tutor</th>
                                <th>Subject</th>
                                <th>Date & Time</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recent_bookings as $booking)
                                <tr>
                                    <td>
                                        <div class="text-sm font-medium text-gray-900">{{ $booking->student->name }}</div>
                                    </td>
                                    <td>
                                        <div class="text-sm font-medium text-gray-900">{{ $booking->tutor->user->name }}</div>
                                    </td>
                                    <td>
                                        <div class="text-sm text-gray-900">{{ $booking->subject->name }}</div>
                                    </td>
                                    <td>
                                        <div class="text-sm text-gray-900">{{ $booking->start_time->format('M d, Y') }}</div>
                                        <div class="text-sm text-gray-500">{{ $booking->start_time->format('g:i A') }} - {{ $booking->end_time->format('g:i A') }}</div>
                                    </td>
                                    <td>
                                        <span class="admin-badge
                                            @if($booking->status === 'accepted') admin-badge-success
                                            @elseif($booking->status === 'pending') admin-badge-warning
                                            @elseif($booking->status === 'cancelled') admin-badge-danger
                                            @elseif($booking->status === 'completed') admin-badge-info
                                            @endif">
                                            {{ ucfirst($booking->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-sm text-gray-500 py-4">
                                        No bookings found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Popular Subjects & Top Tutors -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Popular Subjects -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h3 class="text-lg font-medium text-gray-900">Popular Subjects</h3>
                        <a href="{{ route('admin.subjects') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">View All</a>
                    </div>
                    <ul class="divide-y divide-gray-200">
                        @forelse($popular_subjects as $subject)
                            <li class="py-3 flex justify-between items-center">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $subject->name }}</p>
                                    <p class="text-sm text-gray-500">{{ $subject->tutors_count }} tutors</p>
                                </div>
                                <div class="text-sm text-gray-900">{{ $subject->bookings_count }} bookings</div>
                            </li>
                        @empty
                            <li class="py-3 text-center text-sm text-gray-500">
                                No subjects found.
                            </li>
                        @endforelse
                    </ul>
                </div>

                <!-- Top Tutors -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h3 class="text-lg font-medium text-gray-900">Top Tutors</h3>
                        <a href="{{ route('admin.tutors') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">View All</a>
                    </div>
                    <ul class="divide-y divide-gray-200">
                        @forelse($top_tutors as $tutor)
                            <li class="py-3 flex items-center">
                                <div class="flex-shrink-0 mr-4">
                                    <img class="h-10 w-10 rounded-full" src="{{ $tutor->user->avatar ? asset('storage/' . $tutor->user->avatar) : asset('images/default-avatar.png') }}" alt="{{ $tutor->user->name }}">
                                </div>
                                <div class="flex-grow">
                                    <p class="text-sm font-medium text-gray-900">{{ $tutor->user->name }}</p>
                                    <div class="flex items-center">
                                        <div class="flex items-center">
                                            @for($i = 1; $i <= 5; $i++)
                                                <svg class="h-4 w-4 {{ $i <= $tutor->reviews_avg_rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                </svg>
                                            @endfor
                                            <span class="ml-1 text-sm text-gray-500">{{ number_format($tutor->reviews_avg_rating, 1) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-sm text-gray-500">{{ $tutor->bookings_count }} bookings</div>
                            </li>
                        @empty
                            <li class="py-3 text-center text-sm text-gray-500">
                                No tutors found.
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
