<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewLearningProgressNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $studentName,
        private readonly string $subject,
        private readonly string $milestone,
        private readonly string $teacherName,
        private readonly string $date,
    ) {}

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
            'subject' => $this->subject,
            'milestone' => $this->milestone,
            'teacher_name' => $this->teacherName,
            'date' => $this->date,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("New Learning Progress: {$this->studentName}")
            ->line("New progress recorded for {$this->studentName}.")
            ->line("Subject: {$this->subject}, Milestone: {$this->milestone}.")
            ->line("Teacher: {$this->teacherName}, Date: {$this->date}.");
    }
}
