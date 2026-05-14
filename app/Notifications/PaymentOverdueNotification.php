<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentOverdueNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $studentName,
        private readonly string $period,
        private readonly string $amount,
        private readonly string $dueDate,
    ) {
    }

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if ($notifiable->email_notifications) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'student_name' => $this->studentName,
            'period' => $this->period,
            'amount' => $this->amount,
            'due_date' => $this->dueDate,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Payment Overdue: {$this->studentName}")
            ->line("Payment for {$this->studentName} (period: {$this->period}) is overdue.")
            ->line("Amount: {$this->amount}, Due date: {$this->dueDate}.");
    }
}
