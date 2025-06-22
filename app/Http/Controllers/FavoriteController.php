<?php

namespace App\Http\Controllers;

use App\Services\TutorService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    protected TutorService $tutorService;

    public function __construct(TutorService $tutorService)
    {
        $this->tutorService = $tutorService;
    }

    /**
     * Display user's favorite tutors
     */
    public function index(): View
    {
        $favorites = $this->tutorService->getUserFavoriteTutors(Auth::user());

        return view('favorites.index', compact('favorites'));
    }

    /**
     * Add tutor to favorites (AJAX)
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'tutor_id' => 'required|exists:tutors,id',
            ]);

            $result = $this->tutorService->toggleFavorite(
                Auth::user(),
                $validated['tutor_id']
            );

            return response()->json([
                'success' => true,
                'is_favorite' => $result['is_favorite'],
                'message' => $result['message'],
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Remove tutor from favorites
     */
    public function destroy(Request $request): RedirectResponse
    {
        try {
            $validated = $request->validate([
                'tutor_id' => 'required|exists:tutors,id',
            ]);

            $this->tutorService->removeFavorite(Auth::user(), $validated['tutor_id']);

            return back()->with('success', __('Tutor removed from favorites'));

        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
