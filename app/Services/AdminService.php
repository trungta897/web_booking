<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Review;
use App\Models\Subject;
use App\Models\Tutor;
use App\Models\User;
use App\Repositories\BookingRepository;
use App\Repositories\SubjectRepository;
use App\Repositories\TutorRepository;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class AdminService extends BaseService
{
    protected UserRepository $userRepository;

    protected BookingRepository $bookingRepository;

    protected TutorRepository $tutorRepository;

    protected SubjectRepository $subjectRepository;

    public function __construct(
        UserRepository $userRepository,
        BookingRepository $bookingRepository,
        TutorRepository $tutorRepository,
        SubjectRepository $subjectRepository
    ) {
        $this->userRepository = $userRepository;
        $this->bookingRepository = $bookingRepository;
        $this->tutorRepository = $tutorRepository;
        $this->subjectRepository = $subjectRepository;
    }

    /**
     * Get admin dashboard statistics.
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
                'formatted_stats' => $this->formatDashboardStats($userStats, $bookingStats, $revenueStats),
            ];
        });
    }

    /**
     * Get all users with filters.
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
     * Get all bookings with filters.
     */
    public function getAllBookings(array $filters = []): LengthAwarePaginator
    {
        return $this->bookingRepository->searchBookings($filters);
    }

    /**
     * Get all tutors with their statistics.
     */
    public function getAllTutors(array $filters = []): LengthAwarePaginator
    {
        return $this->tutorRepository->getTutorsWithFilters($filters);
    }

    /**
     * Get all subjects with statistics.
     */
    public function getAllSubjects(): Collection
    {
        return $this->subjectRepository->getSubjectsWithTutorCount();
    }

    /**
     * Suspend/unsuspend user.
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
                'new_status' => $newStatus,
            ]);

            return $user->fresh();
        });
    }

    /**
     * Delete user account.
     */
    public function deleteUser(int $userId): bool
    {
        return $this->executeTransaction(function () use ($userId) {
            $user = $this->userRepository->findByIdOrFail($userId);

            // Cast to User model to ensure proper type
            if (!$user instanceof User) {
                throw new Exception('Invalid user type');
            }

            $this->validateUserCanBeDeleted($user);

            $result = $this->userRepository->delete($user->id);

            if ($result) {
                $this->logActivity('User deleted', [
                    'user_id' => $userId,
                    'email' => $user->email,
                    'role' => $user->role,
                ]);
            }

            return $result;
        });
    }

    /**
     * Validate if user can be deleted.
     */
    protected function validateUserCanBeDeleted(User $user): void
    {
        $activeBookings = $this->getActiveBookingsCount($user);

        if ($activeBookings > 0) {
            throw new Exception(__('Cannot delete user with active bookings'));
        }
    }

    /**
     * Get count of active bookings for user.
     */
    protected function getActiveBookingsCount(User $user): int
    {
        if ($user->role === 'student') {
            return Booking::where('student_id', $user->id)
                ->where(function ($query) {
                    $query->where(function ($q) {
                        $q->where('is_confirmed', false)
                          ->where('is_cancelled', false)
                          ->where('is_completed', false); // Pending bookings
                    })->orWhere('is_confirmed', true); // Accepted bookings
                })
                ->count();
        }

        return Booking::whereHas('tutor', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->where(function ($query) {
            $query->where(function ($q) {
                $q->where('is_confirmed', false)
                  ->where('is_cancelled', false)
                  ->where('is_completed', false); // Pending bookings
            })->orWhere('is_confirmed', true); // Accepted bookings
        })->count();
    }

    /**
     * Get booking statistics.
     */
    protected function getBookingStatistics(): array
    {
        // BOOLEAN LOGIC: Get booking statistics using boolean fields
        $total = Booking::count();
        $pending = Booking::where('is_confirmed', false)->where('is_cancelled', false)->where('is_completed', false)->count();
        $accepted = Booking::where('is_confirmed', true)->where('is_completed', false)->count();
        $completed = Booking::where('is_completed', true)->count();
        $cancelled = Booking::where('is_cancelled', true)->count();

        return [
            'total' => $total,
            'pending' => $pending,
            'accepted' => $accepted,
            'completed' => $completed,
            'cancelled' => $cancelled,
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
            'cancellation_rate' => $total > 0 ? round(($cancelled / $total) * 100, 2) : 0,
        ];
    }

    /**
     * Get revenue statistics.
     */
    protected function getRevenueStatistics(): array
    {
        // 🎯 BOOLEAN LOGIC: Use payment_at instead of payment_status for revenue
        $totalRevenue = Booking::whereNotNull('payment_at')
            ->sum('price');

        $monthlyRevenue = Booking::whereNotNull('payment_at')
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('price');

        $weeklyRevenue = Booking::whereNotNull('payment_at')
            ->whereBetween('created_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek(),
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
                'weekly' => $this->formatCurrency($weeklyRevenue),
            ],
        ];
    }

    /**
     * Get system statistics.
     */
    protected function getSystemStatistics(): array
    {
        return [
            'total_subjects' => Subject::count(),
            'active_subjects' => Subject::active()->count(),
            'total_reviews' => Review::count(),
            'average_rating' => Review::avg('rating'),
            'storage_used' => $this->getStorageUsage(),
            'cache_size' => $this->getCacheSize(),
        ];
    }

    /**
     * Get recent activities.
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
                    'url' => route('admin.bookings.show', $booking),
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
                    'url' => '#',
                ];
            });

        return $recentBookings->merge($recentUsers)
            ->sortByDesc('timestamp')
            ->take($limit)
            ->values()
            ->all();
    }

    /**
     * Get daily revenue for the last 7 days.
     */
    protected function getDailyRevenue(): array
    {
        $revenues = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            // 🎯 BOOLEAN LOGIC: Use payment_at instead of payment_status
            $revenue = Booking::whereNotNull('payment_at')
                ->whereDate('created_at', $date)
                ->sum('price');

            $revenues[] = [
                'date' => $date->format('Y-m-d'),
                'formatted_date' => $date->format('d-m-Y'),
                'revenue' => $revenue,
                'formatted_revenue' => $this->formatCurrency($revenue),
            ];
        }

        return $revenues;
    }

    /**
     * Format dashboard statistics for display.
     */
    protected function formatDashboardStats(array $userStats, array $bookingStats, array $revenueStats): array
    {
        return [
            'users' => [
                'total' => number_format($userStats['total_users']),
                'students' => number_format($userStats['students']),
                'tutors' => number_format($userStats['tutors']),
                'active' => number_format($userStats['active_users']),
            ],
            'bookings' => [
                'total' => number_format($bookingStats['total']),
                'completion_rate' => $bookingStats['completion_rate'] . '%',
                'cancellation_rate' => $bookingStats['cancellation_rate'] . '%',
            ],
            'revenue' => $revenueStats['formatted'],
        ];
    }

    /**
     * Get storage usage (placeholder).
     */
    protected function getStorageUsage(): string
    {
        // This would implement actual storage calculation
        return '0 MB';
    }

    /**
     * Get cache size (placeholder).
     */
    protected function getCacheSize(): string
    {
        // This would implement actual cache size calculation
        return '0 MB';
    }

    /**
     * Export data to CSV.
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
     * Export users to CSV.
     */
    protected function exportUsers(array $filters): string
    {
        // Implementation would generate CSV content
        return "Name,Email,Role,Status,Created At\n";
    }

    /**
     * Export bookings to CSV.
     */
    protected function exportBookings(array $filters): string
    {
        // Implementation would generate CSV content
        return "ID,Student,Tutor,Subject,Status,Price,Created At\n";
    }

    /**
     * Export tutors to CSV.
     */
    protected function exportTutors(array $filters): string
    {
        // Implementation would generate CSV content
        return "Name,Email,Subjects,Rating,Bookings,Created At\n";
    }

    /**
     * Get recent bookings for dashboard.
     */
    public function getRecentBookings(int $limit = 5): Collection
    {
        return Booking::with(['student', 'tutor.user', 'subject'])
            ->latest()
            ->take($limit)
            ->get();
    }

    /**
     * Get popular subjects.
     */
    public function getPopularSubjects(int $limit = 5): Collection
    {
        return Subject::withCount(['tutors', 'bookings'])
            ->orderByDesc('bookings_count')
            ->take($limit)
            ->get();
    }

    /**
     * Get top rated tutors.
     */
    public function getTopRatedTutors(int $limit = 6): Collection
    {
        return User::where('role', 'tutor')
            ->with('tutor')
            ->withAvg('reviewsReceived', 'rating')
            ->orderByDesc('reviews_received_avg_rating')
            ->take($limit)
            ->get();
    }

    /**
     * Get recently joined users.
     */
    public function getRecentUsers(int $limit = 5): Collection
    {
        return User::latest()->take($limit)->get();
    }

    /**
     * Get tutors with search and pagination.
     */
    public function getTutors(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        $query = User::where('role', 'tutor')
            ->with(['tutor.subjects', 'reviewsReceived'])
            ->withAvg('reviewsReceived', 'rating')
            ->withCount('reviewsReceived');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Get students with search and pagination.
     */
    public function getStudents(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        $query = User::where('role', 'student')
            ->withCount('studentBookings');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Get tutor details with related data.
     */
    public function getTutorDetails(User $user): array
    {
        if ($user->role !== 'tutor') {
            throw new Exception('User is not a tutor');
        }

        $user->load([
            'tutor.subjects',
            'tutor.education',  // Thêm dòng này để load education
            'tutorBookings.student',
            'tutorBookings.subject',
            'reviewsReceived.reviewer',
        ]);

        $averageRating = $user->reviewsReceived->avg('rating');
        $totalBookings = $user->tutorBookings->count();
        // 🎯 BOOLEAN LOGIC: Count completed bookings
        $completedBookings = $user->tutorBookings->where('is_completed', true)->count();

        return [
            'user' => $user,
            'averageRating' => $averageRating, // Thêm biến này vào return array
            'average_rating' => $averageRating,
            'total_bookings' => $totalBookings,
            'completed_bookings' => $completedBookings,
        ];
    }

    /**
     * Get student details with related data.
     */
    public function getStudentDetails(User $user): array
    {
        if ($user->role !== 'student') {
            throw new Exception('User is not a student');
        }

        $user->load([
            'studentBookings.tutor.user',
            'studentBookings.subject',
        ]);

        $totalBookings = $user->studentBookings->count();
        // 🎯 BOOLEAN LOGIC: Total spent from completed bookings
        $totalSpent = $user->studentBookings->where('is_completed', true)->sum('price');

        return [
            'user' => $user,
            'total_bookings' => $totalBookings,
            'total_spent' => $totalSpent,
        ];
    }

    /**
     * Toggle user account status.
     */
    public function toggleUserStatus(User $user): bool
    {
        return $this->executeTransaction(function () use ($user) {
            $oldStatus = $user->account_status;
            $newStatus = $oldStatus === 'suspended' ? 'active' : 'suspended';

            $user->update(['account_status' => $newStatus]);

            // Additional logic for tutors
            if ($user->role === 'tutor' && $user->tutor) {
                $user->tutor->update([
                    'is_available' => $newStatus === 'active',
                ]);
            }

            $this->logActivity('User status changed', [
                'user_id' => $user->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'role' => $user->role,
            ]);

            return true;
        });
    }

    /**
     * Get bookings with search and pagination.
     */
    public function getBookings(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        $query = Booking::with(['student', 'tutor.user', 'subject']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('student', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%");
                })
                    ->orWhereHas('tutor.user', function ($tq) use ($search) {
                        $tq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('subject', function ($subq) use ($search) {
                        $subq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Get booking details.
     */
    public function getBookingDetails(Booking $booking): Booking
    {
        return $booking->load([
            'student',
            'tutor.user',
            'subject',
            'reviews',
        ]);
    }

    /**
     * Get subjects with search and pagination.
     */
    public function getSubjects(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        $query = Subject::withCount(['tutors', 'bookings']);

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Create new subject.
     */
    public function createSubject(array $data): Subject
    {
        return $this->executeTransaction(function () use ($data) {
            $subject = Subject::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'icon' => $data['icon'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            $this->logActivity('Subject created', [
                'subject_id' => $subject->id,
                'name' => $subject->name,
            ]);

            return $subject;
        });
    }

    /**
     * Update subject.
     */
    public function updateSubject(Subject $subject, array $data): bool
    {
        return $this->executeTransaction(function () use ($subject, $data) {
            $oldData = $subject->toArray();

            $subject->update([
                'name' => $data['name'],
                'description' => $data['description'] ?? $subject->description,
                'icon' => $data['icon'] ?? $subject->icon,
                'is_active' => $data['is_active'] ?? $subject->is_active,
            ]);

            $this->logActivity('Subject updated', [
                'subject_id' => $subject->id,
                'old_data' => $oldData,
                'new_data' => $subject->fresh()->toArray(),
            ]);

            return true;
        });
    }

    /**
     * Delete subject.
     */
    public function deleteSubject(Subject $subject): bool
    {
        return $this->executeTransaction(function () use ($subject) {
            // Check if subject has active bookings or tutors
            $activeBookings = $subject->bookings()->where(function ($query) {
                $query->where(function ($q) {
                    $q->where('is_confirmed', false)
                      ->where('is_cancelled', false)
                      ->where('is_completed', false); // Pending bookings
                })->orWhere('is_confirmed', true); // Accepted bookings
            })->count();
            $activeTutors = $subject->tutors()->count();

            if ($activeBookings > 0) {
                throw new Exception('Cannot delete subject with active bookings');
            }

            if ($activeTutors > 0) {
                throw new Exception('Cannot delete subject with active tutors');
            }

            $subjectData = $subject->toArray();
            $result = $subject->delete();

            if ($result) {
                $this->logActivity('Subject deleted', [
                    'subject_data' => $subjectData,
                ]);
            }

            return $result;
        });
    }

    /**
     * Get reports data.
     */
    public function getReportsData(): array
    {
        $stats = $this->getDashboardStats();

        // Monthly booking statistics
        $monthlyBookings = Booking::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Revenue by month - 🎯 BOOLEAN LOGIC: Use payment_at instead of payment_status
        $monthlyRevenue = Booking::selectRaw('MONTH(created_at) as month, SUM(price) as revenue')
            ->whereNotNull('payment_at')
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return [
            'stats' => $stats,
            'monthly_bookings' => $monthlyBookings,
            'monthly_revenue' => $monthlyRevenue,
            'popular_subjects' => $this->getPopularSubjects(10),
            'top_tutors' => $this->getTopRatedTutors(10),
        ];
    }

    /**
     * Get reviews with pagination.
     */
    public function getReviews(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        $query = Review::with(['student', 'tutor.user', 'booking.subject']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('comment', 'like', "%{$search}%")
                    ->orWhereHas('student', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('tutor.user', function ($tq) use ($search) {
                        $tq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        return $query->latest()->paginate($perPage);
    }
}
