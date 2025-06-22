<?php

if (!function_exists('translateSubjectName')) {
    /**
     * Translate subject name based on current locale
     *
     * @param string $subjectName
     * @return string
     */
    function translateSubjectName($subjectName) {
        $translatedName = __('subjects.names.' . $subjectName);

        // If translation not found, return original name
        if ($translatedName === 'subjects.names.' . $subjectName) {
            return $subjectName;
        }

        return $translatedName;
    }
}

if (!function_exists('translateSubjectDescription')) {
    /**
     * Translate subject description based on current locale
     *
     * @param string $subjectName
     * @return string
     */
    function translateSubjectDescription($subjectName) {
        $translatedDescription = __('subjects.descriptions.' . $subjectName);

        // If translation not found, return default fallback
        if ($translatedDescription === 'subjects.descriptions.' . $subjectName) {
            return 'Explore ' . $subjectName . ' courses and find expert tutors.';
        }

        return $translatedDescription;
    }
}

if (!function_exists('getBookingCard')) {
    /**
     * Get BookingCard component instance
     */
    function getBookingCard(\App\Models\Booking $booking, array $options = []): \App\Components\BookingCard
    {
        return new \App\Components\BookingCard($booking, $options);
    }
}

if (!function_exists('getTutorCard')) {
    /**
     * Get TutorCard component instance
     */
    function getTutorCard(\App\Models\Tutor $tutor, array $options = []): \App\Components\TutorCard
    {
        return new \App\Components\TutorCard($tutor, $options);
    }
}

if (!function_exists('formatCurrency')) {
    /**
     * Format currency for display
     */
    function formatCurrency(float $amount, string $currency = 'USD'): string
    {
        return number_format($amount, 2) . ' ' . $currency;
    }
}

if (!function_exists('formatDateForDisplay')) {
    /**
     * Format date using user preferred format (d-m-Y)
     */
    function formatDateForDisplay($date, string $format = 'd-m-Y'): string
    {
        if (is_string($date)) {
            $date = new DateTime($date);
        }

        return $date->format($format);
    }
}

if (!function_exists('formatDateTimeForDisplay')) {
    /**
     * Format datetime using user preferred format
     */
    function formatDateTimeForDisplay($dateTime, string $format = 'd-m-Y H:i'): string
    {
        if (is_string($dateTime)) {
            $dateTime = new DateTime($dateTime);
        }

        return $dateTime->format($format);
    }
}

if (!function_exists('getBookingStatusBadge')) {
    /**
     * Get booking status badge configuration
     */
    function getBookingStatusBadge(string $status): array
    {
        $statusConfig = [
            'pending' => [
                'color' => 'warning',
                'icon' => 'clock',
                'text' => __('booking.status.pending')
            ],
            'accepted' => [
                'color' => 'success',
                'icon' => 'check',
                'text' => __('booking.status.accepted')
            ],
            'rejected' => [
                'color' => 'danger',
                'icon' => 'times',
                'text' => __('booking.status.rejected')
            ],
            'cancelled' => [
                'color' => 'secondary',
                'icon' => 'ban',
                'text' => __('booking.status.cancelled')
            ],
            'completed' => [
                'color' => 'primary',
                'icon' => 'check-circle',
                'text' => __('booking.status.completed')
            ]
        ];

        return $statusConfig[$status] ?? [
            'color' => 'secondary',
            'icon' => 'question',
            'text' => ucfirst($status)
        ];
    }
}

if (!function_exists('calculateBookingDuration')) {
    /**
     * Calculate booking duration
     */
    function calculateBookingDuration($startTime, $endTime): string
    {
        $start = \Carbon\Carbon::parse($startTime);
        $end = \Carbon\Carbon::parse($endTime);

        $duration = $end->diffInMinutes($start);
        $hours = intval($duration / 60);
        $minutes = $duration % 60;

        if ($hours > 0) {
            return $minutes > 0 ? "{$hours}h {$minutes}m" : "{$hours}h";
        }

        return "{$minutes}m";
    }
}

if (!function_exists('getTutorRatingStars')) {
    /**
     * Generate star rating array for tutor
     */
    function getTutorRatingStars(float $rating): array
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
}

if (!function_exists('isUpcomingBooking')) {
    /**
     * Check if booking is upcoming
     */
    function isUpcomingBooking($startTime): bool
    {
        return \Carbon\Carbon::parse($startTime)->isFuture();
    }
}

if (!function_exists('getBookingUrgency')) {
    /**
     * Get booking urgency level
     */
    function getBookingUrgency($startTime): string
    {
        if (!\Carbon\Carbon::parse($startTime)->isFuture()) {
            return 'none';
        }

        $hoursUntilStart = \Carbon\Carbon::parse($startTime)->diffInHours();

        if ($hoursUntilStart <= 2) {
            return 'high';
        } elseif ($hoursUntilStart <= 24) {
            return 'medium';
        }

        return 'low';
    }
}
