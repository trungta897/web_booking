<?php

namespace App\Http\Controllers;

use App\Services\BookingService;
use App\Services\StudentService;
use App\Traits\HandlesControllerErrors;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StudentController extends Controller
{
    use HandlesControllerErrors;

    protected StudentService $studentService;

    protected BookingService $bookingService;

    public function __construct(StudentService $studentService, BookingService $bookingService)
    {
        $this->studentService = $studentService;
        $this->bookingService = $bookingService;
    }

    /**
     * Display student dashboard.
     */
    public function dashboard(): View
    {
        try {
            $user = Auth::user();
            $dashboardData = $this->studentService->getDashboardData($user);

            return view('students.dashboard', $dashboardData);
        } catch (Exception $e) {
            return $this->handleWebException($e, 'students.dashboard', [
                'totalBookings' => 0,
                'completedBookings' => 0,
                'pendingBookings' => 0,
                'upcomingBookings' => 0,
                'totalSpent' => 0,
                'totalTutors' => 0,
                'upcomingSessions' => collect(),
                'completedSessions' => collect(),
                'reviews' => collect(),
            ], 'Failed to load student dashboard');
        }
    }

    /**
     * Get dashboard stats (AJAX).
     */
    public function getDashboardStats(): \Illuminate\Http\JsonResponse
    {
        try {
            $user = Auth::user();
            $stats = $this->studentService->getStudentStatistics($user);

            return response()->json([
                'success' => true,
                'stats' => $stats,
            ]);
        } catch (Exception $e) {
            return $this->handleJsonException($e, 'Failed to load dashboard stats');
        }
    }

    /**
     * Get upcoming sessions.
     */
    public function getUpcomingSessions(): \Illuminate\Http\JsonResponse
    {
        try {
            $user = Auth::user();
            $sessions = $this->studentService->getUpcomingSessions($user);

            return response()->json([
                'success' => true,
                'sessions' => $sessions,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
