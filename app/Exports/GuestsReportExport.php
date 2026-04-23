<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class GuestsReportExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function __construct(
        private array $data,
        private array $totals,
        private string $eventName,
    ) {}

    public function collection(): \Illuminate\Support\Collection
    {
        $rows = collect($this->data)->map(function ($row) {
            return [
                $row['promoter_name'],
                $row['pista_total'],
                $row['backstage_total'],
                $row['total'],
                $row['total'],
                $row['total_validated'],
            ];
        });

        $rows->push([
            'TOTAL GERAL',
            $this->totals['pista_total'],
            $this->totals['backstage_total'],
            $this->totals['grand_total'],
            $this->totals['grand_total'],
            $this->totals['grand_validated'],
        ]);

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Responsável',
            '🎟 PISTA',
            '🎭 BACKSTAGE',
            'TOTAL',
            'Entregues',
            'Validados',
        ];
    }
}
