<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CinvoiceExport
{
    protected $dbnote;
    protected $invsdt;
    protected $shipper;
    protected $pobyr;
    protected $judul;
    protected $judulinv;
    protected $shipto;   // ← tambah property
    protected $bill;

    // ── Constructor — semua parameter lengkap ─────────────────────────────────
    public function __construct(
        $dbnote,
        $invsdt,
        $shipper,
        $pobyr,
        $judul    = 'COMMERCIAL INVOICE',
        $judulinv = 'INVOICE NO',
        $shipto   = null,
        $bill = null  // ← tambah parameter
    ) {
        $this->dbnote    = $dbnote;
        $this->bill      = $bill; 
        $this->invsdt    = $invsdt;
        $this->shipper   = $shipper;
        $this->pobyr     = $pobyr;
        $this->judul     = $judul;
        $this->judulinv  = $judulinv;
        $this->shipto    = $shipto;
          // ← assign
    }

    public function download(string $filename = 'commercial_invoice.xlsx'): StreamedResponse
    {
        $spreadsheet = $this->build();

        $response = new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }

    protected function build(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Worksheet');

        // ── Column widths ──────────────────────────────────────────────────────
        $colWidths = [
            'A' => 12,  // PO
            'B' => 10,  // Style
            'C' => 38,  // COLOR
            'D' => 12,  // DESC
            'E' => 12,  // Product
            'F' => 14,  // Destination
            'G' => 14,  // SEND DATE
            'H' => 10,   // Qty
            'I' => 18,  // Unit / label kanan (INVOICE NO, DATE, dll)
            'J' => 28,  // Unit Price / nilai kanan
            'K' => 15,  // Total Amount US$
            'L' => 18,  // AWB SHIPPING #
        ];
        foreach ($colWidths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        $row = 1;

        // ══════════════════════════════════════════════════════════════════════
        // SECTION 1 — COMPANY HEADER
        // ══════════════════════════════════════════════════════════════════════

        $sheet->mergeCells("A{$row}:L{$row}");
        $sheet->setCellValue("A{$row}", 'PT MORICH INDO FASHION');
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font'      => ['bold' => true, 'size' => 16, 'name' => 'Arial'],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension($row)->setRowHeight(22);
        $row++;

        $sheet->mergeCells("A{$row}:L{$row}");
        $sheet->setCellValue("A{$row}", 'JL. RAYA KARANGJATI KM. 25 DESA GEMBONGAN - BERGAS, SEMARANG - INDONESIA');
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font'      => ['size' => 10, 'name' => 'Arial'],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $row++;

        $sheet->mergeCells("A{$row}:L{$row}");
        $sheet->setCellValue("A{$row}", 'PHONE : 0298 - 523523 (6 LINES) FAX. 0298 - 522095');
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font'      => ['size' => 10, 'name' => 'Arial'],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $row++;

        $sheet->mergeCells("A{$row}:L{$row}");
        $sheet->setCellValue("A{$row}", 'www.morichindofashion.com');
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font'      => ['size' => 10, 'name' => 'Arial'],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $row++;

        // Horizontal divider
        $sheet->mergeCells("A{$row}:L{$row}");
        $sheet->getRowDimension($row)->setRowHeight(4);
        $sheet->getStyle("A{$row}:L{$row}")->applyFromArray([
            'borders' => [
                'bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF000000']],
            ],
        ]);
        $row++;

        // Judul dinamis (COMMERCIAL INVOICE / DEBIT NOTE)
        $sheet->mergeCells("A{$row}:L{$row}");
        $sheet->setCellValue("A{$row}", $this->judul);
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font'      => ['bold' => true, 'size' => 12, 'underline' => Font::UNDERLINE_SINGLE, 'name' => 'Arial'],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension($row)->setRowHeight(18);
        $row++;

        // Spacer
        $sheet->getRowDimension($row)->setRowHeight(6);
        $row++;

        // ══════════════════════════════════════════════════════════════════════
        // SECTION 2 — INFO BLOCK (LEFT + RIGHT)
        // ══════════════════════════════════════════════════════════════════════

        $infoStartRow = $row;

        // ── Prepare LEFT block ────────────────────────────────────────────────
        $leftLines = [];

        // BUYER
        $alamatLines = explode("\n", str_replace("\r", '', $this->dbnote->alamatbyr ?? ''));
        $alamatLines = array_values(array_filter(array_map('trim', $alamatLines), fn($l) => $l !== ''));

        $buyerName = trim($this->dbnote->buyer ?? '');
        if ($buyerName === '' && count($alamatLines) > 0) {
            $buyerName = array_shift($alamatLines);
        }

        $leftLines[] = ['label' => 'BUYER', 'value' => $buyerName];
        foreach ($alamatLines as $line) {
            $leftLines[] = ['label' => null, 'value' => $line];
        }

        // CONTACT BUYER
        $leftLines[] = ['label' => null, 'value' => null, 'spacer' => true];
        $leftLines[] = ['label' => 'CONTACT', 'value' => trim($this->dbnote->kontakbyr ?? '')];

        // SHIP TO — sesuai urutan PDF
        $leftLines[] = ['label' => null, 'value' => null, 'spacer' => true];
        $shiptoAlamat = trim($this->shipto->alamat ?? '');
        $shiptoLines  = $shiptoAlamat !== ''
            ? array_values(array_filter(array_map('trim', explode("\n", str_replace("\r", '', $shiptoAlamat))), fn($l) => $l !== ''))
            : [];

        if (!empty($shiptoLines)) {
            foreach ($shiptoLines as $i => $line) {
                $leftLines[] = ['label' => ($i === 0 ? 'SHIP TO' : null), 'value' => $line];
            }
        } else {
            $leftLines[] = ['label' => 'SHIP TO', 'value' => ''];
        }

        // CONTACT SHIP TO
        $leftLines[] = ['label' => null, 'value' => null, 'spacer' => true];
        $leftLines[] = ['label' => 'CONTACT', 'value' => trim($this->shipto->kontakship ?? '')];

        // // BILL TO
        // $leftLines[] = ['label' => null, 'value' => null, 'spacer' => true];
        // $billLines = explode("\n", str_replace("\r", '', $this->dbnote->alamatbill ?? ''));
        // foreach ($billLines as $i => $line) {
        //     $trimmed = trim($line);
        //     if ($trimmed !== '' || $i === 0) {
        //         $leftLines[] = ['label' => ($i === 0 ? 'BILL TO' : null), 'value' => $trimmed];
        //     }
        // }

        $leftLines[] = ['label' => null, 'value' => null, 'spacer' => true];

        // Ambil nama bill dari table bill
        $billName = trim($this->bill->billnm ?? '');

        // Ambil alamat
        $billLines = explode("\n", str_replace("\r", '', $this->dbnote->alamatbill ?? ''));

        // Tambahkan nama di baris pertama
        if ($billName !== '') {
            $leftLines[] = ['label' => 'BILL TO', 'value' => $billName];
        }

        // Tambahkan alamat
        foreach ($billLines as $line) {
            $trimmed = trim($line);
            if ($trimmed !== '') {
                $leftLines[] = ['label' => null, 'value' => $trimmed];
            }
        }

        // SHIPPER
        $leftLines[] = ['label' => null, 'value' => null, 'spacer' => true];
        $shipperLines = explode("\n", str_replace("\r", '', $this->shipper ?? ''));
        foreach ($shipperLines as $i => $line) {
            $leftLines[] = ['label' => ($i === 0 ? 'SHIPPER' : null), 'value' => trim($line)];
        }

        // ── Prepare RIGHT block ───────────────────────────────────────────────
        $rightFields = [
            $this->judulinv => $this->dbnote->dbno ?? '',
            'DATE'          => \Carbon\Carbon::parse($this->dbnote->tgldb)->format('M d, y'),
            'SXS PO NO'     => $this->dbnote->sxspono ?? $this->pobyr,
            
            ''              => '',
            'SEASON'        => $this->dbnote->season ?? '',
            // 'SBU'           => $this->dbnote->sbu ?? '',
            'BRAND'         => $this->dbnote->brand ?? '',
            'HK Invoice'    => $this->dbnote->hkinvoice ?? '',
        ];

        // ── Write LEFT block ──────────────────────────────────────────────────
        $r = $infoStartRow;
        foreach ($leftLines as $entry) {
            if (!empty($entry['spacer'])) {
                $sheet->getRowDimension($r)->setRowHeight(6);
                $r++;
                continue;
            }
            if ($entry['label'] !== null) {
                $sheet->setCellValue("A{$r}", $entry['label']);
                $sheet->getStyle("A{$r}")->getFont()->setBold(true)->setName('Arial')->setSize(10);
            }
            if ($entry['value'] !== null) {
                $sheet->mergeCells("B{$r}:H{$r}");
                $sheet->setCellValue("B{$r}", $entry['value']);
                $sheet->getStyle("B{$r}")->applyFromArray([
                    'font'      => ['name' => 'Arial', 'size' => 10],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
            }
            $sheet->getRowDimension($r)->setRowHeight(15);
            $r++;
        }
        $leftEndRow = $r;

        // ── Write RIGHT block ─────────────────────────────────────────────────
        $r = $infoStartRow;
        foreach ($rightFields as $label => $value) {
            $sheet->setCellValue("I{$r}", $label);
            $sheet->getStyle("I{$r}")->applyFromArray([
                'font'      => ['bold' => true, 'name' => 'Arial', 'size' => 10],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet->mergeCells("J{$r}:L{$r}");
            $sheet->setCellValue("J{$r}", $value);
            $sheet->getStyle("J{$r}")->applyFromArray([
                'font'      => ['name' => 'Arial', 'size' => 10],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet->getRowDimension($r)->setRowHeight(15);
            $r++;
        }
        $rightEndRow = $r;

        $row = max($leftEndRow, $rightEndRow);

        // ══════════════════════════════════════════════════════════════════════
        // SECTION 3 — SECTION TITLE BAR
        // ══════════════════════════════════════════════════════════════════════

        $sheet->getRowDimension($row)->setRowHeight(6);
        $row++;

        $sectionTitle = trim(($this->dbnote->brand ?? '') . ' - SERIES ' . ($this->dbnote->season ?? ''));

        // Merge B:D saja (sesuai permintaan)
        $sheet->mergeCells("B{$row}:D{$row}");
        $sheet->setCellValue("B{$row}", $sectionTitle);

        // Font & alignment
        $sheet->getStyle("B{$row}")->applyFromArray([
            'font'      => ['bold' => true, 'name' => 'Arial', 'size' => 11],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFFFFF']],
        ]);

        // Border allBorders di-apply ke range B:D secara terpisah
        $sheet->getStyle("B{$row}:D{$row}")->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']],
            ],
        ]);

        $sheet->getRowDimension($row)->setRowHeight(16);
        $row++;

        $sheet->getRowDimension($row)->setRowHeight(6);
        $row++;

        // ══════════════════════════════════════════════════════════════════════
        // SECTION 4 — TABLE HEADER
        // ══════════════════════════════════════════════════════════════════════

        $headers = [
            'A' => 'PO',
            'B' => 'Style',
            'C' => 'COLOR',
            'D' => 'DESC',
            'E' => 'Product',
            'F' => 'Destination',
            'G' => 'SEND DATE',
            'H' => 'Qty',
            'I' => 'Unit',
            'J' => "Unit Price\nUS$",
            'K' => "Total Amount\nUS$",
            'L' => 'AWB SHIPPING #',
        ];

        foreach ($headers as $col => $label) {
            $sheet->setCellValue("{$col}{$row}", $label);
        }

        $sheet->getStyle("A{$row}:L{$row}")->applyFromArray([
            'font'      => ['bold' => true, 'name' => 'Arial', 'size' => 10],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFFFFF']],
        ]);
        $sheet->getRowDimension($row)->setRowHeight(28);
        $row++;

        // ══════════════════════════════════════════════════════════════════════
        // SECTION 5 — DATA ROWS
        // ══════════════════════════════════════════════════════════════════════

        foreach ($this->invsdt['row'] as $d) {
            $sheet->setCellValue("A{$row}", $d['po']);
            $sheet->setCellValue("B{$row}", $d['style']);
            $sheet->setCellValue("C{$row}", $d['color']);
            $sheet->setCellValue("D{$row}", $d['desc']);
            $sheet->setCellValue("E{$row}", $d['product']);
            $sheet->setCellValue("F{$row}", $d['destination']);
            $sheet->setCellValue("G{$row}", $d['senddate']);
            $sheet->setCellValue("H{$row}", $d['qty']);
            $sheet->setCellValue("I{$row}", $d['unit']);
            $sheet->setCellValue("J{$row}", $d['price']);
            $sheet->setCellValue("K{$row}", $d['amount']);
            $sheet->setCellValue("L{$row}", $d['shipno']);

            $sheet->getStyle("J{$row}")->getNumberFormat()->setFormatCode('"$"#,##0.00');
            $sheet->getStyle("K{$row}")->getNumberFormat()->setFormatCode('"$"#,##0.00');

            $sheet->getStyle("A{$row}:L{$row}")->applyFromArray([
                'font'      => ['name' => 'Arial', 'size' => 10],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                    'wrapText'   => false,
                ],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ]);

            $sheet->getStyle("J{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("K{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("C{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

            $sheet->getRowDimension($row)->setRowHeight(15);
            $row++;
        }

        // ══════════════════════════════════════════════════════════════════════
        // SECTION 6 — BANK CHARGE (optional)
        // ══════════════════════════════════════════════════════════════════════

        if (!empty($this->dbnote->bankcharge)) {
            $sheet->mergeCells("A{$row}:J{$row}");
            $sheet->setCellValue("A{$row}", 'BANK CHARGE');
            $sheet->getStyle("A{$row}")->applyFromArray([
                'font'      => ['bold' => true, 'name' => 'Arial', 'size' => 10],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet->setCellValue("K{$row}", $this->dbnote->bankcharge);
            $sheet->getStyle("K{$row}")->getNumberFormat()->setFormatCode('"$"#,##0.00');
            $sheet->getStyle("K{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("A{$row}:L{$row}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ]);
            $sheet->getRowDimension($row)->setRowHeight(15);
            $row++;
        }

        // ══════════════════════════════════════════════════════════════════════
        // SECTION 7 — TOTAL ROW
        // ══════════════════════════════════════════════════════════════════════

        $sheet->mergeCells("A{$row}:J{$row}");
        $sheet->setCellValue("A{$row}", 'TOTAL');
        $sheet->setCellValue("K{$row}", $this->invsdt['tamount']);
        $sheet->setCellValue("L{$row}", '');

        $sheet->getStyle("K{$row}")->getNumberFormat()->setFormatCode('"$"#,##0.00');

        $sheet->getStyle("A{$row}:L{$row}")->applyFromArray([
            'font'      => ['bold' => true, 'name' => 'Arial', 'size' => 10],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("K{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getRowDimension($row)->setRowHeight(16);
        $row++;

        // ══════════════════════════════════════════════════════════════════════
        // SECTION 8 — PAYMENT DUE DATE
        // ══════════════════════════════════════════════════════════════════════

        $sheet->getRowDimension($row)->setRowHeight(6);
        $row++;

        $sheet->mergeCells("A{$row}:C{$row}");
        $sheet->setCellValue("A{$row}", 'PAYMENT DUE DATE');
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font'      => ['bold' => true, 'name' => 'Arial', 'size' => 10],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        // Nilai payment due date
        $paydate = $this->dbnote->paydate ?? null;
        $sheet->mergeCells("D{$row}:H{$row}");
        if (!empty($paydate)) {
            $sheet->setCellValue("D{$row}", \Carbon\Carbon::parse($paydate)->format('M d, y'));
        }
        $sheet->getStyle("D{$row}:H{$row}")->applyFromArray([
            'font'      => ['name' => 'Arial', 'size' => 10],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['bottom' => ['borderStyle' => Border::BORDER_THIN]],
        ]);
        $sheet->getRowDimension($row)->setRowHeight(18);
        $row++;

        // ══════════════════════════════════════════════════════════════════════
        // SECTION 9 — BANK INFO TABLE
        // ══════════════════════════════════════════════════════════════════════

        $sheet->getRowDimension($row)->setRowHeight(6);
        $row++;

        $bankData = [
            'Pay to the Order of:'      => '',
            'Beneficiary:'              => 'PT MORICH INDO FASHION',
            'Address of Beneficiary:'   => "JL. RAYA KARANGJATI KM.25, GEMBONGAN - BERGAS,\nKAB SEMARANG - JAWA TENGAH, INDONESIA 50552\nTELP. 62-298-525105",
            'Advising Bank:'            => 'PT. Bank Negara Indonesia (Persero) tbk',
            'Address of advising Bank:' => "Karangayu Branch,\nJl.Jend.Sudirman No. 195, 50149 Ph : (024) 7605502\nSEMARANG - INDONESIA",
            'Account:'                  => '0029042494',
            'Swift No.:'                => 'BNINIDJAKRU',
        ];

        $bankStartRow = $row;
        foreach ($bankData as $label => $value) {
            $isBold      = in_array($label, ['Beneficiary:', 'Swift No.:']);
            $valueLines  = ($value !== '') ? explode("\n", $value) : [''];
            $lineCount   = count($valueLines);
            $entryEndRow = $row + $lineCount - 1;

            // Label — merge vertikal jika value multiline
            if ($lineCount > 1) {
                $sheet->mergeCells("B{$row}:D{$entryEndRow}");
            } else {
                $sheet->mergeCells("B{$row}:D{$row}");
            }
            $sheet->setCellValue("B{$row}", $label);
            $sheet->getStyle("B{$row}")->applyFromArray([
                'font'      => ['bold' => true, 'name' => 'Arial', 'size' => 10],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                    'wrapText'   => false,
                ],
            ]);

            // Value — tiap baris → row tersendiri
            foreach ($valueLines as $i => $line) {
                $r = $row + $i;
                $sheet->mergeCells("E{$r}:H{$r}");
                $sheet->setCellValue("E{$r}", trim($line));
                $sheet->getStyle("E{$r}")->applyFromArray([
                    'font'      => ['bold' => $isBold, 'name' => 'Arial', 'size' => 10],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                        'wrapText'   => false,
                    ],
                ]);
                $sheet->getRowDimension($r)->setRowHeight(16);
            }

            $sheet->getStyle("B{$row}:D{$entryEndRow}")->applyFromArray([
                'borders' => ['outline' => ['borderStyle' => Border::BORDER_THIN]],
            ]);
            $sheet->getStyle("E{$row}:H{$entryEndRow}")->applyFromArray([
                'borders' => ['outline' => ['borderStyle' => Border::BORDER_THIN]],
            ]);

            $row = $entryEndRow + 1;
        }

        $bankEndRow = $row - 1;
        $sheet->getStyle("B{$bankStartRow}:H{$bankEndRow}")->applyFromArray([
            'borders' => ['outline' => ['borderStyle' => Border::BORDER_MEDIUM]],
        ]);

        // ══════════════════════════════════════════════════════════════════════
        // SECTION 10 — PREPARED BY
        // ══════════════════════════════════════════════════════════════════════

        $sheet->getRowDimension($row)->setRowHeight(8);
        $row++;

        $sheet->setCellValue("A{$row}", 'PREPARED BY');
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font'      => ['bold' => true, 'name' => 'Arial', 'size' => 10],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->mergeCells("C{$row}:E{$row}");
        $sheet->setCellValue("C{$row}", $this->dbnote->prepareby ?? '');
        $sheet->getStyle("C{$row}")->applyFromArray([
            'font'      => ['name' => 'Arial', 'size' => 10],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);
        $sheet->getRowDimension($row)->setRowHeight(16);

        // Apply Arial ke seluruh sheet
        $sheet->getStyle("A1:L{$row}")->getFont()->setName('Arial');

        return $spreadsheet;
    }
}