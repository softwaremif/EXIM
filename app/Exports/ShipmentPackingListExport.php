<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ShipmentPackingListExport implements FromView, WithStyles
{
    protected $exportpk;
    protected $sizes = []; // label size (gabungan semua PO), dipakai juga di WithStyles
    protected $rowCount = 0;
    protected $bundleRanges = []; // [index line awal, index line akhir] tiap carton bundle

    public function __construct($exportpk)
    {
        $this->exportpk = $exportpk;
    }

    public function view(): \Illuminate\Contracts\View\View
    {
        $rows = DB::connection('mysql_andon')
            ->table('pack')
            ->join('po', 'po.popk', '=', 'pack.popk')
            ->leftJoin('carton_bundle as cb', 'cb.bundlepk', '=', 'pack.bundlepk')
            ->where('pack.exportpk', $this->exportpk)
            ->orderBy('pack.POno')
            ->orderByRaw('CAST(pack.carton AS UNSIGNED)')
            ->orderBy('pack.packpk')
            ->select(
                'pack.*', 'po.style', 'po.material as po_material',
                'cb.panjang as cb_panjang', 'cb.lebar as cb_lebar', 'cb.tinggi as cb_tinggi', 'cb.measpk as cb_measpk',
                ...$this->sizeColumns()
            )
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

        $lines = $this->buildLines($rows->values());

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
        $this->bundleRanges = [];
        foreach ($lines as $idx => $line) {
            if (!empty($line['bundle']['first'])) {
                $this->bundleRanges[] = [$idx, $idx + $line['bundle']['span'] - 1];
            }
        }

        return view('exports.shipment-packing-list', compact('sizes', 'lines', 'totals'));
    }

    public function styles(Worksheet $sheet)
    {
        $sizeCount = count($this->sizes);
        $lastColIndex = 8 + $sizeCount + 9; // A..H (8 kolom) + size + 9 kolom akhir
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
        $widths = [16, 16, 14, 22, 9, 7, 7, 10];
        foreach ($widths as $i => $w) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i + 1))->setWidth($w);
        }
        for ($i = 1; $i <= $sizeCount; $i++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex(8 + $i))->setWidth(6);
        }
        foreach ([9, 9, 9, 12, 11, 11, 11, 11, 15] as $i => $w) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex(8 + $sizeCount + $i + 1))->setWidth($w);
        }

        // carton bundle: baris inner box diblok kuning, TTL CTN & Master Box di-merge (dihitung 1 carton)
        $masterCol = Coordinate::stringFromColumnIndex(8 + $sizeCount + 1);
        foreach ($this->bundleRanges as [$start, $end]) {
            $rowStart = $firstData + $start;
            $rowEnd = $firstData + $end;

            $sheet->getStyle("A{$rowStart}:{$lastCol}{$rowEnd}")->getFill()
                ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');

            foreach (['H', $masterCol] as $col) {
                if ($rowEnd > $rowStart) {
                    $sheet->mergeCells("{$col}{$rowStart}:{$col}{$rowEnd}");
                }
                $sheet->getStyle("{$col}{$rowStart}:{$col}{$rowEnd}")->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER);
            }
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
                'master_box'   => '',
                'remark'       => '',
                'bundle'       => null,
            ];

            $i = $j + 1;
        }

        return $lines;
    }

    // key carton fisik yang berisi beberapa baris pack:
    // carton bundle (bundlepk sama) atau carton mix (mixno + no carton sama)
    private function physicalCartonKey($r): ?string
    {
        if (!empty($r->bundlepk)) {
            return 'B|' . $r->bundlepk;
        }
        if (!empty($r->mixno)) {
            return 'M|' . $r->mixno . '|' . $r->carton;
        }
        return null;
    }

    // susun baris packing list; baris dalam 1 carton bundle / mix dikumpulkan
    // di posisi baris pertamanya, carton biasa tetap digabung berurutan
    private function buildLines($rows): array
    {
        $groups = $rows->filter(fn($r) => $this->physicalCartonKey($r) !== null)
            ->groupBy(fn($r) => $this->physicalCartonKey($r));

        $lines = [];
        $buffer = [];
        $emitted = [];

        foreach ($rows as $row) {
            $key = $this->physicalCartonKey($row);
            if ($key === null) {
                $buffer[] = $row;
                continue;
            }
            if (isset($emitted[$key])) {
                continue;
            }

            array_push($lines, ...$this->groupConsecutiveCartons(collect($buffer)));
            $buffer = [];

            $members = $groups[$key]->values();
            array_push($lines, ...(str_starts_with($key, 'B|') ? $this->bundleLines($members) : $this->mixLines($members)));
            $emitted[$key] = true;
        }

        array_push($lines, ...$this->groupConsecutiveCartons(collect($buffer)));

        return $lines;
    }

    // 1 baris pack = 1 baris excel (tanpa digabung), ttl_ctn diisi pemanggil
    private function singleLine($r, int $ttlCtn, string $meas): array
    {
        $gw = (float) ($r->gw ?? 0);
        $nw = (float) ($r->nw ?? 0);

        return [
            'POno'         => $r->POno,
            'customer_po'  => '',
            'style'        => $r->style,
            'color'        => $r->po_material ?: $r->material,
            'unit'         => '',
            'ctn_from'     => $r->carton,
            'ctn_to'       => $r->carton,
            'ttl_ctn'      => $ttlCtn,
            'qty_per_size' => $this->extractQtyPerSize($r),
            'qty_per_ctn'  => (int) $r->pcsp,
            'ttl_qty'      => (int) $r->pcsp,
            'gw'           => $gw,
            'nw'           => $nw,
            'ttl_gw'       => $gw,
            'ttl_nw'       => $nw,
            'meas'         => $meas,
            'master_box'   => '',
            'remark'       => '',
            'bundle'       => null,
        ];
    }

    // 1 baris per inner box; yang dihitung hanya 1 master carton
    // (ttl_ctn = 1 di baris pertama, sisanya 0 agar total carton tidak dobel)
    private function bundleLines($members): array
    {
        $span = $members->count();

        return $members->map(fn($r, $idx) => array_merge($this->singleLine($r, $idx === 0 ? 1 : 0, $this->bundleMeasurement($r)), [
            'ttl_ctn_label' => $idx === 0 ? 1 : '',
            'master_box'    => $idx === 0 ? 1 : '',
            'remark'        => 'INNER BOX',
            'bundle'        => ['first' => $idx === 0, 'span' => $span],
        ]))->all();
    }

    // carton mix: beberapa PO/size dalam 1 carton fisik (no carton sama),
    // hanya baris pertama yang dihitung, sisanya ditandai REPEATED (tanpa CTN NO.)
    private function mixLines($members): array
    {
        return $members->map(fn($r, $idx) => array_merge($this->singleLine($r, $idx === 0 ? 1 : 0, $this->measurement($r)), [
            'ttl_ctn_label' => $idx === 0 ? 1 : 'REPEATED',
            'remark'        => 'IRREGULAR',
        ], $idx === 0 ? [] : ['ctn_from' => '', 'ctn_to' => '']))->all();
    }

    // ukuran master carton (carton_bundle); kalau belum diisi pakai ukuran inner box
    private function bundleMeasurement($packRow): string
    {
        if ($packRow->cb_panjang === null || $packRow->cb_lebar === null || $packRow->cb_tinggi === null) {
            return $this->measurement($packRow);
        }

        return $this->measurement((object) [
            'panjang' => $packRow->cb_panjang,
            'lebar'   => $packRow->cb_lebar,
            'tinggi'  => $packRow->cb_tinggi,
            'measpk'  => $packRow->cb_measpk,
            'meas'    => $packRow->meas,
        ]);
    }
}
