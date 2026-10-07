<?php

namespace App\Exports;


use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TagihanExport implements FromView, WithStyles, WithHeadings
{
    protected $data;
    protected $gudnm;

    public function __construct($data, $gudnm)
    {
        $this->data = $data;
        $this->gudnm = $gudnm;
    }

    public function view(): View
    {
        return view('menu.finance.export-tagihan', [
            'gudnm' => $this->gudnm,
            'dt_kwitansi' => $this->data
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
