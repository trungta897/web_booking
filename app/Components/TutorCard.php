<?php

namespace App\Components;

use App\Models\Tutor;
use Illuminate\Support\Facades\Auth;

class TutorCard
{
    protected Tutor $tutor;
    protected array $options;

    public function __construct(Tutor $tutor, array $options = [])
    {
        $this->tutor = $tutor;
        $this->options = array_merge([
            'show_actions' => true,
            'show_rating' => true,
            'show_subjects' => true,
            'show_price' => true,
            'show_experience' => true,
            'show_location' => false
        ], $options);
    }

    /**
     * Get tutor card data
     */
    public function getData(): array
    {
        return [
            'tutor' => $this->tutor,
            'formatted_data' => $this->getFormattedData(),
            'rating_info' => $this->getRatingInfo(),
            'subjects' => $this->getSubjects(),
            'actions' => $this->getAvailableActions(),
            'options' => $this->options
        ];
    }

    /**
     * Get formatted tutor data
     */
    protected function getFormattedData(): array
    {
        return [
            'name' => $this->tutor->user->name,
            'hourly_rate' => number_format($this->tutor->hourly_rate, 2) . ' USD/hour',
            'experience' => $this->tutor->experience_years . ' years experience',
            'location' => $this->tutor->user->address ?? 'Not specified',
            'bio' => $this->tutor->bio ? \Illuminate\Support\Str::limit($this->tutor->bio, 150) : 'No bio available',
            'avatar' => $this->tutor->user->avatar ?? '/images/default-avatar.png'
        ];
    }

    /**
     * Get rating information
     */
    protected function getRatingInfo(): array
    {
        $averageRating = $this->tutor->reviews_avg_rating ?? 0;
        $reviewsCount = $this->tutor->reviews_count ?? 0;

        return [
            'average_rating' => round($averageRating, 1),
            'reviews_count' => $reviewsCount,
            'stars' => $this->generateStars($averageRating),
            'rating_text' => $averageRating > 0 ?
                number_format($averageRating, 1) . ' (' . $reviewsCount . ' reviews)' :
                'No reviews yet'
        ];
    }

    /**
     * Get tutor subjects
     */
    protected function getSubjects(): array
    {
        return $this->tutor->subjects->map(function ($subject) {
            return [
                'id' => $subject->id,
                'name' => $subject->name,
                'icon' => $subject->icon ?? 'book'
            ];
        })->toArray();
    }

    /**
     * Get available actions
     */
    protected function getAvailableActions(): array
    {
        if (!$this->options['show_actions']) {
            return [];
        }

        $actions = [];
        $user = Auth::user();

        // View profile action (always available)
        $actions[] = [
            'type' => 'view',
            'label' => __('tutors.actions.view_profile'),
            'route' => route('tutors.show', $this->tutor),
            'class' => 'btn-outline-primary',
            'icon' => 'user'
        ];

        if ($user && $user->role === 'student') {
            // Book session action
            $actions[] = [
                'type' => 'book',
                'label' => __('tutors.actions.book_session'),
                'route' => route('bookings.create', $this->tutor),
                'class' => 'btn-primary',
                'icon' => 'calendar-plus'
            ];

            // Favorite/Unfavorite action
            $isFavorite = $user->favoriteTutors()->where('tutor_id', $this->tutor->id)->exists();
            $actions[] = [
                'type' => 'favorite',
                'label' => $isFavorite ? __('tutors.actions.remove_favorite') : __('tutors.actions.add_favorite'),
                'route' => route('tutors.favorite', $this->tutor),
                'class' => $isFavorite ? 'btn-warning' : 'btn-outline-warning',
                'icon' => $isFavorite ? 'heart-fill' : 'heart',
                'method' => 'POST',
                'data_favorite' => $isFavorite ? 'true' : 'false'
            ];

            // Message action
            $actions[] = [
                'type' => 'message',
                'label' => __('tutors.actions.message'),
                'route' => route('messages.create', ['tutor' => $this->tutor->id]),
                'class' => 'btn-info',
                'icon' => 'chat'
            ];
        }

        return $actions;
    }

    /**
     * Generate star rating HTML
     */
    protected function generateStars(float $rating): array
    {
        $stars = [];
        $fullStars = floor($rating);
        $hasHalfStar = ($rating - $fullStars) >= 0.5;

        // Full stars
        for ($i = 0; $i < $fullStars; $i++) {
            $stars[] = ['type' => 'full', 'icon' => 'star-fill'];
        }

        // Half star
        if ($hasHalfStar) {
            $stars[] = ['type' => 'half', 'icon' => 'star-half'];
        }

        // Empty stars
        $emptyStars = 5 - count($stars);
        for ($i = 0; $i < $emptyStars; $i++) {
            $stars[] = ['type' => 'empty', 'icon' => 'star'];
        }

        return $stars;
    }

    /**
     * Check if tutor is available today
     */
    public function isAvailableToday(): bool
    {
        $today = strtolower(now()->format('l')); // Get current day name

        return $this->tutor->availability()
            ->where('day_of_week', $today)
            ->where('is_available', true)
            ->exists();
    }

    /**
     * Get tutor's next available slot
     */
    public function getNextAvailableSlot(): ?string
    {
        $today = strtolower(now()->format('l'));
        $currentTime = now()->format('H:i');

        // Check today first
        $todaySlot = $this->tutor->availability()
            ->where('day_of_week', $today)
            ->where('is_available', true)
            ->where('start_time', '>', $currentTime)
            ->orderBy('start_time')
            ->first();

        if ($todaySlot) {
            return 'Today at ' . $todaySlot->start_time;
        }

        // Check next 7 days
        for ($i = 1; $i <= 7; $i++) {
            $date = now()->addDays($i);
            $dayName = strtolower($date->format('l'));

            $slot = $this->tutor->availability()
                ->where('day_of_week', $dayName)
                ->where('is_available', true)
                ->orderBy('start_time')
                ->first();

            if ($slot) {
                return $date->format('M j') . ' at ' . $slot->start_time;
            }
        }

        return null;
    }

    /**
     * Get tutor specializations
     */
    public function getSpecializations(): array
    {
        return $this->tutor->subjects->pluck('name')->toArray();
    }

    /**
     * Render tutor card HTML
     */
    public function render(): string
    {
        $data = $this->getData();

        // This would typically use a Blade template or view component
        return view('components.tutor-card', $data)->render();
    }

    /**
     * Get tutor badge based on rating and experience
     */
    public function getBadge(): ?array
    {
        $rating = $this->tutor->reviews_avg_rating ?? 0;
        $reviewsCount = $this->tutor->reviews_count ?? 0;
        $experience = $this->tutor->experience_years;

        if ($rating >= 4.8 && $reviewsCount >= 50) {
            return [
                'type' => 'premium',
                'label' => 'Premium Tutor',
                'class' => 'badge-gold',
                'icon' => 'award'
            ];
        } elseif ($rating >= 4.5 && $reviewsCount >= 20) {
            return [
                'type' => 'verified',
                'label' => 'Verified Tutor',
                'class' => 'badge-blue',
                'icon' => 'check-circle'
            ];
        } elseif ($experience >= 5) {
            return [
                'type' => 'experienced',
                'label' => 'Experienced',
                'class' => 'badge-green',
                'icon' => 'clock'
            ];
        }

        return null;
    }
}
