<?php

namespace App\Exports;


use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExportListShipment implements FromView, WithStyles, WithHeadings
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function view(): View
    {
        return view('menu.finance.export-list-shipment', [
            'dt_export' => $this->data
        ]);
    }

    public function styles(Worksheet $sheet)
    {

    }

    public function headings(): array
    {
        return [
            'KONTRAK KERJA',
            'INVOICE NO.',
            'STYLE',
            'QUANTITY (PCS)',
            'UNIT PRICE (USD)',
            'AMOUNT (USD)',
        ];
    }
}
