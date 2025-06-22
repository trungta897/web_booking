<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\SubjectRequest;
use App\Models\Booking;
use App\Models\Subject;
use App\Models\User;
use App\Services\AdminService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    protected AdminService $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }

    /**
     * Display the admin dashboard
     */
    public function dashboard(): View
    {
        try {
            $stats = $this->adminService->getDashboardStats();
            $recentBookings = $this->adminService->getRecentBookings();

            return view('admin.dashboard', [
                'totalStudents' => $stats['users']['students'] ?? 0,
                'totalTutors' => $stats['users']['tutors'] ?? 0,
                'totalAdmins' => $stats['users']['admins'] ?? 0,
                'activeBookings' => $stats['bookings']['total'] ?? 0,
                'totalRevenue' => $stats['revenue']['total'] ?? 0,
                'recentBookings' => $recentBookings,
            ]);
        } catch (Exception $e) {
            return view('admin.dashboard', [
                'totalStudents' => 0,
                'totalTutors' => 0,
                'totalAdmins' => 0,
                'activeBookings' => 0,
                'totalRevenue' => 0,
                'recentBookings' => collect(),
                'popularSubjects' => collect(),
                'topTutors' => collect(),
                'recentUsers' => collect(),
                'error' => 'Failed to load dashboard data: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Display list of tutors
     */
    public function tutors(Request $request): View
    {
        $search = $request->get('search');
        $tutors = $this->adminService->getTutors($search);

        return view('admin.tutors', compact('tutors'));
    }

    /**
     * Display tutor details
     */
    public function showTutor(User $user): View
    {
        try {
            $tutorData = $this->adminService->getTutorDetails($user);

            return view('admin.tutors.show', $tutorData);
        } catch (Exception $e) {
            abort(404, $e->getMessage());
        }
    }

    /**
     * Toggle tutor suspension status
     */
    public function suspendTutor(User $user): RedirectResponse
    {
        try {
            $this->adminService->toggleUserStatus($user);
            $status = $user->fresh()->account_status;
            $message = $status === 'suspended'
                ? 'Tutor account suspended successfully'
                : 'Tutor account reinstated successfully';

            return redirect()->route('admin.tutors')->with('success', $message);
        } catch (Exception $e) {
            return redirect()->route('admin.tutors')->with('error', $e->getMessage());
        }
    }

    /**
     * Display list of students
     */
    public function students(Request $request): View
    {
        $search = $request->get('search');
        $students = $this->adminService->getStudents($search);

        return view('admin.students', compact('students'));
    }

    /**
     * Display student details
     */
    public function showStudent(User $user): View
    {
        try {
            $studentData = $this->adminService->getStudentDetails($user);

            return view('admin.students.show', $studentData);
        } catch (Exception $e) {
            abort(404, $e->getMessage());
        }
    }

    /**
     * Toggle student suspension status
     */
    public function suspendStudent(User $user): RedirectResponse
    {
        try {
            $this->adminService->toggleUserStatus($user);
            $status = $user->fresh()->account_status;
            $message = $status === 'suspended'
                ? 'Student account suspended successfully'
                : 'Student account reinstated successfully';

            return redirect()->route('admin.students')->with('success', $message);
        } catch (Exception $e) {
            return redirect()->route('admin.students')->with('error', $e->getMessage());
        }
    }

    /**
     * Display list of bookings
     */
    public function bookings(Request $request): View
    {
        $search = $request->get('search');
        $bookings = $this->adminService->getBookings($search);

        return view('admin.bookings', compact('bookings'));
    }

    /**
     * Display booking details
     */
    public function showBooking(Booking $booking): View
    {
        $booking = $this->adminService->getBookingDetails($booking);

        return view('admin.bookings.show', compact('booking'));
    }

    /**
     * Display list of subjects
     */
    public function subjects(Request $request): View
    {
        $search = $request->get('search');
        $subjects = $this->adminService->getSubjects($search);

        return view('admin.subjects', compact('subjects'));
    }

    /**
     * Show create subject form
     */
    public function createSubject(): View
    {
        return view('admin.subjects.create');
    }

    /**
     * Store new subject
     */
    public function storeSubject(SubjectRequest $request): RedirectResponse
    {
        try {
            $this->adminService->createSubject($request->validated());

            return redirect()->route('admin.subjects')->with('success', 'Subject created successfully');
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Show subject details
     */
    public function showSubject(Subject $subject): View
    {
        $subject->load(['tutors.user', 'bookings.student']);

        return view('admin.subjects.show', compact('subject'));
    }

    /**
     * Show edit subject form
     */
    public function editSubject(Subject $subject): View
    {
        return view('admin.subjects.edit', compact('subject'));
    }

    /**
     * Update subject
     */
    public function updateSubject(SubjectRequest $request, Subject $subject): RedirectResponse
    {
        try {
            $this->adminService->updateSubject($subject, $request->validated());

            return redirect()->route('admin.subjects')->with('success', 'Subject updated successfully');
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Show delete confirmation
     */
    public function confirmDeleteSubject(Subject $subject): View
    {
        $subject->load(['tutors', 'bookings']);

        return view('admin.subjects.confirm-delete', compact('subject'));
    }

    /**
     * Delete subject
     */
    public function destroySubject(Subject $subject): RedirectResponse
    {
        try {
            $this->adminService->deleteSubject($subject);

            return redirect()->route('admin.subjects')->with('success', 'Subject deleted successfully');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Display reports
     */
    public function reports(): View
    {
        $reportsData = $this->adminService->getReportsData();

        return view('admin.reports', $reportsData);
    }

    /**
     * Display reviews
     */
    public function reviews(Request $request): View
    {
        $search = $request->get('search');
        $reviews = $this->adminService->getReviews($search);

        return view('admin.reviews', compact('reviews'));
    }
}
