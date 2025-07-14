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
        return $user->id === $booking->student_id || $user->id === $booking->tutor->user->id;
    }

    public function update(User $user, Booking $booking)
    {
        // Only tutors can update booking status
        return $user->id === $booking->tutor->user->id;
    }

    public function delete(User $user, Booking $booking)
    {
        // Students can cancel their own pending bookings
        if ($user->id === $booking->student_id && $booking->isPending()) {
            return true;
        }

        // Tutors can cancel/reject bookings assigned to them
        if ($user->id === $booking->tutor->user->id && ($booking->isPending() || $booking->status === 'accepted')) {
            return true;
        }

        return false;
    }
}
