<?php

namespace App\Exports;


use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DebitNoteExport implements FromView, WithStyles, WithHeadings
{
    protected $data;
    protected $gudnm;
    protected $alamat;

    public function __construct($data, $gudnm, $alamat)
    {
        $this->data = $data;
        $this->gudnm = $gudnm;
        $this->alamat = $alamat;
    }

    public function view(): View
    {
        return view('menu.finance.export-debitnote', [
            'gudnm' => $this->gudnm,
            'alamat' => $this->alamat,
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
