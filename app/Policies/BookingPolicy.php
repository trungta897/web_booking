<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BookingPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Booking $booking)
    {
        return $user->id === $booking->student_id || $user->id === $booking->tutor->user_id;
    }

    public function update(User $user, Booking $booking)
    {
        // Only tutors can update booking status
        return $user->id === $booking->tutor->user_id;
    }

    public function delete(User $user, Booking $booking)
    {
        // Only students can cancel their own bookings
        return $user->id === $booking->student_id && $booking->status === 'pending';
    }
}
