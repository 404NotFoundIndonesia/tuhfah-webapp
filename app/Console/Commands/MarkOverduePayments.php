<?php

namespace App\Console\Commands;

use App\Enum\PaymentStatus;
use App\Enum\Role;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\PaymentOverdueNotification;
use Illuminate\Console\Command;

class MarkOverduePayments extends Command
{
    protected $signature = 'payments:mark-overdue';

    protected $description = 'Mark unpaid payments as overdue when their due date has passed';

    public function handle(): int
    {
        $overduePayments = Payment::where('status', PaymentStatus::UNPAID->value)
            ->where('due_date', '<', now()->startOfDay())
            ->whereNull('notified_at')
            ->with('student')
            ->get();

        $admins = User::where('role', Role::ADMINISTRATOR->value)->get();

        foreach ($overduePayments as $payment) {
            $payment->update([
                'status' => PaymentStatus::OVERDUE->value,
                'notified_at' => now(),
            ]);

            $notification = new PaymentOverdueNotification(
                studentName: $payment->student->name ?? 'Unknown',
                period: $payment->period,
                amount: number_format((float) $payment->amount, 2),
                dueDate: $payment->due_date->format('Y-m-d'),
            );

            // Notify guardian
            $student = $payment->student;
            if ($student && $student->student_guardian_id) {
                $guardian = User::find($student->student_guardian_id);
                $guardian?->notify($notification);
            }

            // Notify all admins
            foreach ($admins as $admin) {
                $admin->notify($notification);
            }
        }

        $this->info("Marked {$overduePayments->count()} payment(s) as overdue.");

        return Command::SUCCESS;
    }
}
