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
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $locale = app()->getLocale();

        if ($locale === 'vi') {
            return (new MailMessage)
                ->subject('Hoàn tiền thanh toán - Buổi học đã bị hủy')
                ->greeting('Xin chào ' . $notifiable->name . ',')
                ->line('Thanh toán của bạn đã được hoàn lại cho buổi học bị hủy.')
                ->line('**Môn học:** ' . $this->booking->subject->name)
                ->line('**Thời gian:** ' . $this->booking->start_time->format('d/m/Y H:i'))
                ->line('**Số tiền hoàn:** ' . $this->booking->display_amount)
                ->line('**Lý do:** ' . ($this->refundReason ?? 'Không có lý do cụ thể'))
                ->line('Bạn sẽ nhận được tiền hoàn trong tài khoản trong vòng 3-5 ngày làm việc.')
                ->action('Xem chi tiết booking', route('bookings.show', $this->booking))
                ->line('Cảm ơn bạn đã sử dụng nền tảng gia sư của chúng tôi!')
                ->salutation('Trân trọng,\nĐội ngũ ' . config('app.name'));
        }

        return (new MailMessage)
            ->subject('Payment Refunded - Booking Cancelled')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your payment has been refunded for the cancelled booking.')
            ->line('**Subject:** ' . $this->booking->subject->name)
            ->line('**Date:** ' . $this->booking->start_time->format('d/m/Y H:i'))
            ->line('**Amount:** ' . $this->booking->display_amount)
            ->line('**Reason:** ' . ($this->refundReason ?? 'No reason provided'))
            ->line('You will receive the refund in your account within 3-5 business days.')
            ->action('View Booking', route('bookings.show', $this->booking))
            ->line('Thank you for using our tutoring platform!')
            ->salutation('Best regards,\n' . config('app.name') . ' Team');
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
