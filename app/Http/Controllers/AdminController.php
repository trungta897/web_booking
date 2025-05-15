<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\User;
use App\Models\Subject;
use App\Models\Tutor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Display the admin dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function dashboard()
    {
        // Get counts for dashboard cards
        $stats = [
            'tutors_count' => User::where('role', 'tutor')->count(),
            'students_count' => User::where('role', 'student')->count(),
            'bookings_count' => Booking::count(),
            'subjects_count' => Subject::count(),
            'completed_bookings' => Booking::where('status', 'completed')->count(),
            'pending_bookings' => Booking::where('status', 'pending')->count(),
            'accepted_bookings' => Booking::where('status', 'accepted')->count(),
            'revenue' => Booking::where('payment_status', 'paid')->sum('price'),
        ];

        // Get recent bookings
        $recent_bookings = Booking::with(['student', 'tutor.user', 'subject'])
            ->latest()
            ->take(5)
            ->get();

        // Get popular subjects
        $popular_subjects = Subject::withCount('tutors')
            ->withCount('bookings')
            ->orderByDesc('bookings_count')
            ->take(5)
            ->get();

        // Get top tutors
        $top_tutors = Tutor::with('user')
            ->withCount('bookings')
            ->withAvg('reviews', 'rating')
            ->orderByDesc('reviews_avg_rating')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'stats',
            'recent_bookings',
            'popular_subjects',
            'top_tutors'
        ));
    }

    /**
     * Display list of tutors for admin.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function tutors(Request $request)
    {
        $query = User::where('role', 'tutor')
            ->with('tutor.subjects');

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $tutors = $query->latest()->paginate(10);

        return view('admin.tutors', compact('tutors'));
    }

    /**
     * Display list of students for admin.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function students(Request $request)
    {
        $query = User::where('role', 'student');

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $students = $query->latest()->paginate(10);

        return view('admin.students', compact('students'));
    }

    /**
     * Display list of bookings for admin.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function bookings(Request $request)
    {
        $query = Booking::with(['student', 'tutor.user', 'subject']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        $bookings = $query->latest()->paginate(10);

        return view('admin.bookings', compact('bookings'));
    }

    /**
     * Display list of subjects for admin.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function subjects(Request $request)
    {
        $query = Subject::withCount('tutors')->withCount('bookings');

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        $subjects = $query->orderBy('name')->paginate(10);

        return view('admin.subjects', compact('subjects'));
    }

    /**
     * Display admin reports page.
     *
     * @return \Illuminate\View\View
     */
    public function reports()
    {
        // Bookings by month
        $bookings_by_month = DB::table('bookings')
            ->select(DB::raw('DATE_FORMAT(start_time, "%Y-%m") as month'), DB::raw('COUNT(*) as count'))
            ->where('start_time', '>=', now()->subYear())
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Revenue by month
        $revenue_by_month = DB::table('bookings')
            ->select(DB::raw('DATE_FORMAT(start_time, "%Y-%m") as month'), DB::raw('SUM(price) as revenue'))
            ->where('payment_status', 'paid')
            ->where('start_time', '>=', now()->subYear())
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Bookings by subject
        $bookings_by_subject = DB::table('bookings')
            ->join('subjects', 'bookings.subject_id', '=', 'subjects.id')
            ->select('subjects.name', DB::raw('COUNT(*) as count'))
            ->groupBy('subjects.name')
            ->orderByDesc('count')
            ->take(10)
            ->get();

        return view('admin.reports', compact(
            'bookings_by_month',
            'revenue_by_month',
            'bookings_by_subject'
        ));
    }
}
