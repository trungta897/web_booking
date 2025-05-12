<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'tutor_id',
        'subject_id',
        'start_time',
        'end_time',
        'status',
        'notes',
        'price',
        'meeting_link',
        'payment_status',
        'payment_intent_id',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'price' => 'decimal:2',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function tutor()
    {
        return $this->belongsTo(Tutor::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function getDurationAttribute()
    {
        return $this->start_time->diffInMinutes($this->end_time);
    }

    public function getTotalPriceAttribute()
    {
        return $this->price * ($this->duration / 60);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }
}
