<?php

if (!function_exists('translateSubjectName')) {
    /**
     * Translate subject name based on current locale.
     *
     * @param  string  $subjectName
     * @return string
     */
    function translateSubjectName($subjectName)
    {
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
     * Translate subject description based on current locale.
     *
     * @param  string  $subjectName
     * @return string
     */
    function translateSubjectDescription($subjectName)
    {
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
     * Get BookingCard component instance.
     */
    function getBookingCard(\App\Models\Booking $booking, array $options = []): \App\Components\BookingCard
    {
        return new \App\Components\BookingCard($booking, $options);
    }
}

if (!function_exists('getTutorCard')) {
    /**
     * Get TutorCard component instance.
     */
    function getTutorCard(\App\Models\Tutor $tutor, array $options = []): \App\Components\TutorCard
    {
        return new \App\Components\TutorCard($tutor, $options);
    }
}

if (!function_exists('formatCurrency')) {
    /**
     * Format currency for display - always show VND for Vietnamese locale
     */
    function formatCurrency($amount, $currency = 'VND'): string
    {
        // Get current locale
        $locale = session('locale') ?: app()->getLocale();
        if (!$locale || !in_array($locale, ['en', 'vi'])) {
            $locale = config('app.locale', 'vi');
        }

        // Convert amount to float to handle any data type
        $amount = (float) $amount;

        // For Vietnamese locale, ALWAYS show VND regardless of amount
        if ($locale === 'vi') {
            return number_format($amount, 0, ',', '.') . ' VND';
        } else {
            // For English locale, convert VND to USD if amount is large
            if ($amount > 1000) {
                // Convert VND to USD for display
                $usdAmount = $amount / 25000;
                return '$' . number_format($usdAmount, 2);
            } else {
                // Already USD
                return '$' . number_format($amount, 2);
            }
        }
    }
}

if (!function_exists('formatBookingAmount')) {
    /**
     * Format booking amount using the booking's stored currency field.
     */
    function formatBookingAmount(\App\Models\Booking $booking): string
    {
        $currency = $booking->currency ?? 'VND';
        $amount = (float) $booking->price; // Cast to float

        // Protection against negative prices - should not happen but just in case
        if ($amount < 0) {
            \App\Services\LogService::error('Negative price detected in formatBookingAmount', null, [
                'booking_id' => $booking->id,
                'price' => $amount,
                'currency' => $currency,
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

                return number_format($vndAmount, 0, ',', '.') . ' VND';
            } else {
                // English: Display as USD
                return '$' . number_format($amount, 2);
            }
        }

        // Case 1: Currency is VND (real VND amounts)
        if ($currency === 'VND') {
            if ($locale === 'vi') {
                // Vietnamese: Display VND as is
                return number_format($amount, 0, ',', '.') . ' VND';
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

                return number_format($vndAmount, 0, ',', '.') . ' VND';
            } else {
                // English: Display USD as is
                return '$' . number_format($amount, 2);
            }
        }

        // Default fallback
        return number_format($amount, 2) . ' ' . $currency;
    }
}

if (!function_exists('formatHourlyRate')) {
    /**
     * Format hourly rate with proper currency and unit based on locale
     * Smart detection: if amount > 1000, assume it's already VND.
     */
    function formatHourlyRate(float $amount, string $currency = 'USD'): string
    {
        // Protection against negative rates
        if ($amount < 0) {
            \App\Services\LogService::error('Negative hourly rate detected in formatHourlyRate', null, [
                'amount' => $amount,
                'currency' => $currency,
            ]);
            $amount = abs($amount); // Use absolute value for display
        }

        // Get current locale with multiple fallbacks
        $locale = session('locale') ?: app()->getLocale();
        if (!$locale || !in_array($locale, ['en', 'vi'])) {
            $locale = config('app.locale', 'vi');
        }

        // Smart detection: If amount > 1000, it's likely already VND
        // This prevents double conversion (VND -> USD conversion -> VND again)
        if ($amount > 1000) {
            // Amount is already VND
            if ($locale === 'vi') {
                return number_format($amount, 0, ',', '.') . ' VND/giờ';
            } else {
                // Convert VND to USD for English display
                $usdAmount = $amount / 25000;

                return '$' . number_format($usdAmount, 2) . '/hr';
            }
        } else {
            // Amount is in USD
            if ($locale === 'vi') {
                // Convert USD to VND for Vietnamese display
                $vndAmount = $amount * 25000;

                return number_format($vndAmount, 0, ',', '.') . ' VND/giờ';
            } else {
                // Display USD as-is for English
                return '$' . number_format($amount, 2) . '/hr';
            }
        }
    }
}

if (!function_exists('formatDateForDisplay')) {
    /**
     * Format date using user preferred format (d-m-Y).
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
     * Format datetime using user preferred format.
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
     * Get booking status badge configuration.
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

if (!function_exists('calculateBookingDuration')) {
    /**
     * Calculate booking duration.
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

if (!function_exists('getTutorRatingStars')) {
    /**
     * Generate star rating array for tutor.
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
     * Check if booking is upcoming.
     */
    function isUpcomingBooking($startTime): bool
    {
        return \Carbon\Carbon::parse($startTime)->isFuture();
    }
}

if (!function_exists('getBookingUrgency')) {
    /**
     * Get booking urgency level.
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

if (!function_exists('translateReasonCode')) {
    /**
     * Translate reason codes (cancellation_reason, rejection_reason) to human readable text.
     */
    function translateReasonCode($reasonCode, $type = 'common')
    {
        if (empty($reasonCode)) {
            return __('notifications.no_reason_provided');
        }

        // Map of reason codes to translation keys
        $reasonMap = [
            'schedule_conflict' => 'common.schedule_conflict',
            'financial_reason' => 'common.financial_reason',
            'personal_reason' => 'common.personal_reason',
            'found_another_tutor' => 'common.found_another_tutor',
            'not_qualified' => 'common.not_qualified',
            'overbooked' => 'common.overbooked',
            'inappropriate_request' => 'common.inappropriate_request',
            'tutor_unavailable' => 'booking.reason_tutor_unavailable',
            'emergency' => 'booking.reason_emergency',
            'technical_issues' => 'booking.reason_technical_issues',
            'other' => 'common.other',
        ];

        if (isset($reasonMap[$reasonCode])) {
            return __($reasonMap[$reasonCode]);
        }

        // If no mapping found, try to capitalize and return as fallback
        return ucfirst(str_replace('_', ' ', $reasonCode));
    }
}

if (!function_exists('safeOld')) {
    /**
     * Safely get old input value, ensuring it returns a string for form fields.
     * Prevents htmlspecialchars() errors when arrays are passed to string fields.
     */
    function safeOld($key, $default = '')
    {
        $value = old($key, $default);

        // If value is an array, return empty string or default
        if (is_array($value)) {
            return is_string($default) ? $default : '';
        }

        // Ensure we return a string
        return (string) $value;
    }
}

if (!function_exists('safeOldArray')) {
    /**
     * Safely get old input value for array fields.
     * Ensures we always return an array.
     */
    function safeOldArray($key, $default = [])
    {
        $value = old($key, $default);

        // If value is not an array, return default array
        if (!is_array($value)) {
            return is_array($default) ? $default : [];
        }

        return $value;
    }
}
