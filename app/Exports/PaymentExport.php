<?php

namespace App\Exports;

use App\Models\Payment;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PaymentExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(private readonly ?string $period = null)
    {
    }

    public function query()
    {
        return Payment::with(['student', 'recordedBy'])
            ->when($this->period, fn ($q) => $q->where('period', $this->period))
            ->orderBy('period')
            ->orderBy('student_id');
    }

    public function headings(): array
    {
        return [
            __('field.name'),
            __('field.period'),
            __('field.amount'),
            __('field.status'),
            __('field.due_date'),
            __('field.paid_at'),
            __('field.recorded_by'),
        ];
    }

    public function map($row): array
    {
        return [
            optional($row->student)->name ?? '-',
            $row->period,
            $row->amount,
            $row->status->value,
            $row->due_date->format('Y-m-d'),
            $row->paid_at?->format('Y-m-d H:i') ?? '-',
            optional($row->recordedBy)->name ?? '-',
        ];
    }
}
