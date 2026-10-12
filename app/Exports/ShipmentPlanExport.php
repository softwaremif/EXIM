<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Carbon\Carbon;

class ShipmentPlanExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithColumnFormatting
{
    protected $rows;

    public function __construct($rows)
    {
        $this->rows = $rows;
    }

    public function collection()
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'No', 'ID Shipment', 'PO No', 'OP', 'Buyer', 'Ord No', 'Style',
            'Season', 'Part', 'Vassal', 'Cont. Type', 'Total Cont.',
            'Qty List', 'Qty', 'PEB No', 'Invoice', 'Est. Date (ETD)',
            'Cont. Date', 'Status', 'Tanggal Dibuat',
        ];
    }

    public function map($row): array
    {
        return [
            $row->no,
            $row->idship,
            $row->POno,
            $row->OP,
            $row->buyer,
            $row->ordno,
            $row->style,
            $row->season,
            $row->part,
            $row->vassal,
            $row->type,
            $row->totctn,
            $row->qty_list,
            $row->qty,
            $row->pebno,
            $row->invs,
            $this->formatDate($row->exdate ?? null),
            $this->formatDate($row->contdate ?? null),
            $row->status_label,
            $this->formatDate($row->datecreate ?? null),
        ];
    }

    private function formatDate($value): string
    {
        if (empty($value)) {
            return '-';
        }

        try {
            return Carbon::parse($value)->format('d-m-Y');
        } catch (\Throwable $e) {
            return (string) $value;
        }
    }

    public function columnFormats(): array
    {
        // Dipaksa text supaya PO No/OP tidak diubah Excel (leading zero
        // hilang atau berubah jadi notasi ilmiah)
        return [
            'C' => NumberFormat::FORMAT_TEXT, // PO No
            'D' => NumberFormat::FORMAT_TEXT, // OP
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:T1')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E9ECEF'],
            ],
        ]);

        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}