<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ShipExport implements FromCollection, WithHeadings, WithMapping, WithEvents, WithCustomStartCell
{
    protected $ships;

    // kolom terakhir tabel (A..L = 12 kolom)
    const LAST_COL = 'L';
    // baris tempat header tabel (PO, Supplier, dst) mulai digambar
    const HEADER_ROW = 9;

    public function __construct($ships)
    {
        $this->ships = $ships;
    }

    public function collection()
    {
        return $this->ships;
    }

    public function headings(): array
    {
        return [
            'PO', 'Supplier', 'Buyer', 'Type', 'Vassal name', 'AirwayBill',
            'Qty', 'Unit', 'Supplier ETD', 'Delivery', 'Arrival', 'In house',
        ];
    }

    public function startCell(): string
    {
        return 'A' . self::HEADER_ROW;
    }

    public function map($ship): array
    {
        $fmt = function ($date) {
            return (!empty($date) && !str_starts_with($date, '0000-00-00'))
                ? Carbon::parse($date)->format('d/m/Y')
                : '-';
        };

        return [
            $ship->pono_display ?: '-',
            $ship->supnm ?: '-',
            $ship->byrnm ?: '-',
            $ship->typenm ?: '-',
            $ship->vassalnm ?: '-',
            $ship->AWB ?: '-',
            $ship->qtys ?? '-',
            $ship->unit ?: '-',
            $fmt($ship->etd ?? null),
            "E: " . $fmt($ship->ETDV) . "\r\nA: " . $fmt($ship->ATD),
            "E: " . $fmt($ship->ETDA) . "\r\nA: " . $fmt($ship->ATA),
            "E: " . $fmt($ship->EIH) . "\r\nA: " . $fmt($ship->iha_actual),
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // ==== KOP SURAT (baris 1-7) ====
                $sheet->mergeCells('A1:' . self::LAST_COL . '1');
                $sheet->setCellValue('A1', 'PT. MORICH INDO FASION');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->mergeCells('A2:' . self::LAST_COL . '2');
                $sheet->setCellValue('A2', 'Jl.Raya Karangjati KM 25, Desa Gembongan, Bergas');
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->mergeCells('A3:' . self::LAST_COL . '3');
                $sheet->setCellValue('A3', 'KAB. SEMARANG');
                $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // baris 4 = spacer, sengaja dikosongkan

                $sheet->mergeCells('A5:' . self::LAST_COL . '5');
                $sheet->setCellValue('A5', 'EXIM');
                $sheet->getStyle('A5')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // baris 6 = spacer, sengaja dikosongkan

                $sheet->mergeCells('A7:' . self::LAST_COL . '7');
                $sheet->setCellValue('A7', 'Data Shipment planning');
                $sheet->getStyle('A7')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('A7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // baris 8 = spacer sebelum tabel

                // ==== STYLING HEADER TABEL (baris 9) ====
                $headerRow = self::HEADER_ROW;
                $sheet->getStyle('A' . $headerRow . ':' . self::LAST_COL . $headerRow)
                    ->getFont()->setBold(true);
                $sheet->getStyle('A' . $headerRow . ':' . self::LAST_COL . $headerRow)
                    ->getFill()->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('D9D9D9');

                // ==== WRAP TEXT KOLOM DELIVERY, ARRIVAL, IN HOUSE ====
                $lastRow = $sheet->getHighestRow();
                foreach (['J', 'K', 'L'] as $col) {
                    $sheet->getStyle($col . ($headerRow + 1) . ':' . $col . $lastRow)
                        ->getAlignment()
                        ->setWrapText(true)
                        ->setVertical(Alignment::VERTICAL_CENTER);
                }

                for ($row = $headerRow + 1; $row <= $lastRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(-1);
                }

                $sheet->getColumnDimension('J')->setWidth(18);
                $sheet->getColumnDimension('K')->setWidth(18);
                $sheet->getColumnDimension('L')->setWidth(18);
            },
        ];
    }
}