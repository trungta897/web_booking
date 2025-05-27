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
        // Explicitly define stats for the view
        $totalStudents = User::where('role', 'student')->count();
        $totalTutors = User::where('role', 'tutor')->count();
        $totalAdmins = User::where('role', 'admin')->count();
        $activeBookings = Booking::where('status', 'accepted')->count(); // Assuming 'active' means 'accepted'
        $totalRevenue = Booking::where('payment_status', 'paid')->sum('price');

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

        // Get top rated tutor users (User model with tutor role)
        $topRatedTutorUsers = User::where('role', 'tutor')
            ->with('tutor')
            ->withAvg('reviewsReceived', 'rating')
            ->orderByDesc('reviews_received_avg_rating')
            ->take(6)
            ->get();

        // Get recently joined users
        $recentUsers = User::latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'totalStudents',
            'totalTutors',
            'totalAdmins',
            'activeBookings',
            'totalRevenue',
            'recent_bookings',
            'popular_subjects',
            'topRatedTutorUsers',
            'recentUsers'
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
     * Display the specified tutor.
     *
     * @param  \App\Models\User  $user // Type hint for the User model, which should be a tutor
     * @return \Illuminate\View\View
     */
    public function showTutor(User $user)
    {
        // Ensure the user is a tutor and load related data
        if ($user->role !== 'tutor') {
            abort(404, 'Tutor not found.');
        }
        $user->load('tutor.subjects', 'tutorBookings.student', 'tutorBookings.subject', 'reviewsReceived.reviewer');
        // Calculate average rating
        $averageRating = $user->reviewsReceived->avg('rating');

        return view('admin.tutors.show', compact('user', 'averageRating'));
    }

    /**
     * Suspend or reinstate the specified tutor account.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function suspendTutor(User $user)
    {
        // Ensure the user is a tutor
        if ($user->role !== 'tutor') {
            return redirect()->route('admin.tutors')->with('error', 'User is not a tutor.');
        }

        if ($user->account_status === 'suspended') {
            $user->account_status = 'active';
            $user->save();
            // Additionally, you might want to re-verify the tutor or reset their availability
            // For example: $user->tutor->update(['is_verified' => true, 'is_available' => true]);
            return redirect()->route('admin.tutors')->with('success', 'Tutor account reinstated successfully.');
        } else {
            $user->account_status = 'suspended';
            $user->save();
            // You might want to also mark the tutor as unavailable
            // For example: $user->tutor->update(['is_available' => false]);
            return redirect()->route('admin.tutors')->with('success', 'Tutor account suspended successfully.');
        }
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
     * Display the specified student.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\View\View
     */
    public function showStudent(User $user)
    {
        // Ensure the user is a student
        if ($user->role !== 'student') {
            abort(404, 'Student not found.');
        }
        // Eager load bookings for the student
        $user->load('studentBookings.tutor.user', 'studentBookings.subject');
        return view('admin.students.show', compact('user'));
    }

    /**
     * Suspend the specified student account.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function suspendStudent(User $user)
    {
        // Ensure the user is a student
        if ($user->role !== 'student') {
            return redirect()->route('admin.students')->with('error', 'User is not a student.');
        }

        if ($user->account_status === 'suspended') {
            $user->account_status = 'active';
            $user->save();
            return redirect()->route('admin.students')->with('success', 'Student account reinstated successfully.');
        } else {
            $user->account_status = 'suspended';
            $user->save();
            return redirect()->route('admin.students')->with('success', 'Student account suspended successfully.');
        }
    }

    /**
     * Display the specified booking.
     *
     * @param  \App\Models\Booking  $booking
     * @return \Illuminate\View\View
     */
    public function showBooking(Booking $booking)
    {
        $booking->load('student', 'tutor.user', 'subject', 'review'); // Eager load related models
        return view('admin.bookings.show', compact('booking'));
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
     * Store a newly created subject in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeSubject(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:subjects,name',
        ]);

        Subject::create($request->only('name'));

        return redirect()->route('admin.subjects')->with('success', 'Subject created successfully.');
    }

    /**
     * Show the form for creating a new subject.
     *
     * @return \Illuminate\View\View
     */
    public function createSubject()
    {
        return view('admin.subjects.create');
    }

    /**
     * Update the specified subject in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Subject  $subject
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateSubject(Request $request, Subject $subject)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:subjects,name,'. $subject->id,
        ]);

        $subject->update($request->only('name'));

        return redirect()->route('admin.subjects')->with('success', 'Subject updated successfully.');
    }

    /**
     * Show the form for editing the specified subject.
     *
     * @param  \App\Models\Subject  $subject
     * @return \Illuminate\View\View
     */
    public function editSubject(Subject $subject)
    {
        return view('admin.subjects.edit', compact('subject'));
    }

    /**
     * Remove the specified subject from storage.
     *
     * @param  \App\Models\Subject  $subject
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroySubject(Subject $subject)
    {
        // Optional: Add checks here if the subject is associated with tutors/bookings
        // and handle accordingly (e.g., prevent deletion or disassociate)
        try {
            $subject->delete();
            return redirect()->route('admin.subjects')->with('success', 'Subject deleted successfully.');
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle potential foreign key constraint violations if not handled by cascading deletes
            return redirect()->route('admin.subjects')->with('error', 'Could not delete subject. It might be in use.');
        }
    }

    /**
     * Show the form for confirming deletion of the specified subject.
     *
     * @param  \App\Models\Subject  $subject
     * @return \Illuminate\View\View
     */
    public function confirmDeleteSubject(Subject $subject)
    {
        return view('admin.subjects.confirm-delete', compact('subject'));
    }

    /**
     * Display the specified subject.
     *
     * @param  \App\Models\Subject  $subject
     * @return \Illuminate\View\View
     */
    public function showSubject(Subject $subject)
    {
        $subject->load('tutors.user', 'bookings.student', 'bookings.tutor.user'); // Eager load related tutors and bookings
        return view('admin.subjects.show', compact('subject'));
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

    /**
     * Display list of reviews for admin.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function reviews(Request $request)
    {
        $query = DB::table('reviews')
            ->join('users as students', 'reviews.student_id', '=', 'students.id')
            ->join('users as tutors', 'reviews.tutor_id', '=', 'tutors.id')
            ->select(
                'reviews.*',
                'students.name as student_name',
                'students.profile_photo_url as student_photo',
                'tutors.name as tutor_name'
            );

        // Apply filters
        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('students.name', 'like', "%{$search}%")
                  ->orWhere('tutors.name', 'like', "%{$search}%")
                  ->orWhere('reviews.comment', 'like', "%{$search}%");
            });
        }

        $reviews = $query->latest()->paginate(10);

        return view('admin.reviews', compact('reviews'));
    }
}
