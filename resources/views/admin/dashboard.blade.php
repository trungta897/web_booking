<x-admin-layout>
    <x-slot name="header">
        <h2>Dashboard</h2>
    </x-slot>

    <div class="admin-dashboard-grid">
        <!-- Total Students Card -->
        <div class="admin-stat-card">
            <div class="admin-stat-icon primary">
                <i class="fas fa-user-graduate text-xl"></i>
            </div>
            <div class="admin-stat-value">{{ $totalStudents ?? 0 }}</div>
            <div class="admin-stat-label">Total Students</div>
        </div>

        <!-- Total Tutors Card -->
        <div class="admin-stat-card">
            <div class="admin-stat-icon success">
                <i class="fas fa-chalkboard-teacher text-xl"></i>
            </div>
            <div class="admin-stat-value">{{ $totalTutors ?? 0 }}</div>
            <div class="admin-stat-label">Total Tutors</div>
        </div>

        <!-- Total Admins Card -->
        <div class="admin-stat-card">
            <div class="admin-stat-icon info">
                <i class="fas fa-user-shield text-xl"></i>
            </div>
            <div class="admin-stat-value">{{ $totalAdmins ?? 0 }}</div>
            <div class="admin-stat-label">Total Admins</div>
        </div>

        <!-- Active Bookings Card -->
        <div class="admin-stat-card">
            <div class="admin-stat-icon warning">
                <i class="fas fa-calendar-check text-xl"></i>
            </div>
            <div class="admin-stat-value">{{ $activeBookings ?? 0 }}</div>
            <div class="admin-stat-label">Active Bookings</div>
        </div>

        <!-- Total Revenue Card -->
        <div class="admin-stat-card">
            <div class="admin-stat-icon danger">
                <i class="fas fa-dollar-sign text-xl"></i>
            </div>
            <div class="admin-stat-value">${{ number_format($totalRevenue ?? 0, 2) }}</div>
            <div class="admin-stat-label">Total Revenue</div>
        </div>
    </div>

    <!-- Recent Bookings -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3>Recent Bookings</h3>
            <a href="{{ route('admin.bookings') }}" class="text-sm text-indigo-600 hover:text-indigo-900">View All</a>
        </div>
        <div class="admin-card-body">
            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Tutor</th>
                            <th>Subject</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentBookings ?? [] as $booking)
                            <tr>
                                <td>{{ optional($booking->student)->name }}</td>
                                <td>{{ optional($booking->tutor)->name }}</td>
                                <td>{{ optional($booking->subject)->name }}</td>
                                <td>{{ $booking->date->format('M d, Y') }}</td>
                                <td>
                                    <span class="admin-badge {{ $booking->status === 'confirmed' ? 'admin-badge-success' : 'admin-badge-warning' }}">
                                        {{ ucfirst($booking->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-gray-500">No recent bookings</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Reviews -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3>Recent Reviews</h3>
            <a href="{{ route('admin.reviews') }}" class="text-sm text-indigo-600 hover:text-indigo-900">View All</a>
        </div>
        <div class="admin-card-body">
            <div class="space-y-4">
                @forelse($recentReviews ?? [] as $review)
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0">
                            <img class="h-10 w-10 rounded-full" src="{{ $review->student->profile_photo_url }}" alt="{{ $review->student->name }}">
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <h4 class="text-sm font-medium text-gray-900">{{ $review->student->name }}</h4>
                                <div class="flex items-center">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                                    @endfor
                                </div>
                            </div>
                            <p class="mt-1 text-sm text-gray-600">{{ $review->comment }}</p>
                            <p class="mt-1 text-xs text-gray-500">For {{ $review->tutor->name }} - {{ $review->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-gray-500">No recent reviews</p>
                @endforelse
            </div>
        </div>
    </div>
</x-admin-layout>
