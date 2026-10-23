<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ShipmentPackingListExport implements FromView, WithStyles
{
    protected $exportpk;
    protected $sizes = []; // label size (gabungan semua PO), dipakai juga di WithStyles
    protected $rowCount = 0;

    public function __construct($exportpk)
    {
        $this->exportpk = $exportpk;
    }

    public function view(): \Illuminate\Contracts\View\View
    {
        $rows = DB::connection('mysql_andon')
            ->table('pack')
            ->join('po', 'po.popk', '=', 'pack.popk')
            ->where('pack.exportpk', $this->exportpk)
            ->orderBy('pack.POno')
            ->orderByRaw('CAST(pack.carton AS UNSIGNED)')
            ->orderBy('pack.packpk')
            ->select('pack.*', 'po.style', 'po.material as po_material', ...$this->sizeColumns())
            ->get();

        // kolom size = gabungan label size semua PO (urut sesuai index size di po),
        // karena 1 shipment bisa berisi beberapa PO dengan size range berbeda
        $sizes = [];
        foreach ($rows as $row) {
            foreach ($this->extractUsedSizes($row) as $label) {
                $sizes[$label] = true;
            }
        }
        $sizes = array_keys($sizes);

        $lines = $this->groupConsecutiveCartons($rows->values());

        $totals = [
            'ttl_ctn' => array_sum(array_column($lines, 'ttl_ctn')),
            'ttl_qty' => array_sum(array_column($lines, 'ttl_qty')),
            'gw'      => array_sum(array_column($lines, 'gw')),
            'nw'      => array_sum(array_column($lines, 'nw')),
            'ttl_gw'  => array_sum(array_column($lines, 'ttl_gw')),
            'ttl_nw'  => array_sum(array_column($lines, 'ttl_nw')),
        ];

        $this->sizes = $sizes;
        $this->rowCount = count($lines);

        return view('exports.shipment-packing-list', compact('sizes', 'lines', 'totals'));
    }

    public function styles(Worksheet $sheet)
    {
        $sizeCount = count($this->sizes);
        $lastColIndex = 8 + $sizeCount + 7; // A..H (8 kolom) + size + 7 kolom akhir
        $lastCol = Coordinate::stringFromColumnIndex($lastColIndex);
        $headerRow = 1;
        $firstData = 2;
        $lastData = $headerRow + $this->rowCount;
        $totalRow = $lastData + 2; // 1 baris kosong sebelum Total

        $sheet->getStyle("A1:{$lastCol}{$totalRow}")
            ->getFont()->setName('Arial')->setSize(10);

        // header
        $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
        ]);
        $sheet->getRowDimension($headerRow)->setRowHeight(42);

        // border tabel (header + data + baris kosong), total tanpa border tengah
        $sheet->getStyle("A{$headerRow}:{$lastCol}" . ($lastData + 1))
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")
            ->getBorders()->getOutline()->setBorderStyle(Border::BORDER_MEDIUM);
        $sheet->getStyle("A{$totalRow}:{$lastCol}{$totalRow}")
            ->getBorders()->getTop()->setBorderStyle(Border::BORDER_MEDIUM);
        $sheet->getStyle("A{$totalRow}:{$lastCol}{$totalRow}")->getFont()->setBold(true);

        // data: rata tengah kecuali kolom PO (A)
        if ($this->rowCount > 0) {
            $sheet->getStyle("B{$firstData}:{$lastCol}{$lastData}")
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        $sheet->getStyle("B{$totalRow}:{$lastCol}{$totalRow}")
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // berat 2 desimal: GROSS WT, NET WT, TTL GROSS WT, TTL NET WT
        $gwCol = Coordinate::stringFromColumnIndex($lastColIndex - 4);
        $ttlNwCol = Coordinate::stringFromColumnIndex($lastColIndex - 1);
        $sheet->getStyle("{$gwCol}{$firstData}:{$ttlNwCol}{$totalRow}")
            ->getNumberFormat()->setFormatCode('0.00');

        // lebar kolom
        $widths = [16, 16, 14, 22, 9, 7, 7, 7];
        foreach ($widths as $i => $w) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i + 1))->setWidth($w);
        }
        for ($i = 1; $i <= $sizeCount; $i++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex(8 + $i))->setWidth(6);
        }
        foreach ([9, 9, 11, 11, 9, 9, 15] as $i => $w) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex(8 + $sizeCount + $i + 1))->setWidth($w);
        }

        return [];
    }

    private function sizeColumns(): array
    {
        $cols = [];
        for ($i = 1; $i <= 40; $i++) $cols[] = "po.size{$i} as size{$i}";
        return $cols;
    }

    private function extractUsedSizes($poRow): array
    {
        $used = [];
        for ($i = 1; $i <= 40; $i++) {
            $label = $poRow->{"size{$i}"} ?? null;
            if (!empty($label) && trim($label) !== '' && trim($label) !== '-') {
                $used[$i] = trim($label);
            }
        }
        return $used;
    }

    // qty per label size dari 1 baris pack (qtyp{index} -> label size PO-nya)
    private function extractQtyPerSize($packRow): array
    {
        $qty = [];
        foreach ($this->extractUsedSizes($packRow) as $index => $label) {
            $val = (int) ($packRow->{"qtyp{$index}"} ?? 0);
            if ($val > 0) {
                $qty[$label] = ($qty[$label] ?? 0) + $val;
            }
        }
        return $qty;
    }

    // ukuran carton dalam CM; measpk 2 = inch -> dikonversi ke cm
    private function measurement($packRow): string
    {
        if ($packRow->panjang === null || $packRow->lebar === null || $packRow->tinggi === null) {
            return (string) ($packRow->meas ?? '');
        }

        $factor = (int) $packRow->measpk === 2 ? 2.54 : 1;
        $fmt = fn($v) => rtrim(rtrim(number_format($v * $factor, 1, '.', ''), '0'), '.');

        return $fmt($packRow->panjang) . ' x ' . $fmt($packRow->lebar) . ' x ' . $fmt($packRow->tinggi);
    }

    // carton berurutan dengan PO, style, color, isi, berat & ukuran yang sama
    // digabung jadi 1 baris (CTN NO. dari - sampai)
    private function groupConsecutiveCartons($rows): array
    {
        $lines = [];
        $count = $rows->count();
        $i = 0;

        $signature = fn($r) => json_encode([
            $r->POno, $r->style, $r->po_material, (int) $r->pcsp,
            $this->extractQtyPerSize($r), (float) $r->gw, (float) $r->nw, $this->measurement($r),
        ]);

        while ($i < $count) {
            $current = $rows[$i];
            $sig = $signature($current);

            $j = $i;
            while (
                $j + 1 < $count
                && (int) $rows[$j + 1]->carton === (int) $rows[$j]->carton + 1
                && $signature($rows[$j + 1]) === $sig
            ) {
                $j++;
            }

            $ttlCtn = $j - $i + 1;
            $gw = (float) ($current->gw ?? 0);
            $nw = (float) ($current->nw ?? 0);

            $lines[] = [
                'POno'         => $current->POno,
                'customer_po'  => '',
                'style'        => $current->style,
                'color'        => $current->po_material ?: $current->material,
                'unit'         => '',
                'ctn_from'     => $current->carton,
                'ctn_to'       => $rows[$j]->carton,
                'ttl_ctn'      => $ttlCtn,
                'qty_per_size' => $this->extractQtyPerSize($current),
                'qty_per_ctn'  => (int) $current->pcsp,
                'ttl_qty'      => (int) $current->pcsp * $ttlCtn,
                'gw'           => $gw,
                'nw'           => $nw,
                'ttl_gw'       => $gw * $ttlCtn,
                'ttl_nw'       => $nw * $ttlCtn,
                'meas'         => $this->measurement($current),
            ];

            $i = $j + 1;
        }

        return $lines;
    }
}
