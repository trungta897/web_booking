<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use Notifiable;

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
        'account_status',
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

    public function tutor()
    {
        return $this->hasOne(Tutor::class);
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

    // Using Laravel's built-in notification system
    // notifications() and unreadNotifications are provided by Notifiable trait

    /**
     * Get unread notifications count - backup method for views.
     */
    public function getUnreadNotificationsCountAttribute()
    {
        return $this->unreadNotifications->count();
    }

    public function unreadMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id')->whereNull('read_at');
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

    // Dashboard attributes
    public function getUpcomingBookingsAttribute()
    {
        $now = Carbon::now();

        if ($this->role === 'tutor') {
            return Booking::with(['student', 'subject'])
                ->where('tutor_id', $this->tutor->id)
                ->where('start_time', '>=', $now)
                ->where('status', 'accepted')
                ->orderBy('start_time')
                ->take(5)
                ->get();
        } else {
            return Booking::with(['tutor.user', 'subject'])
                ->where('student_id', $this->id)
                ->where('start_time', '>=', $now)
                ->whereIn('status', ['accepted', 'pending'])
                ->orderBy('start_time')
                ->take(5)
                ->get();
        }
    }

    public function getUpcomingBookingsCountAttribute()
    {
        $now = Carbon::now();

        if ($this->role === 'tutor') {
            return Booking::where('tutor_id', $this->tutor->id)
                ->where('start_time', '>=', $now)
                ->where('status', 'accepted')
                ->count();
        } else {
            return Booking::where('student_id', $this->id)
                ->where('start_time', '>=', $now)
                ->whereIn('status', ['accepted', 'pending'])
                ->count();
        }
    }

    public function getTotalHoursAttribute()
    {
        $query = $this->role === 'tutor'
            ? Booking::where('tutor_id', $this->tutor->id)->where('status', 'completed')
            : Booking::where('student_id', $this->id)->where('status', 'completed');

        $totalSeconds = $query->get()->sum(function ($booking) {
            $start = Carbon::parse($booking->start_time);
            $end = Carbon::parse($booking->end_time);

            return $end->diffInSeconds($start);
        });

        return round($totalSeconds / 3600, 1); // Convert seconds to hours
    }

    /**
     * Get the profile photo URL attribute.
     *
     * @return string
     */
    public function getProfilePhotoUrlAttribute()
    {
        if ($this->avatar && file_exists(public_path('storage/' . $this->avatar))) {
            return asset('storage/' . $this->avatar);
        }

        // Return default avatar using UI Avatars service
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name ?? 'User') . '&color=7F9CF5&background=EBF4FF';
    }
}
