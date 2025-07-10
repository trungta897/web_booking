<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingStatusChanged extends Notification implements ShouldQueue
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
        $tutorName = $this->booking->tutor->user->name;
        $studentName = $this->booking->student->name;
        $date = $this->booking->start_time->format('F d, Y');
        $time = $this->booking->start_time->format('h:i A') . ' - ' . $this->booking->end_time->format('h:i A');

        if ($notifiable->id === $this->booking->student_id) {
            return (new MailMessage())
                ->subject("Booking {$status}: {$subject} with {$tutorName}")
                ->greeting("Hello {$studentName}!")
                ->line("Your booking for {$subject} with {$tutorName} has been {$this->booking->status}.")
                ->line("Date: {$date}")
                ->line("Time: {$time}")
                ->when($this->booking->status === 'accepted', function ($message) {
                    return $message->line('You can now proceed with the payment to confirm your booking.')
                        ->action('View Booking', route('bookings.show', $this->booking));
                })
                ->when($this->booking->status === 'rejected', function ($message) {
                    return $message->line('We apologize for any inconvenience. You can try booking with another tutor.')
                        ->action('Find Another Tutor', route('tutors.index'));
                });
        } else {
            return (new MailMessage())
                ->subject("New Booking Request: {$subject}")
                ->greeting("Hello {$tutorName}!")
                ->line("You have a new booking request from {$studentName} for {$subject}.")
                ->line("Date: {$date}")
                ->line("Time: {$time}")
                ->action('View Booking', route('bookings.show', $this->booking));
        }
    }

    public function toArray($notifiable)
    {
        $status = ucfirst($this->booking->status);
        $subject = $this->booking->subject->name;
        $tutorName = $this->booking->tutor->user->name;
        $studentName = $this->booking->student->name;
        $date = $this->booking->start_time->format('F d, Y');
        $time = $this->booking->start_time->format('h:i A') . ' - ' . $this->booking->end_time->format('h:i A');

        if ($notifiable->id === $this->booking->student_id) {
            $title = "Booking {$status}: {$subject}";
            $message = "Your booking for {$subject} with {$tutorName} has been {$this->booking->status}.";
        } else {
            $title = "New Booking Request: {$subject}";
            $message = "You have a new booking request from {$studentName} for {$subject}.";
        }

        return [
            'booking_id' => $this->booking->id,
            'status' => $this->booking->status,
            'title' => $title,
            'message' => $message,
            'subject' => $subject,
            'date' => $this->booking->start_time->format('Y-m-d'),
            'time' => $this->booking->start_time->format('H:i'),
            'action_url' => route('bookings.show', $this->booking),
            'action_text' => 'View Booking',
        ];
    }
}
