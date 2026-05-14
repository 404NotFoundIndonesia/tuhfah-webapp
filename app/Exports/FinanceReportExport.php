<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class FinanceReportExport implements FromArray, WithHeadings, WithTitle
{
    public function __construct(
        private readonly array $summary,
        private readonly string $period,
    ) {
    }

    public function array(): array
    {
        return [
            [__('label.total_collected'), number_format($this->summary['total_collected'], 0, '.', ',')],
            [__('label.total_outstanding'), number_format($this->summary['total_outstanding'], 0, '.', ',')],
            [__('label.total_overdue'), number_format($this->summary['total_overdue'], 0, '.', ',')],
            [__('label.total_honorariums_paid'), number_format($this->summary['total_honorariums_paid'], 0, '.', ',')],
            [__('label.net_income'), number_format($this->summary['net_income'], 0, '.', ',')],
        ];
    }

    public function headings(): array
    {
        return [__('field.status'), __('field.amount')];
    }

    public function title(): string
    {
        return $this->period;
    }
}
