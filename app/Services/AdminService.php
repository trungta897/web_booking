<?php

namespace App\Services;

use App\Models\User;
use App\Models\Booking;
use App\Models\Subject;
use App\Models\Tutor;
use App\Models\Review;
use App\Repositories\UserRepository;
use App\Repositories\BookingRepository;
use App\Repositories\TutorRepository;
use App\Repositories\SubjectRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Exception;

class AdminService extends BaseService
{
    protected UserRepository $userRepository;
    protected BookingRepository $bookingRepository;
    protected TutorRepository $tutorRepository;
    protected SubjectRepository $subjectRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository(new User());
        $this->bookingRepository = new BookingRepository(new Booking());
        $this->tutorRepository = new TutorRepository(new Tutor());
        $this->subjectRepository = new SubjectRepository(new Subject());
    }

    /**
     * Get admin dashboard statistics
     */
    public function getDashboardStats(): array
    {
        return Cache::remember('admin.dashboard.stats', 300, function () {
            $userStats = $this->userRepository->getGeneralUserStatistics();
            $bookingStats = $this->getBookingStatistics();
            $revenueStats = $this->getRevenueStatistics();
            $systemStats = $this->getSystemStatistics();

            return [
                'users' => $userStats,
                'bookings' => $bookingStats,
                'revenue' => $revenueStats,
                'system' => $systemStats,
                'recent_activities' => $this->getRecentActivities(),
                'formatted_stats' => $this->formatDashboardStats($userStats, $bookingStats, $revenueStats)
            ];
        });
    }

    /**
     * Get all users with filters
     */
    public function getAllUsers(array $filters = []): LengthAwarePaginator
    {
        if (isset($filters['search']) && !empty($filters['search'])) {
            return $this->userRepository->searchUsers($filters['search'], $filters['per_page'] ?? 15);
        }

        if (isset($filters['role']) && !empty($filters['role'])) {
            // Use pagination instead of collection
            return $this->userRepository->where('role', $filters['role'])
                ->latest()
                ->paginate($filters['per_page'] ?? 15);
        }

        if (isset($filters['status']) && $filters['status'] === 'active') {
            // Use pagination instead of collection
            return $this->userRepository->where('account_status', 'active')
                ->latest()
                ->paginate($filters['per_page'] ?? 15);
        }

        return $this->userRepository->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Get all bookings with filters
     */
    public function getAllBookings(array $filters = []): LengthAwarePaginator
    {
        return $this->bookingRepository->searchBookings($filters);
    }

    /**
     * Get all tutors with their statistics
     */
    public function getAllTutors(array $filters = []): LengthAwarePaginator
    {
        return $this->tutorRepository->getTutorsWithFilters($filters);
    }

    /**
     * Get all subjects with statistics
     */
    public function getAllSubjects(): Collection
    {
        return $this->subjectRepository->getSubjectsWithTutorCount();
    }

    /**
     * Suspend/unsuspend user
     */
    public function toggleUserSuspension(int $userId): User
    {
        return $this->executeTransaction(function () use ($userId) {
            $user = $this->userRepository->findByIdOrFail($userId);

            $newStatus = $user->account_status === 'active' ? 'suspended' : 'active';

            $this->userRepository->update($user->id, ['account_status' => $newStatus]);

            $this->logActivity('User suspension toggled', [
                'user_id' => $userId,
                'old_status' => $user->account_status,
                'new_status' => $newStatus
            ]);

            return $user->fresh();
        });
    }

    /**
     * Delete user account
     */
    public function deleteUser(int $userId): bool
    {
        return $this->executeTransaction(function () use ($userId) {
            $user = $this->userRepository->findByIdOrFail($userId);

            // Check if user has active bookings
            if ($user->role === 'student') {
                $activeBookings = Booking::where('student_id', $userId)
                    ->whereIn('status', ['pending', 'accepted'])
                    ->count();
            } else {
                $activeBookings = Booking::whereHas('tutor', function($q) use ($userId) {
                    $q->where('user_id', $userId);
                })->whereIn('status', ['pending', 'accepted'])->count();
            }

            if ($activeBookings > 0) {
                throw new Exception(__('Cannot delete user with active bookings'));
            }

            $result = $this->userRepository->delete($user->id);

            if ($result) {
                $this->logActivity('User deleted', [
                    'user_id' => $userId,
                    'email' => $user->email,
                    'role' => $user->role
                ]);
            }

            return $result;
        });
    }

    /**
     * Get booking statistics
     */
    protected function getBookingStatistics(): array
    {
        $total = Booking::count();
        $pending = Booking::where('status', 'pending')->count();
        $accepted = Booking::where('status', 'accepted')->count();
        $completed = Booking::where('status', 'completed')->count();
        $cancelled = Booking::where('status', 'cancelled')->count();

        return [
            'total' => $total,
            'pending' => $pending,
            'accepted' => $accepted,
            'completed' => $completed,
            'cancelled' => $cancelled,
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
            'cancellation_rate' => $total > 0 ? round(($cancelled / $total) * 100, 2) : 0
        ];
    }

    /**
     * Get revenue statistics
     */
    protected function getRevenueStatistics(): array
    {
        $totalRevenue = Booking::where('status', 'completed')
            ->where('payment_status', 'paid')
            ->sum('price');

        $monthlyRevenue = Booking::where('status', 'completed')
            ->where('payment_status', 'paid')
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('price');

        $weeklyRevenue = Booking::where('status', 'completed')
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ])
            ->sum('price');

        return [
            'total' => $totalRevenue,
            'monthly' => $monthlyRevenue,
            'weekly' => $weeklyRevenue,
            'daily' => $this->getDailyRevenue(),
            'formatted' => [
                'total' => $this->formatCurrency($totalRevenue),
                'monthly' => $this->formatCurrency($monthlyRevenue),
                'weekly' => $this->formatCurrency($weeklyRevenue)
            ]
        ];
    }

    /**
     * Get system statistics
     */
    protected function getSystemStatistics(): array
    {
        return [
            'total_subjects' => Subject::count(),
            'active_subjects' => Subject::active()->count(),
            'total_reviews' => Review::count(),
            'average_rating' => Review::avg('rating'),
            'storage_used' => $this->getStorageUsage(),
            'cache_size' => $this->getCacheSize()
        ];
    }

    /**
     * Get recent activities
     */
    protected function getRecentActivities(int $limit = 10): array
    {
        $recentBookings = Booking::with(['student', 'tutor.user', 'subject'])
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function ($booking) {
                return [
                    'type' => 'booking',
                    'message' => "New booking: {$booking->student->name} booked {$booking->subject->name} with {$booking->tutor->user->name}",
                    'timestamp' => $booking->created_at,
                    'url' => route('admin.bookings.show', $booking)
                ];
            });

        $recentUsers = User::latest()
            ->limit($limit)
            ->get()
            ->map(function ($user) {
                return [
                    'type' => 'user',
                    'message' => "New {$user->role} registered: {$user->name}",
                    'timestamp' => $user->created_at,
                    'url' => '#'
                ];
            });

        return $recentBookings->merge($recentUsers)
            ->sortByDesc('timestamp')
            ->take($limit)
            ->values()
            ->all();
    }

    /**
     * Get daily revenue for the last 7 days
     */
    protected function getDailyRevenue(): array
    {
        $revenues = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $revenue = Booking::where('status', 'completed')
                ->where('payment_status', 'paid')
                ->whereDate('created_at', $date)
                ->sum('price');

            $revenues[] = [
                'date' => $date->format('Y-m-d'),
                'formatted_date' => $date->format('d-m-Y'),
                'revenue' => $revenue,
                'formatted_revenue' => $this->formatCurrency($revenue)
            ];
        }

        return $revenues;
    }

    /**
     * Format dashboard statistics for display
     */
    protected function formatDashboardStats(array $userStats, array $bookingStats, array $revenueStats): array
    {
        return [
            'users' => [
                'total' => number_format($userStats['total_users']),
                'students' => number_format($userStats['students']),
                'tutors' => number_format($userStats['tutors']),
                'active' => number_format($userStats['active_users'])
            ],
            'bookings' => [
                'total' => number_format($bookingStats['total']),
                'completion_rate' => $bookingStats['completion_rate'] . '%',
                'cancellation_rate' => $bookingStats['cancellation_rate'] . '%'
            ],
            'revenue' => $revenueStats['formatted']
        ];
    }

    /**
     * Get storage usage (placeholder)
     */
    protected function getStorageUsage(): string
    {
        // This would implement actual storage calculation
        return '0 MB';
    }

    /**
     * Get cache size (placeholder)
     */
    protected function getCacheSize(): string
    {
        // This would implement actual cache size calculation
        return '0 MB';
    }

    /**
     * Export data to CSV
     */
    public function exportData(string $type, array $filters = []): string
    {
        switch ($type) {
            case 'users':
                return $this->exportUsers($filters);
            case 'bookings':
                return $this->exportBookings($filters);
            case 'tutors':
                return $this->exportTutors($filters);
            default:
                throw new Exception(__('Invalid export type'));
        }
    }

    /**
     * Export users to CSV
     */
    protected function exportUsers(array $filters): string
    {
        // Implementation would generate CSV content
        return "Name,Email,Role,Status,Created At\n";
    }

    /**
     * Export bookings to CSV
     */
    protected function exportBookings(array $filters): string
    {
        // Implementation would generate CSV content
        return "ID,Student,Tutor,Subject,Status,Price,Created At\n";
    }

    /**
     * Export tutors to CSV
     */
    protected function exportTutors(array $filters): string
    {
        // Implementation would generate CSV content
        return "Name,Email,Subjects,Rating,Bookings,Created At\n";
    }
}
