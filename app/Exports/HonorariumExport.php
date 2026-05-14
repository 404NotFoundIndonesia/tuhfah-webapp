<?php

namespace App\Exports;

use App\Models\Honorarium;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class HonorariumExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(private readonly ?string $period = null) {}

    public function query()
    {
        return Honorarium::with(['teacher', 'recordedBy'])
            ->when($this->period, fn ($q) => $q->where('period', $this->period))
            ->orderBy('period')
            ->orderBy('teacher_id');
    }

    public function headings(): array
    {
        return [
            __('field.teacher_id'),
            __('field.period'),
            __('field.amount'),
            __('field.status'),
            __('field.paid_at'),
            __('field.recorded_by'),
        ];
    }

    public function map($row): array
    {
        return [
            optional($row->teacher)->name ?? '-',
            $row->period,
            $row->amount,
            $row->status->value,
            $row->paid_at?->format('Y-m-d H:i') ?? '-',
            optional($row->recordedBy)->name ?? '-',
        ];
    }
}
