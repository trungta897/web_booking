<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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

    /**
     * Check if the tutor is available at the given time slot
     *
     * @param string $startTime
     * @param string $endTime
     * @return bool
     */
    public function isTimeSlotAvailable($startTime, $endTime)
    {
        // Convert strings to Carbon instances
        $startDateTime = Carbon::parse($startTime);
        $endDateTime = Carbon::parse($endTime);

        // Get day of week (lowercase)
        $dayOfWeek = strtolower($startDateTime->format('l'));

        // Check if the tutor has availability set for this day and time
        $availabilityExists = $this->availability()
            ->where('day_of_week', $dayOfWeek)
            ->where('start_time', '<', $startDateTime->format('H:i:s'))
            ->where('end_time', '>', $endDateTime->format('H:i:s'))
            ->where('is_available', true)
            ->exists();

        if (!$availabilityExists) {
            return false;
        }

        // Check if there are overlapping bookings
        $overlappingBookings = $this->bookings()
            ->where('status', '!=', 'cancelled')
            ->where('status', '!=', 'rejected')
            ->where(function ($query) use ($startDateTime, $endDateTime) {
                $query->where(function($q) use ($startDateTime, $endDateTime) {
                    $q->where('start_time', '<', $endDateTime)
                      ->where('end_time', '>', $startDateTime);
                });
            })
            ->exists();

        return !$overlappingBookings;
    }
}
