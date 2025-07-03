@extends('layouts.admin')

@section('content')
    <!-- Header -->
    <div class="admin-page-header bg-white border-b border-gray-200 dark:bg-gray-800 dark:border-gray-700 px-6 py-4">
        <h2>{{ __('admin.dashboard') }}</h2>
    </div>

    <!-- Main Content -->
    <div class="px-6 py-4">
        <!-- Debug Information -->
        <div style="background: #f8f9fa; border: 1px solid #dee2e6; padding: 15px; margin-bottom: 20px; border-radius: 5px;">
            <h4 style="color: #495057; margin: 0 0 10px 0;">🔍 DEBUG INFO:</h4>
            <div style="font-family: monospace; font-size: 12px;">
                <p><strong>Total Students:</strong> {{ var_export($totalStudents ?? 'UNDEFINED', true) }}</p>
                <p><strong>Total Tutors:</strong> {{ var_export($totalTutors ?? 'UNDEFINED', true) }}</p>
                <p><strong>Total Admins:</strong> {{ var_export($totalAdmins ?? 'UNDEFINED', true) }}</p>
                <p><strong>Active Bookings:</strong> {{ var_export($activeBookings ?? 'UNDEFINED', true) }}</p>
                <p><strong>Total Revenue:</strong> {{ var_export($totalRevenue ?? 'UNDEFINED', true) }}</p>
                <p><strong>Recent Bookings:</strong> {{ isset($recentBookings) ? get_class($recentBookings) . ' (' . $recentBookings->count() . ' items)' : 'UNDEFINED' }}</p>
                @if(isset($error))
                    <p style="color: red;"><strong>Error:</strong> {{ $error }}</p>
                @endif
            </div>
        </div>

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
    </div>
@endsection
