<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentReceived extends Notification implements ShouldQueue
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
        $subject = $this->booking->subject->name;
        $studentName = $this->booking->student->name;
        $date = $this->booking->start_time->format('F j, Y');
        $time = $this->booking->start_time->format('g:i A');
        $amount = number_format($this->booking->price, 2);

        return (new MailMessage)
            ->subject('Payment Received')
            ->line("You have received a payment of $${amount} for your tutoring session.")
            ->line("Subject: {$subject}")
            ->line("Student: {$studentName}")
            ->line("Date: {$date}")
            ->line("Time: {$time}")
            ->action('View Booking', route('bookings.show', $this->booking))
            ->line('Thank you for using our platform!');
    }

    public function toArray($notifiable)
    {
        return [
            'booking_id' => $this->booking->id,
            'amount' => $this->booking->price,
            'student_name' => $this->booking->student->name,
            'subject' => $this->booking->subject->name,
            'date' => $this->booking->start_time->format('F j, Y'),
            'time' => $this->booking->start_time->format('g:i A'),
            'action_url' => route('bookings.show', $this->booking),
            'action_text' => 'View Booking',
        ];
    }
}
