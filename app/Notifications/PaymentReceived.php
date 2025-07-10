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

    /**
     * The booking instance.
     *
     * @var \App\Models\Booking
     */
    protected $booking;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $subject = $this->booking->subject->name;
        $student = $this->booking->student->name;
        $startTime = $this->booking->start_time->format('F j, Y g:i A');
        $endTime = $this->booking->end_time->format('g:i A');
        $amount = number_format((float) $this->booking->price, 2);

        return (new MailMessage())
            ->subject("Payment Received for {$subject} Session")
            ->greeting("Hello {$notifiable->name}!")
            ->line("You've received payment from {$student} for the upcoming {$subject} session.")
            ->line("Session Date: {$this->booking->start_time->format('F j, Y')}")
            ->line("Session Time: {$startTime} - {$endTime}")
            ->line("Amount: {$amount}")
            ->action('View Booking Details', route('bookings.show', $this->booking))
            ->line('Thank you for teaching with our platform!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $amount = number_format((float) $this->booking->price, 2);

        return [
            'title' => 'Payment Received',
            'message' => 'Payment of $' . $amount . " received from {$this->booking->student->name} for {$this->booking->subject->name} session.",
            'booking_id' => $this->booking->id,
            'student_id' => $this->booking->student_id,
            'subject' => $this->booking->subject->name,
            'date' => $this->booking->start_time->format('Y-m-d'),
            'time' => $this->booking->start_time->format('H:i'),
            'amount' => $this->booking->price,
            'action_url' => route('bookings.show', $this->booking),
            'action_text' => 'View Booking',
        ];
    }
}
