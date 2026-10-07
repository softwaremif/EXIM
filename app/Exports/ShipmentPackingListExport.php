<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ShipmentPackingListExport implements FromView, ShouldAutoSize, WithStyles
{
    protected $exportpk;
    protected $tables = []; // menyimpan hasil olahan per PO, dipakai juga di WithStyles

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
            ->orderBy('po.popk')
            ->orderBy('pack.carton')
            ->select('pack.*', 'po.style', 'po.buyer', 'po.material', ...$this->sizeColumns())
            ->get();

        $groupedByPo = $rows->groupBy('popk');
        $tables = [];

        foreach ($groupedByPo as $popk => $poRows) {
            $poHeader = $poRows->first();
            $usedSizes = $this->extractUsedSizes($poHeader);
            [$colorCode, $colorName] = $this->splitColorMaterial($poHeader->material);
            $cartonGroups = $this->groupConsecutiveCartons($poRows, $usedSizes);

            $tables[] = [
                'style'      => $poHeader->style,
                'OP'         => $poHeader->OP,
                'POno'       => $poHeader->POno,
                'color'      => $colorName,
                'color_code' => $colorCode,
                'sizes'      => $usedSizes,      // [index => label]
                'groups'     => $cartonGroups,   // hasil grouping siap tampil
            ];
        }

        $this->tables = $tables;

        return view('exports.shipment-packing-list', compact('tables'));
    }

    public function styles(Worksheet $sheet)
    {
        // header tiap tabel di-bold + background abu — ditandai lewat class 'header-row'
        // di view lewat data-row, styling detail per baris dilakukan setelah render via loop
        $highestRow = $sheet->getHighestRow();
        $highestCol = $sheet->getHighestColumn();

        $sheet->getStyle("A1:{$highestCol}{$highestRow}")
            ->getFont()->setName('Arial')->setSize(10);

        $sheet->getStyle("A1:{$highestCol}{$highestRow}")
            ->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // cari baris header tiap tabel (baris yang isi kolom A = 'STYLE') dan tebalkan
        for ($r = 1; $r <= $highestRow; $r++) {
            $cellValue = $sheet->getCell("A{$r}")->getValue();
            if ($cellValue === 'STYLE') {
                $sheet->getStyle("A{$r}:{$highestCol}{$r}")->getFont()->setBold(true);
                $sheet->getStyle("A{$r}:{$highestCol}{$r}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('F2F2F2');
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

    private function splitColorMaterial(?string $material): array
    {
        if (empty($material)) return [null, null];
        if (preg_match('/^(\S+)\s+(.*)$/', trim($material), $m)) {
            return [$m[1], $m[2]];
        }
        return [null, $material];
    }

    private function groupConsecutiveCartons($poRows, array $usedSizes): array
    {
        $groups = [];
        $rows = $poRows->values();
        $count = $rows->count();
        $i = 0;

        while ($i < $count) {
            $current = $rows[$i];
            $currentQty = $this->extractQtyPerSize($current, $usedSizes);

            $j = $i;
            while (
                $j + 1 < $count
                && (int) $rows[$j + 1]->carton === (int) $rows[$j]->carton + 1
                && $this->extractQtyPerSize($rows[$j + 1], $usedSizes) === $currentQty
            ) {
                $j++;
            }

            $totalCartonInGroup = $j - $i + 1;
            $firstCarton = $rows[$i]->carton;
            $lastCarton = $rows[$j]->carton;

            $groups[] = [
                'case_range'     => $totalCartonInGroup > 1 ? "{$firstCarton} - {$lastCarton}" : (string) $firstCarton,
                'total_carton'   => $totalCartonInGroup,
                'pcs_per_carton' => (int) $current->pcsp,
                'total_pcs'      => (int) $current->pcsp * $totalCartonInGroup,
                'qty_per_size'   => $currentQty,
                'is_singleton'   => $totalCartonInGroup === 1,
                'line'           => $current->po_linepk ?? 1,
            ];

            $i = $j + 1;
        }

        return $groups;
    }

    private function extractQtyPerSize($packRow, array $usedSizes): array
    {
        $qty = [];
        foreach ($usedSizes as $index => $label) {
            $qty[$index] = (int) ($packRow->{"qtyp{$index}"} ?? 0);
        }
        return $qty;
    }
}