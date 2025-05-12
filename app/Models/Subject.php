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
    ];

    public function tutors()
    {
        return $this->belongsToMany(Tutor::class, 'tutor_subjects_new', 'subject_id', 'tutor_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
