<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingCancelled extends Notification
{
    use Queueable;

    public $booking;
    public $cancelledBy;

    /**
     * Create a new notification instance.
     */
    public function __construct(Booking $booking, $cancelledBy)
    {
        $this->booking = $booking;
        $this->cancelledBy = $cancelledBy; // 'student' or 'tutor'
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->cancelledBy === 'student'
            ? 'Student has cancelled the booking'
            : 'Tutor has cancelled the booking';

        return (new MailMessage)
            ->subject($subject)
            ->line('A booking has been cancelled.')
            ->line('Subject: ' . $this->booking->subject->name)
            ->line('Date: ' . $this->booking->start_time->format('d/m/Y H:i'))
            ->line('Reason: ' . ($this->booking->cancellation_reason ?? 'No reason provided'))
            ->action('View Booking', route('bookings.show', $this->booking))
            ->line('Thank you for using our tutoring platform!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
            'cancelled_by' => $this->cancelledBy,
            'title' => $this->cancelledBy === 'student'
                ? __('notifications.booking_cancelled_by_student')
                : __('notifications.booking_cancelled_by_tutor'),
            'message' => __('notifications.booking_cancelled_message', [
                'subject' => $this->booking->subject->name,
                'date' => $this->booking->start_time->format('d/m/Y H:i'),
                'reason' => $this->booking->cancellation_reason ?? __('notifications.no_reason_provided')
            ]),
            'action_url' => route('bookings.show', $this->booking),
        ];
    }
}
