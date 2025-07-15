<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Review;
use App\Models\Subject;
use App\Models\User;
use App\Repositories\BookingRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class StudentService extends BaseService
{
    protected BookingRepository $bookingRepository;

    public function __construct()
    {
        $this->bookingRepository = new BookingRepository(new Booking());
    }

    /**
     * Get dashboard data for student.
     */
    public function getDashboardData(User $student): array
    {
        // Get bookings for this student
        $bookings = Booking::where('student_id', $student->id)
            ->with(['tutor.user', 'subject'])
            ->orderBy('start_time', 'desc')
            ->get();

        // Calculate statistics
        $totalBookings = $bookings->count();
        $completedBookings = $bookings->where('is_completed', true)->count();
        $pendingBookings = $bookings->where('is_confirmed', false)->where('is_cancelled', false)->where('is_completed', false)->count();
        $upcomingBookings = $bookings->where('is_confirmed', true)
            ->where('start_time', '>', Carbon::now())
            ->count();

        // Calculate total spent (from completed bookings)
        $totalSpent = $bookings->where('is_completed', true)
            ->sum('price');

        // Get unique tutors count
        $totalTutors = $bookings->pluck('tutor_id')->unique()->count();

        // Get upcoming sessions (next 5)
        $upcomingSessions = $bookings->where('is_confirmed', true)
            ->where('start_time', '>', Carbon::now())
            ->sortBy('start_time')
            ->take(5);

        // Get completed sessions for history (last 10)
        $completedSessions = $bookings->where('is_completed', true)
            ->sortByDesc('start_time')
            ->take(10);

        // Get reviews made by this student
        $reviews = Review::where('student_id', $student->id)
            ->with(['tutor.user', 'booking.subject'])
            ->latest()
            ->take(5)
            ->get();

        return [
            'totalBookings' => $totalBookings,
            'completedBookings' => $completedBookings,
            'pendingBookings' => $pendingBookings,
            'upcomingBookings' => $upcomingBookings,
            'totalSpent' => $totalSpent,
            'totalTutors' => $totalTutors,
            'upcomingSessions' => $upcomingSessions,
            'completedSessions' => $completedSessions,
            'reviews' => $reviews,
        ];
    }

    /**
     * Get student statistics.
     */
    public function getStudentStatistics(User $student): array
    {
        $bookings = $this->bookingRepository->getStudentBookings($student->id);

        $stats = [
            'total_bookings' => $bookings->count(),
            'total_spent' => $bookings->where('is_completed', true)->sum('price'),
            'pending_bookings' => $bookings->where('is_pending', true)->count(),
            'upcoming_bookings' => $bookings->where('is_accepted', true)
                ->where('start_time', '>', now())->count(),
        ];

        // Calculate average session cost
        if ($stats['completed_bookings'] > 0) {
            $stats['average_session_cost'] = $stats['total_spent'] / $stats['completed_bookings'];
        }

        // Calculate total hours
        $completedBookings = $bookings->where('status', 'completed');
        foreach ($completedBookings as $booking) {
            $start = Carbon::parse($booking->start_time);
            $end = Carbon::parse($booking->end_time);
            $stats['total_hours'] += $start->diffInHours($end);
        }

        // Get favorite subjects (most booked)
        $subjectCounts = $bookings->groupBy('subject_id')->map->count()->sortDesc()->take(3);
        $stats['favorite_subjects'] = $subjectCounts->keys()->map(function ($subjectId) {
            return Subject::find($subjectId);
        })->filter();

        return $stats;
    }

    /**
     * Get upcoming sessions.
     */
    public function getUpcomingSessions(User $student): Collection
    {
        return Booking::where('student_id', $student->id)
            ->where('status', 'confirmed')
            ->where('start_time', '>', Carbon::now())
            ->with(['tutor.user', 'subject'])
            ->orderBy('start_time')
            ->limit(10)
            ->get();
    }

    /**
     * Get completed sessions.
     */
    public function getCompletedSessions(User $student, int $limit = 10): Collection
    {
        return Booking::where('student_id', $student->id)
            ->where('status', 'completed')
            ->with(['tutor.user', 'subject'])
            ->orderBy('start_time', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get student reviews.
     */
    public function getStudentReviews(User $student, int $limit = 5): Collection
    {
        return Review::where('student_id', $student->id)
            ->with(['tutor.user', 'booking.subject'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get learning progress.
     */
    public function getLearningProgress(User $student): array
    {
        $bookings = $this->bookingRepository->getStudentBookings($student->id);

        $monthlyProgress = [];
        $currentMonth = Carbon::now()->startOfMonth();

        for ($i = 0; $i < 12; $i++) {
            $month = $currentMonth->copy()->subMonths($i);
            $monthlyBookings = $bookings->filter(function ($booking) use ($month) {
                return Carbon::parse($booking->start_time)->isSameMonth($month);
            });

            $monthlyProgress[] = [
                'month' => $month->format('M Y'),
                'bookings' => $monthlyBookings->count(),
                'completed' => $monthlyBookings->where('status', 'completed')->count(),
                'hours' => $this->calculateTotalHours($monthlyBookings->where('status', 'completed')),
            ];
        }

        return array_reverse($monthlyProgress);
    }

    /**
     * Calculate total hours from bookings.
     */
    protected function calculateTotalHours($bookings): float
    {
        $totalMinutes = 0;

        foreach ($bookings as $booking) {
            $start = Carbon::parse($booking->start_time);
            $end = Carbon::parse($booking->end_time);
            $totalMinutes += $start->diffInMinutes($end);
        }

        return round($totalMinutes / 60, 2);
    }
}
