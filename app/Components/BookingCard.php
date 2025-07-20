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
            'date_format' => 'd/m/Y H:i',
        ], $options);
    }

    /**
     * Get booking card data.
     */
    public function getData(): array
    {
        return [
            'id' => $this->booking->id,
            'student_name' => $this->booking->student->name ?? 'N/A',
            'tutor_name' => $this->booking->tutor->user->name ?? 'N/A',
            'subject_name' => $this->booking->subject->name ?? 'N/A',
            'start_time' => $this->booking->start_time->format('d/m/Y H:i'),
            'end_time' => $this->booking->end_time->format('d/m/Y H:i'),
            'status' => $this->booking->status, // Sử dụng accessor
            'status_badge' => $this->getBookingStatusBadge($this->booking->status),
            'duration' => $this->calculateDuration(),
            'price' => number_format((float) $this->booking->price, 0, ',', '.') . ' VND',
            'is_upcoming' => $this->isUpcoming(),
            'urgency' => $this->getUrgencyLevel(),
            'can_be_cancelled' => $this->booking->canBeCancelled(),
            'show_url' => route('bookings.show', $this->booking),
            ...$this->options,
        ];
    }

    /**
     * Get formatted booking data.
     */
    protected function getFormattedData(): array
    {
        return [
            'start_time' => Carbon::parse($this->booking->start_time)->format($this->options['date_format']),
            'end_time' => Carbon::parse($this->booking->end_time)->format($this->options['date_format']),
            'duration' => $this->calculateDuration(),
            'price' => number_format((float) $this->booking->price, 0, ',', '.') . ' VND',
            'status_text' => ucfirst($this->booking->status),
            ...$this->getPaymentStatusProperties(),
        ];
    }

    /**
     * Get booking status badge HTML class and text.
     */
    protected function getBookingStatusBadge(string $status): array
    {
        $badges = [
            'pending' => [
                'class' => 'bg-yellow-100 text-yellow-800',
                'text' => 'Đang chờ'
            ],
            'accepted' => [
                'class' => 'bg-blue-100 text-blue-800',
                'text' => 'Đã chấp nhận'
            ],
            'confirmed' => [
                'class' => 'bg-green-100 text-green-800', 
                'text' => 'Đã xác nhận'
            ],
            'rejected' => [
                'class' => 'bg-red-100 text-red-800',
                'text' => 'Bị từ chối'
            ],
            'cancelled' => [
                'class' => 'bg-gray-100 text-gray-800',
                'text' => 'Đã hủy'
            ],
            'completed' => [
                'class' => 'bg-purple-100 text-purple-800',
                'text' => 'Hoàn thành'
            ],
        ];

        return $badges[$status] ?? [
            'class' => 'bg-gray-100 text-gray-800',
            'text' => ucfirst($status)
        ];
    }

    /**
     * Get status information with styling.
     */
    protected function getStatusInfo(): array
    {
        $statusConfig = [
            'pending' => [
                'color' => 'warning',
                'icon' => 'clock',
                'text' => 'Đang chờ',
            ],
            'accepted' => [
                'color' => 'info',
                'icon' => 'check',
                'text' => 'Đã chấp nhận',
            ],
            'confirmed' => [
                'color' => 'success',
                'icon' => 'check-circle',
                'text' => 'Đã xác nhận',
            ],
            'rejected' => [
                'color' => 'danger',
                'icon' => 'times',
                'text' => 'Bị từ chối',
            ],
            'cancelled' => [
                'color' => 'secondary',
                'icon' => 'ban',
                'text' => 'Đã hủy',
            ],
            'completed' => [
                'color' => 'primary',
                'icon' => 'check-circle',
                'text' => 'Hoàn thành',
            ],
        ];

        return $statusConfig[$this->booking->status] ?? [
            'color' => 'secondary',
            'icon' => 'question',
            'text' => ucfirst($this->booking->status),
        ];
    }

    /**
     * Get available actions based on booking status and user role.
     */
    protected function getAvailableActions(): array
    {
        if (!$this->options['show_actions']) {
            return [];
        }

        $actions = [];
        $user = \Illuminate\Support\Facades\Auth::user();

        if (!$user) {
            return $actions;
        }

        // View action (always available)
        $actions[] = [
            'type' => 'view',
            'label' => 'Xem chi tiết',
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
                        'label' => 'Chấp nhận',
                        'route' => route('bookings.update', $this->booking),
                        'class' => 'btn-success',
                        'icon' => 'check',
                        'method' => 'PATCH',
                    ];
                    $actions[] = [
                        'type' => 'reject',
                        'label' => 'Từ chối',
                        'route' => route('bookings.update', $this->booking),
                        'class' => 'btn-danger',
                        'icon' => 'times',
                        'method' => 'PATCH',
                    ];
                }
                break;

            case 'accepted':
            case 'confirmed':
                if ($this->booking->canBeCancelled()) {
                    $actions[] = [
                        'type' => 'cancel',
                        'label' => 'Hủy bỏ',
                        'route' => route('bookings.destroy', $this->booking),
                        'class' => 'btn-warning',
                        'icon' => 'ban',
                        'method' => 'DELETE',
                        'confirm' => 'Bạn có chắc muốn hủy booking này?',
                    ];
                }
                break;

            case 'completed':
                if ($user->role === 'student' && $this->booking->student_id === $user->id) {
                    if (!$this->booking->review) {
                        $actions[] = [
                            'type' => 'review',
                            'label' => 'Đánh giá',
                            'route' => route('tutors.show', $this->booking->tutor) . '#review',
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
     * Calculate booking duration.
     */
    protected function calculateDuration(): string
    {
        if (!$this->booking->start_time || !$this->booking->end_time) {
            return '0 phút';
        }

        $start = Carbon::parse($this->booking->start_time);
        $end = Carbon::parse($this->booking->end_time);

        $duration = $start->diffInMinutes($end);
        $hours = intval($duration / 60);
        $minutes = $duration % 60;

        if ($hours > 0) {
            return $minutes > 0 ? "{$hours} giờ {$minutes} phút" : "{$hours} giờ";
        }

        return "{$minutes} phút";
    }

    /**
     * Check if booking is upcoming.
     */
    public function isUpcoming(): bool
    {
        return Carbon::parse($this->booking->start_time)->isFuture();
    }

    /**
     * Check if booking is past.
     */
    public function isPast(): bool
    {
        return Carbon::parse($this->booking->end_time)->isPast();
    }

    /**
     * Get time until booking starts.
     */
    public function getTimeUntilStart(): string
    {
        if (!$this->isUpcoming()) {
            return '';
        }

        return Carbon::parse($this->booking->start_time)->diffForHumans();
    }

    /**
     * Render booking card HTML.
     */
    public function render(): string
    {
        $data = $this->getData();

        // This would typically use a Blade template or view component
        // For now, returning the view name that should be created
        return view('components.booking-card', $data)->render();
    }

    /**
     * Get booking urgency level.
     */
    public function getUrgencyLevel(): string
    {
        if (!$this->isUpcoming()) {
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

    /**
     * Get payment status properties.
     */
    protected function getPaymentStatusProperties(): array
    {
        // BOOLEAN LOGIC: Use boolean fields instead of payment_status
        $isConfirmed = $this->booking->is_confirmed ?? false;
        $isCompleted = $this->booking->is_completed ?? false;
        $isCancelled = $this->booking->is_cancelled ?? false;

        if ($isConfirmed) {
            $paymentStatusText = 'Paid';
        } elseif ($isCompleted) {
            $paymentStatusText = 'Completed';
        } elseif ($isCancelled) {
            $paymentStatusText = 'Cancelled';
        } else {
            $paymentStatusText = 'Unpaid';
        }

        return [
            'payment_status_text' => $paymentStatusText,
            'payment_status_class' => $this->getPaymentStatusClass($paymentStatusText),
        ];
    }

    /**
     * Get payment status CSS class.
     */
    protected function getPaymentStatusClass(string $paymentStatus): string
    {
        $statusClasses = [
            'Paid' => 'text-success',
            'Completed' => 'text-primary',
            'Cancelled' => 'text-secondary',
            'Unpaid' => 'text-warning',
        ];

        return $statusClasses[$paymentStatus] ?? 'text-muted';
    }
}
