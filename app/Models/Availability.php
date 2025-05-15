<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Availability extends Model
{
    use HasFactory;

    protected $table = 'availability';

    protected $fillable = [
        'tutor_id',
        'day_of_week',
        'start_time',
        'end_time',
        'is_available',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'is_available' => 'boolean',
    ];

    public function tutor()
    {
        return $this->belongsTo(Tutor::class);
    }

    /**
     * Convert day of week number to string representation
     *
     * @return string
     */
    public function getDayNameAttribute()
    {
        $days = [
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
        ];

        return $days[$this->day_of_week] ?? 'Unknown';
    }

    /**
     * Format the start time for display
     *
     * @return string
     */
    public function getFormattedStartTimeAttribute()
    {
        return Carbon::parse($this->start_time)->format('g:i A');
    }

    /**
     * Format the end time for display
     *
     * @return string
     */
    public function getFormattedEndTimeAttribute()
    {
        return Carbon::parse($this->end_time)->format('g:i A');
    }

    /**
     * Check if a given time slot falls within this availability window
     *
     * @param string|Carbon $startTime
     * @param string|Carbon $endTime
     * @return bool
     */
    public function isTimeSlotAvailable($startTime, $endTime)
    {
        if (!$this->is_available) {
            return false;
        }

        // Convert everything to hours and minutes for comparison
        $startTimeCarbon = $startTime instanceof Carbon ? $startTime : Carbon::parse($startTime);
        $endTimeCarbon = $endTime instanceof Carbon ? $endTime : Carbon::parse($endTime);

        $availStartTime = Carbon::parse($this->start_time);
        $availEndTime = Carbon::parse($this->end_time);

        // Compare just the time components
        $startTimeInRange = $startTimeCarbon->format('H:i:s') >= $availStartTime->format('H:i:s');
        $endTimeInRange = $endTimeCarbon->format('H:i:s') <= $availEndTime->format('H:i:s');

        return $startTimeInRange && $endTimeInRange;
    }
}
