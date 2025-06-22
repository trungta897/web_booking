<?php

namespace App\Http\Controllers;

use App\Services\SubjectService;
use App\Services\TutorService;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class PageController extends Controller
{
    protected TutorService $tutorService;

    protected SubjectService $subjectService;

    public function __construct(TutorService $tutorService, SubjectService $subjectService)
    {
        $this->tutorService = $tutorService;
        $this->subjectService = $subjectService;
    }

    /**
     * Display the landing page
     */
    public function index(): View
    {
        try {
            $pageData = $this->getLandingPageData();

            return view('welcome', $pageData);

        } catch (Exception $e) {
            // Fallback with empty data if service fails
            return view('welcome', [
                'featuredTutors' => collect(),
                'popularSubjects' => collect(),
            ]);
        }
    }

    /**
     * Display how it works page
     */
    public function howItWorks(): View
    {
        return view('pages.how-it-works');
    }

    /**
     * Display pricing page
     */
    public function pricing(): View
    {
        return view('pages.pricing');
    }

    /**
     * Get landing page data with caching
     */
    protected function getLandingPageData(): array
    {
        return Cache::remember('landing_page_data', 3600, function () {
            return [
                'featuredTutors' => $this->tutorService->getFeaturedTutors(6),
                'popularSubjects' => $this->subjectService->getPopularSubjects(8),
            ];
        });
    }
}
