<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentRefunded extends Notification
{
    use Queueable;

    public $booking;
    public $refundReason;

    /**
     * Create a new notification instance.
     */
    public function __construct(Booking $booking, string $refundReason = null)
    {
        $this->booking = $booking;
        $this->refundReason = $refundReason;
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
        return (new MailMessage)
            ->subject('Payment Refunded - Booking Cancelled')
            ->line('Your payment has been refunded for the cancelled booking.')
            ->line('Subject: ' . $this->booking->subject->name)
            ->line('Date: ' . $this->booking->start_time->format('d/m/Y H:i'))
            ->line('Amount: ' . $this->booking->display_amount)
            ->line('Reason: ' . ($this->refundReason ?? 'No reason provided'))
            ->line('You will receive the refund in your account within 3-5 business days.')
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
            'refund_amount' => $this->booking->price,
            'title' => __('notifications.payment_refunded'),
            'message' => __('notifications.payment_refunded_message', [
                'subject' => $this->booking->subject->name,
                'date' => $this->booking->start_time->format('d/m/Y H:i'),
                'amount' => $this->booking->display_amount,
                'reason' => $this->refundReason ?? __('notifications.no_reason_provided'),
            ]),
            'action_url' => route('bookings.show', $this->booking),
        ];
    }
}
