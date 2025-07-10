<?php

if (! function_exists('translateSubjectName')) {
    /**
     * Translate subject name based on current locale
     *
     * @param  string  $subjectName
     * @return string
     */
    function translateSubjectName($subjectName)
    {
        $translatedName = __('subjects.names.'.$subjectName);

        // If translation not found, return original name
        if ($translatedName === 'subjects.names.'.$subjectName) {
            return $subjectName;
        }

        return $translatedName;
    }
}

if (! function_exists('translateSubjectDescription')) {
    /**
     * Translate subject description based on current locale
     *
     * @param  string  $subjectName
     * @return string
     */
    function translateSubjectDescription($subjectName)
    {
        $translatedDescription = __('subjects.descriptions.'.$subjectName);

        // If translation not found, return default fallback
        if ($translatedDescription === 'subjects.descriptions.'.$subjectName) {
            return 'Explore '.$subjectName.' courses and find expert tutors.';
        }

        return $translatedDescription;
    }
}

if (! function_exists('getBookingCard')) {
    /**
     * Get BookingCard component instance
     */
    function getBookingCard(\App\Models\Booking $booking, array $options = []): \App\Components\BookingCard
    {
        return new \App\Components\BookingCard($booking, $options);
    }
}

if (! function_exists('getTutorCard')) {
    /**
     * Get TutorCard component instance
     */
    function getTutorCard(\App\Models\Tutor $tutor, array $options = []): \App\Components\TutorCard
    {
        return new \App\Components\TutorCard($tutor, $options);
    }
}

if (! function_exists('formatCurrency')) {
    /**
     * Format currency for display with automatic conversion based on locale
     */
    function formatCurrency(float $amount, string $currency = 'USD'): string
    {
        // Get current locale with multiple fallbacks
        $locale = session('locale') ?: app()->getLocale();
        if (!$locale || !in_array($locale, ['en', 'vi'])) {
            $locale = config('app.locale', 'vi');
        }

        // If Vietnamese locale and currency is USD, convert to VND
        if ($locale === 'vi' && $currency === 'USD') {
            $vndAmount = $amount * 25000; // 1 USD = 25,000 VND
            return number_format($vndAmount, 0, ',', '.') . ' ₫';
        }

        // For other cases, format normally
        if ($currency === 'VND') {
            return number_format($amount, 0, ',', '.') . ' ₫';
        }

        return '$' . number_format($amount, 2);
    }
}

if (! function_exists('formatBookingAmount')) {
    /**
     * Format booking amount using the booking's stored currency field
     */
    function formatBookingAmount(\App\Models\Booking $booking): string
    {
        $currency = $booking->currency ?? 'VND';
        $amount = $booking->price;

        // Protection against negative prices - should not happen but just in case
        if ($amount < 0) {
            \App\Services\LogService::error('Negative price detected in formatBookingAmount', null, [
                'booking_id' => $booking->id,
                'price' => $amount,
                'currency' => $currency
            ]);
            $amount = abs($amount); // Use absolute value for display
        }

        // Get current locale
        $locale = session('locale') ?: app()->getLocale();
        if (!$locale || !in_array($locale, ['en', 'vi'])) {
            $locale = config('app.locale', 'vi');
        }

        // Smart detection: If currency is VND but amount is small (< 1000),
        // it's likely USD amount saved with wrong currency
        if ($currency === 'VND' && $amount < 1000) {
            // This is likely USD amount with wrong currency label
            if ($locale === 'vi') {
                // Vietnamese: Convert USD to VND for display
                $vndAmount = $amount * 25000; // 1 USD = 25,000 VND
                return number_format($vndAmount, 0, ',', '.') . ' ₫';
            } else {
                // English: Display as USD
                return '$' . number_format($amount, 2);
            }
        }

        // Case 1: Currency is VND (real VND amounts)
        if ($currency === 'VND') {
            if ($locale === 'vi') {
                // Vietnamese: Display VND as is
                return number_format($amount, 0, ',', '.') . ' ₫';
            } else {
                // English: Convert VND to USD for display
                $usdAmount = $amount / 25000; // 1 USD = 25,000 VND
                return '$' . number_format($usdAmount, 2);
            }
        }

        // Case 2: Currency is USD (legacy)
        if ($currency === 'USD') {
            if ($locale === 'vi') {
                // Vietnamese: Convert USD to VND for display
                $vndAmount = $amount * 25000; // 1 USD = 25,000 VND
                return number_format($vndAmount, 0, ',', '.') . ' ₫';
            } else {
                // English: Display USD as is
                return '$' . number_format($amount, 2);
            }
        }

        // Default fallback
        return number_format($amount, 2) . ' ' . $currency;
    }
}

if (! function_exists('formatHourlyRate')) {
    /**
     * Format hourly rate with proper currency and unit based on locale
     */
        function formatHourlyRate(float $amount, string $currency = 'USD'): string
    {
        // Protection against negative rates
        if ($amount < 0) {
            \App\Services\LogService::error('Negative hourly rate detected in formatHourlyRate', null, [
                'amount' => $amount,
                'currency' => $currency
            ]);
            $amount = abs($amount); // Use absolute value for display
        }

        // Get current locale with multiple fallbacks
        $locale = session('locale') ?: app()->getLocale();
        if (!$locale || !in_array($locale, ['en', 'vi'])) {
            $locale = config('app.locale', 'vi');
        }

        if ($locale === 'vi') {
            // Vietnamese: convert USD to VND and show as "₫/giờ"
            if ($currency === 'USD') {
                $vndAmount = $amount * 25000;
                return number_format($vndAmount, 0, ',', '.') . ' ₫/giờ';
            }
            return number_format($amount, 0, ',', '.') . ' ₫/giờ';
        } else {
            // English: show as "$/hr"
            return '$' . number_format($amount, 2) . '/hr';
        }
    }
}

if (! function_exists('formatDateForDisplay')) {
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

if (! function_exists('formatDateTimeForDisplay')) {
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

if (! function_exists('getBookingStatusBadge')) {
    /**
     * Get booking status badge configuration
     */
    function getBookingStatusBadge(string $status): array
    {
        $statusConfig = [
            'pending' => [
                'color' => 'warning',
                'icon' => 'clock',
                'text' => __('booking.status.pending'),
            ],
            'accepted' => [
                'color' => 'success',
                'icon' => 'check',
                'text' => __('booking.status.accepted'),
            ],
            'rejected' => [
                'color' => 'danger',
                'icon' => 'times',
                'text' => __('booking.status.rejected'),
            ],
            'cancelled' => [
                'color' => 'secondary',
                'icon' => 'ban',
                'text' => __('booking.status.cancelled'),
            ],
            'completed' => [
                'color' => 'primary',
                'icon' => 'check-circle',
                'text' => __('booking.status.completed'),
            ],
        ];

        return $statusConfig[$status] ?? [
            'color' => 'secondary',
            'icon' => 'question',
            'text' => ucfirst($status),
        ];
    }
}

if (! function_exists('calculateBookingDuration')) {
    /**
     * Calculate booking duration
     */
    function calculateBookingDuration($startTime, $endTime): string
    {
        $start = \Carbon\Carbon::parse($startTime);
        $end = \Carbon\Carbon::parse($endTime);

        // Use correct order to avoid negative duration
        $duration = $start->diffInMinutes($end);
        $hours = intval($duration / 60);
        $minutes = $duration % 60;

        if ($hours > 0) {
            return $minutes > 0 ? "{$hours}h {$minutes}m" : "{$hours}h";
        }

        return "{$minutes}m";
    }
}

if (! function_exists('getTutorRatingStars')) {
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

if (! function_exists('isUpcomingBooking')) {
    /**
     * Check if booking is upcoming
     */
    function isUpcomingBooking($startTime): bool
    {
        return \Carbon\Carbon::parse($startTime)->isFuture();
    }
}

if (! function_exists('getBookingUrgency')) {
    /**
     * Get booking urgency level
     */
    function getBookingUrgency($startTime): string
    {
        if (! \Carbon\Carbon::parse($startTime)->isFuture()) {
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
