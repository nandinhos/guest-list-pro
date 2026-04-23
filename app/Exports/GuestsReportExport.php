<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;

class GuestsReportExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    public function __construct(
        private array $data,
        private array $totals,
        private string $eventName,
    ) {}

    public function collection(): \Illuminate\Support\Collection
    {
        $rows = collect();

        $rows->push([
            'Total: ' . $this->totals['grand_total'] . '  |  PISTA: ' . $this->totals['pista_total'] . '  |  BACKSTAGE: ' . $this->totals['backstage_total'] . '  |  Validação: ' . ($this->totals['grand_total'] > 0 ? round(($this->totals['grand_validated'] / $this->totals['grand_total']) * 100, 1) : 0) . '%',
            '',
            '',
            '',
            '',
        ]);
        $rows->push([
            'RESPONSÁVEL',
            'SETOR',
            'TOTAL',
            'CHECK-INS',
            '% POR SETOR',
        ]);

        foreach ($this->data as $row) {
            $rows->push([
                $row['promoter_name'],
                'PISTA',
                $row['pista_total'],
                $row['pista_validated'],
                $row['pista_total'] > 0 ? round(($row['pista_validated'] / $row['pista_total']) * 100, 1) . '%' : '0%',
            ]);
            $rows->push([
                '',
                'BACKSTAGE',
                $row['backstage_total'],
                $row['backstage_validated'],
                $row['backstage_total'] > 0 ? round(($row['backstage_validated'] / $row['backstage_total']) * 100, 1) . '%' : '0%',
            ]);
        }

        $rows->push(['', '', '', '', '']);
        $rows->push([
            'TOTAL GERAL',
            'PISTA',
            $this->totals['pista_total'],
            $this->totals['pista_validated'],
            $this->totals['pista_total'] > 0 ? round(($this->totals['pista_validated'] / $this->totals['pista_total']) * 100, 1) . '%' : '0%',
        ]);
        $rows->push([
            '',
            'BACKSTAGE',
            $this->totals['backstage_total'],
            $this->totals['backstage_validated'],
            $this->totals['backstage_total'] > 0 ? round(($this->totals['backstage_validated'] / $this->totals['backstage_total']) * 100, 1) . '%' : '0%',
        ]);
        $rows->push([
            '',
            'TOTAL',
            $this->totals['grand_total'],
            $this->totals['grand_validated'],
            $this->totals['grand_total'] > 0 ? round(($this->totals['grand_validated'] / $this->totals['grand_total']) * 100, 1) . '%' : '0%',
        ]);

        return $rows;
    }

    public function headings(): array
    {
        return [];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 11],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEF3C7']],
                'borders' => [
                    'top' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => 'f97316']],
                    'bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => 'f97316']],
                    'left' => ['borderStyle' => Border::BORDER_THIN],
                    'right' => ['borderStyle' => Border::BORDER_THIN],
                ],
            ],
            3 => [
                'font' => ['bold' => true, 'size' => 10],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F3F4F6']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            'A' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]],
            'B' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            'C' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            'D' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            'E' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
        ];
    }
}