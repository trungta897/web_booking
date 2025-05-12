<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'avatar',
        'phone_number',
        'address',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Relationships
    public function tutorProfile()
    {
        return $this->hasOne(TutorProfile::class);
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'tutor_subjects');
    }

    public function studentBookings()
    {
        return $this->hasMany(Booking::class, 'student_id');
    }

    public function tutorBookings()
    {
        return $this->hasMany(Booking::class, 'tutor_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public function reviewsGiven()
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    public function reviewsReceived()
    {
        return $this->hasMany(Review::class, 'reviewed_user_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function favoriteTutors()
    {
        return $this->belongsToMany(Tutor::class, 'favorite_tutors')
            ->withTimestamps();
    }

    // Helper methods
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isTutor()
    {
        return $this->role === 'tutor';
    }

    public function isStudent()
    {
        return $this->role === 'student';
    }
}
