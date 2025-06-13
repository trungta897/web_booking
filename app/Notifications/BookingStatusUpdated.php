<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingStatusUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    protected $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $status = ucfirst($this->booking->status);
        $subject = $this->booking->subject->name;
        $date = $this->booking->start_time->format('F j, Y');
        $time = $this->booking->start_time->format('g:i A');

        return (new MailMessage)
            ->subject("Booking {$status}")
            ->line("Your booking for {$subject} on {$date} at {$time} has been {$this->booking->status}.")
            ->action('View Booking', route('bookings.show', $this->booking))
            ->line('Thank you for using our platform!');
    }

    public function toArray($notifiable)
    {
        return [
            'booking_id' => $this->booking->id,
            'status' => $this->booking->status,
            'subject' => $this->booking->subject->name,
            'date' => $this->booking->start_time->format('F j, Y'),
            'time' => $this->booking->start_time->format('g:i A'),
            'action_url' => route('bookings.show', $this->booking),
            'action_text' => 'View Booking',
        ];
    }
}
