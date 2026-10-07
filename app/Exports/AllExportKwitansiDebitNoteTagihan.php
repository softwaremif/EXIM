<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AllExportKwitansiDebitNoteTagihan implements FromView, WithStyles, WithHeadings
{
    protected $data;
    protected $gudnm;
    protected $gudpk;
    protected $alamat;

    public function __construct($data, $gudnm, $gudpk, $alamat)
    {
        $this->data = $data;
        $this->gudnm = $gudnm;
        $this->gudpk = $gudpk;
        $this->alamat = $alamat;
    }

    public function view(): View
    {
        return view('menu.finance.export-all', [
            'gudnm' => $this->gudnm,
            'gudpk' => $this->gudpk,
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
