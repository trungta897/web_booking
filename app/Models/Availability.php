<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Availability extends Model
{
    use HasFactory;

    protected $fillable = [
        'tutor_id',
        'day_of_week',
        'start_time',
        'end_time',
        'is_available',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_available' => 'boolean',
    ];

    public function tutor()
    {
        return $this->belongsTo(Tutor::class);
    }

    public function isTimeSlotAvailable($startTime, $endTime)
    {
        return $this->is_available &&
            $startTime->between($this->start_time, $this->end_time) &&
            $endTime->between($this->start_time, $this->end_time);
    }
}
