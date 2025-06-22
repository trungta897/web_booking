<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'icon',
        'category',
        'level',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function tutors()
    {
        return $this->belongsToMany(Tutor::class, 'subject_tutor', 'subject_id', 'tutor_id')
            ->withPivot(['hourly_rate', 'description']);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Scope a query to only include active subjects.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include inactive subjects.
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }
}
