<?php

namespace App\Console\Commands;

use App\Enum\PaymentStatus;
use App\Models\Payment;
use Illuminate\Console\Command;

class MarkOverduePayments extends Command
{
    protected $signature = 'payments:mark-overdue';

    protected $description = 'Mark unpaid payments as overdue when their due date has passed';

    public function handle(): int
    {
        $count = Payment::where('status', PaymentStatus::UNPAID->value)
            ->where('due_date', '<', now()->startOfDay())
            ->update(['status' => PaymentStatus::OVERDUE->value]);

        $this->info("Marked {$count} payment(s) as overdue.");

        return Command::SUCCESS;
    }
}
