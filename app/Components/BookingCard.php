<?php

namespace App\Components;

use App\Models\Booking;
use Carbon\Carbon;

class BookingCard
{
    protected Booking $booking;

    protected array $options;

    public function __construct(Booking $booking, array $options = [])
    {
        $this->booking = $booking;
        $this->options = array_merge([
            'show_actions' => true,
            'show_tutor_info' => true,
            'show_student_info' => false,
            'show_payment_status' => true,
            'date_format' => 'd-m-Y H:i',
        ], $options);
    }

    /**
     * Get booking card data
     */
    public function getData(): array
    {
        return [
            'id' => $this->booking->id,
            'student_name' => $this->booking->student->name,
            'tutor_name' => $this->booking->tutor->user->name,
            'subject_name' => $this->booking->subject->name,
            'start_time' => $this->booking->start_time->format('d-m-Y H:i'),
            'end_time' => $this->booking->end_time->format('d-m-Y H:i'),
            'status' => $this->booking->status,
            'status_badge' => getBookingStatusBadge($this->booking->status),
            'duration' => calculateBookingDuration($this->booking->start_time, $this->booking->end_time),
            'price' => formatCurrency($this->booking->price),
            'is_upcoming' => isUpcomingBooking($this->booking->start_time),
            'urgency' => getBookingUrgency($this->booking->start_time),
            'can_be_cancelled' => $this->booking->canBeCancelled(),
            'show_url' => route('bookings.show', $this->booking),
            ...$this->options,
        ];
    }

    /**
     * Get formatted booking data
     */
    protected function getFormattedData(): array
    {
        return [
            'start_time' => Carbon::parse($this->booking->start_time)->format($this->options['date_format']),
            'end_time' => Carbon::parse($this->booking->end_time)->format($this->options['date_format']),
            'duration' => $this->calculateDuration(),
            'price' => formatCurrency($this->booking->price),
            'status_text' => ucfirst($this->booking->status),
            'payment_status_text' => ucfirst($this->booking->payment_status ?? 'unpaid'),
        ];
    }

    /**
     * Get status information with styling
     */
    protected function getStatusInfo(): array
    {
        $statusConfig = [
            'pending' => [
                'color' => 'warning',
                'icon' => 'clock',
                'text' => __('booking.status.pending'),
            ],
            'accepted' => [
                'color' => 'success',
                'icon' => 'check',
                'text' => __('booking.status.accepted'),
            ],
            'rejected' => [
                'color' => 'danger',
                'icon' => 'times',
                'text' => __('booking.status.rejected'),
            ],
            'cancelled' => [
                'color' => 'secondary',
                'icon' => 'ban',
                'text' => __('booking.status.cancelled'),
            ],
            'completed' => [
                'color' => 'primary',
                'icon' => 'check-circle',
                'text' => __('booking.status.completed'),
            ],
        ];

        return $statusConfig[$this->booking->status] ?? [
            'color' => 'secondary',
            'icon' => 'question',
            'text' => ucfirst($this->booking->status),
        ];
    }

    /**
     * Get available actions based on booking status and user role
     */
    protected function getAvailableActions(): array
    {
        if (! $this->options['show_actions']) {
            return [];
        }

        $actions = [];
        $user = \Illuminate\Support\Facades\Auth::user();

        if (! $user) {
            return $actions;
        }

        // View action (always available)
        $actions[] = [
            'type' => 'view',
            'label' => __('common.view'),
            'route' => route('bookings.show', $this->booking),
            'class' => 'btn-outline-primary',
            'icon' => 'eye',
        ];

        // Actions based on booking status and user role
        switch ($this->booking->status) {
            case 'pending':
                if ($user->role === 'tutor' && $this->booking->tutor_id === $user->tutor?->id) {
                    $actions[] = [
                        'type' => 'accept',
                        'label' => __('booking.actions.accept'),
                        'route' => route('bookings.update', $this->booking),
                        'class' => 'btn-success',
                        'icon' => 'check',
                        'method' => 'PATCH',
                    ];
                    $actions[] = [
                        'type' => 'reject',
                        'label' => __('booking.actions.reject'),
                        'route' => route('bookings.update', $this->booking),
                        'class' => 'btn-danger',
                        'icon' => 'times',
                        'method' => 'PATCH',
                    ];
                }
                break;

            case 'accepted':
                if ($this->booking->payment_status !== 'paid' && !$this->booking->completedTransactions()->exists()) {
                    if ($user->role === 'student' && $this->booking->student_id === $user->id) {
                        $actions[] = [
                            'type' => 'pay',
                            'label' => __('booking.actions.pay'),
                            'route' => route('bookings.payment', $this->booking),
                            'class' => 'btn-primary',
                            'icon' => 'credit-card',
                        ];
                    }
                }

                if ($this->booking->canBeCancelled()) {
                    $actions[] = [
                        'type' => 'cancel',
                        'label' => __('booking.actions.cancel'),
                        'route' => route('bookings.destroy', $this->booking),
                        'class' => 'btn-warning',
                        'icon' => 'ban',
                        'method' => 'DELETE',
                        'confirm' => __('booking.confirm.cancel'),
                    ];
                }
                break;

            case 'completed':
                if ($user->role === 'student' && $this->booking->student_id === $user->id) {
                    if (! $this->booking->review) {
                        $actions[] = [
                            'type' => 'review',
                            'label' => __('booking.actions.review'),
                            'route' => route('tutors.show', $this->booking->tutor).'#review',
                            'class' => 'btn-info',
                            'icon' => 'star',
                        ];
                    }
                }
                break;
        }

        return $actions;
    }

    /**
     * Calculate booking duration
     */
    protected function calculateDuration(): string
    {
        $start = Carbon::parse($this->booking->start_time);
        $end = Carbon::parse($this->booking->end_time);

        $duration = $end->diffInMinutes($start);
        $hours = intval($duration / 60);
        $minutes = $duration % 60;

        if ($hours > 0) {
            return $minutes > 0 ? "{$hours}h {$minutes}m" : "{$hours}h";
        }

        return "{$minutes}m";
    }

    /**
     * Check if booking is upcoming
     */
    public function isUpcoming(): bool
    {
        return Carbon::parse($this->booking->start_time)->isFuture();
    }

    /**
     * Check if booking is past
     */
    public function isPast(): bool
    {
        return Carbon::parse($this->booking->end_time)->isPast();
    }

    /**
     * Get time until booking starts
     */
    public function getTimeUntilStart(): string
    {
        if (! $this->isUpcoming()) {
            return '';
        }

        return Carbon::parse($this->booking->start_time)->diffForHumans();
    }

    /**
     * Render booking card HTML
     */
    public function render(): string
    {
        $data = $this->getData();

        // This would typically use a Blade template or view component
        // For now, returning the view name that should be created
        return view('components.booking-card', $data)->render();
    }

    /**
     * Get booking urgency level
     */
    public function getUrgencyLevel(): string
    {
        if (! $this->isUpcoming()) {
            return 'none';
        }

        $hoursUntilStart = Carbon::parse($this->booking->start_time)->diffInHours();

        if ($hoursUntilStart <= 2) {
            return 'high';
        } elseif ($hoursUntilStart <= 24) {
            return 'medium';
        }

        return 'low';
    }
}
