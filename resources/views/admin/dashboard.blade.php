<x-admin-layout>
    <x-slot name="header">
        <h2>{{ __('admin.dashboard') }}</h2>
    </x-slot>

    <div class="admin-dashboard-grid">
        <!-- Total Students Card -->
        <div class="admin-stat-card">
            <div class="admin-stat-icon primary">
                <i class="fas fa-user-graduate text-xl"></i>
            </div>
            <div class="admin-stat-value">{{ $totalStudents ?? 0 }}</div>
            <div class="admin-stat-label">{{ __('admin.total_students') }}</div>
        </div>

        <!-- Total Tutors Card -->
        <div class="admin-stat-card">
            <div class="admin-stat-icon success">
                <i class="fas fa-chalkboard-teacher text-xl"></i>
            </div>
            <div class="admin-stat-value">{{ $totalTutors ?? 0 }}</div>
            <div class="admin-stat-label">{{ __('admin.total_tutors') }}</div>
        </div>

        <!-- Total Admins Card -->
        <div class="admin-stat-card">
            <div class="admin-stat-icon info">
                <i class="fas fa-user-shield text-xl"></i>
            </div>
            <div class="admin-stat-value">{{ $totalAdmins ?? 0 }}</div>
            <div class="admin-stat-label">{{ __('admin.total_admins') }}</div>
        </div>

        <!-- Active Bookings Card -->
        <div class="admin-stat-card">
            <div class="admin-stat-icon warning">
                <i class="fas fa-calendar-check text-xl"></i>
            </div>
            <div class="admin-stat-value">{{ $activeBookings ?? 0 }}</div>
            <div class="admin-stat-label">{{ __('admin.active_bookings') }}</div>
        </div>

        <!-- Total Revenue Card -->
        <div class="admin-stat-card">
            <div class="admin-stat-icon danger">
                <i class="fas fa-dollar-sign text-xl"></i>
            </div>
            <div class="admin-stat-value">${{ number_format($totalRevenue ?? 0, 2) }}</div>
            <div class="admin-stat-label">{{ __('admin.total_revenue') }}</div>
        </div>
    </div>

    <!-- Recent Bookings -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3>{{ __('admin.recent_bookings') }}</h3>
            <a href="{{ route('admin.bookings') }}" class="text-sm text-indigo-600 hover:text-indigo-900">{{ __('admin.view_all') }}</a>
        </div>
        <div class="admin-card-body">
            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>{{ __('admin.student') }}</th>
                            <th>{{ __('admin.tutor') }}</th>
                            <th>{{ __('admin.subject') }}</th>
                            <th>{{ __('admin.date') }}</th>
                            <th>{{ __('admin.status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentBookings ?? [] as $booking)
                            <tr>
                                <td>{{ optional($booking->student)->name }}</td>
                                <td>{{ optional($booking->tutor)->name }}</td>
                                <td>{{ optional($booking->subject)->name }}</td>
                                <td>{{ \Carbon\Carbon::parse($booking->start_time)->format('M d, Y') }}</td>
                                <td>
                                    <span class="admin-badge {{ $booking->status === 'confirmed' ? 'admin-badge-success' : 'admin-badge-warning' }}">
                                        {{ __('admin.' . $booking->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-gray-500">{{ __('admin.no_bookings_found') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>


</x-admin-layout>
