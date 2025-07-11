<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tutor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bio',
        'hourly_rate',
        'is_available',
        'experience_years',
        'specialization',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'hourly_rate' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'subject_tutor', 'tutor_id', 'subject_id')
            ->withPivot(['hourly_rate', 'description']);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function availability()
    {
        return $this->hasMany(Availability::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorite_tutors')
            ->withTimestamps();
    }

    public function education()
    {
        return $this->hasMany(Education::class, 'tutor_id');
    }

    public function payouts()
    {
        return $this->hasMany(TutorPayout::class);
    }

    /**
     * Check if the tutor is available at the given time slot.
     *
     * @param  string  $startTime
     * @param  string  $endTime
     * @return bool
     */
    public function isTimeSlotAvailable($startTime, $endTime)
    {
        \Illuminate\Support\Facades\Log::info('--- Checking Time Slot Availability ---');
        \Illuminate\Support\Facades\Log::info("Input StartTime: $startTime, EndTime: $endTime");

        $startDateTime = Carbon::parse($startTime);
        $endDateTime = Carbon::parse($endTime);
        $dayOfWeek = strtolower($startDateTime->format('l'));
        \Illuminate\Support\Facades\Log::info("Day of week: $dayOfWeek");

        $availabilities = $this->availability()
            ->where('day_of_week', $dayOfWeek)
            ->where('is_available', true)
            ->get();

        if ($availabilities->isEmpty()) {
            \Illuminate\Support\Facades\Log::info('No availability records found for this day.');

            return false;
        }

        $found = false;
        foreach ($availabilities as $a) {
            $startTimeStr = $a->start_time->format('H:i:s');
            $endTimeStr = $a->end_time->format('H:i:s');
            \Illuminate\Support\Facades\Log::info("Checking against availability: {$startTimeStr} - {$endTimeStr}");

            // So sánh chỉ phần giờ phút giây
            $availStart = Carbon::createFromFormat('H:i:s', $startTimeStr)->setDate($startDateTime->year, $startDateTime->month, $startDateTime->day);
            $availEnd = Carbon::createFromFormat('H:i:s', $endTimeStr)->setDate($startDateTime->year, $startDateTime->month, $startDateTime->day);
            if ($startDateTime->greaterThanOrEqualTo($availStart) && $endDateTime->lessThanOrEqualTo($availEnd)) {
                $found = true;

                break;
            }
        }
        \Illuminate\Support\Facades\Log::info('Availability match found: ' . ($found ? 'Yes' : 'No'));
        if (!$found) {
            return false;
        }

        // Check overlapping bookings như cũ
        $overlappingBookingsExist = $this->bookings()
            ->whereIn('status', [Booking::STATUS_ACCEPTED, Booking::STATUS_PENDING])
            ->where(function ($query) use ($startDateTime, $endDateTime) {
                $query->where(function ($q) use ($startDateTime, $endDateTime) {
                    $q->where('start_time', '<', $endDateTime)
                        ->where('end_time', '>', $startDateTime);
                });
            })
            ->exists();
        \Illuminate\Support\Facades\Log::info('Overlapping bookings check found: ' . ($overlappingBookingsExist ? 'Yes' : 'No'));
        if ($overlappingBookingsExist) {
            \Illuminate\Support\Facades\Log::info('Result: Not available (Overlapping booking found).');

            return false;
        }
        \Illuminate\Support\Facades\Log::info('Result: Available.');

        return true;
    }

    /**
     * Check if the tutor is available for booking
     * This checks if the tutor is generally available and has at least one availability slot.
     *
     * @return bool
     */
    public function isAvailableForBooking()
    {
        // Check if tutor is marked as available
        if (!$this->is_available) {
            return false;
        }

        // Check if tutor has any availability slots
        $hasAvailability = $this->availability()
            ->where('is_available', true)
            ->exists();

        if (!$hasAvailability) {
            return false;
        }

        // Check if tutor has any active subjects
        $hasSubjects = $this->subjects()->exists();

        if (!$hasSubjects) {
            return false;
        }

        // Check if tutor's account is active
        if (!$this->user || $this->user->account_status !== 'active') {
            return false;
        }

        return true;
    }

    /**
     * Set the hourly rate attribute.
     *
     * @param  mixed  $value
     * @return void
     */
    public function setHourlyRateAttribute($value)
    {
        $this->attributes['hourly_rate'] = round($value, 2);
    }
}
