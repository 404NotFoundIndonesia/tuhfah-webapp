<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AttendanceReportExport implements FromArray, WithHeadings
{
    public function __construct(private readonly array $rows)
    {
    }

    public function array(): array
    {
        return collect($this->rows)->map(fn ($row) => [
            $row['student']->name,
            $row['present'],
            $row['absent'],
            $row['sick'],
            $row['permitted'],
            $row['total'],
            $row['percentage'].'%',
        ])->toArray();
    }

    public function headings(): array
    {
        return [
            __('field.name'),
            __('label.total_present'),
            __('label.total_absent'),
            __('label.total_sick'),
            __('label.total_permitted'),
            __('label.total'),
            __('label.attendance_percentage'),
        ];
    }
}
