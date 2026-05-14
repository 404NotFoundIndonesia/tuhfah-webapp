<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StudentAbsentNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $studentName,
        private readonly string $date,
        private readonly string $status,
        private readonly string $recordedBy,
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
            'date' => $this->date,
            'status' => $this->status,
            'recorded_by' => $this->recordedBy,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Attendance Update: {$this->studentName}")
            ->line("Your child {$this->studentName} was marked as {$this->status} on {$this->date}.")
            ->line("Recorded by: {$this->recordedBy}");
    }
}
