<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Services\SubjectService;
use App\Services\TutorService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubjectController extends Controller
{
    protected SubjectService $subjectService;

    protected TutorService $tutorService;

    public function __construct(SubjectService $subjectService, TutorService $tutorService)
    {
        $this->subjectService = $subjectService;
        $this->tutorService = $tutorService;
    }

    /**
     * Display list of subjects.
     */
    public function index(Request $request): View
    {
        try {
            $filters = $request->only(['search', 'sort', 'order']);
            $subjects = $this->subjectService->getSubjectsWithTutorCount();

            // Apply search if provided
            if (!empty($filters['search'])) {
                $subjects = $this->subjectService->searchSubjects($filters['search']);
            }

            return view('subjects.index', compact('subjects'));
        } catch (Exception $e) {
            return view('subjects.index', [
                'subjects' => collect(),
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Display tutors for specific subject.
     */
    public function listTutorsForSubject(Subject $subject, Request $request): View
    {
        try {
            $filters = $request->only(['search', 'sort', 'order', 'min_rating', 'max_price']);
            $tutorData = $this->tutorService->getTutorsForSubject($subject, $filters);

            return view('subjects.tutors', $tutorData);
        } catch (Exception $e) {
            return view('subjects.tutors', [
                'subject' => $subject,
                'tutors' => collect(),
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get subjects data for AJAX.
     */
    public function getSubjects(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $search = $request->get('search', '');
            $subjects = $this->subjectService->getAllActiveSubjects();

            // Filter by search if provided
            if (!empty($search)) {
                $subjects = $subjects->filter(function ($subject) use ($search) {
                    return stripos($subject->name, $search) !== false ||
                           stripos($subject->description, $search) !== false;
                });
            }

            return response()->json([
                'success' => true,
                'subjects' => $subjects->values(),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
