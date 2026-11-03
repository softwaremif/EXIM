<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

// ============================================================
// PROGRES STUFFING per shipment -- dipakai kolom "Stuffing" di list
// shipment plan & notifikasi email stuffing 100%
// - total  = carton yang dialokasikan EXIM ke container shipment ini
//            (pack.exportpk = shipment ini & pack.contpk terisi)
// - loaded = dari carton tsb, yang sudah discan masuk container di
//            FG Stuffing (pack.status 6 = Stuffing, 7 = Shipment)
// 1 carton fisik dihitung 1x, grouping SAMA dengan API Packing:
// Bundle via bundlepk, Mix via mixno, selain itu
// carton + POno + OP + poref + part (1 PO bisa punya beberapa popk)
// ============================================================
class StuffingProgress
{
    /**
     * @param iterable $exportpks
     * @return \Illuminate\Support\Collection exportpk => ['loaded' => int, 'total' => int]
     */
    public static function forExports($exportpks)
    {
        $exportpks = collect($exportpks)->map(fn($v) => (int) $v)->filter()->unique()->values();
        if ($exportpks->isEmpty()) {
            return collect();
        }

        $packsByExport = DB::connection('mysql_andon')
            ->table('pack')
            ->leftJoin('po', 'po.popk', '=', 'pack.popk')
            ->whereIn('pack.exportpk', $exportpks->all())
            ->whereNotNull('pack.contpk')
            ->selectRaw("DISTINCT pack.exportpk, (pack.status IN (6, 7)) AS stuffed,
                COALESCE(
                    CONCAT('B', NULLIF(pack.bundlepk, 0)),
                    CONCAT('M', NULLIF(pack.mixno, '')),
                    CONCAT('S', IFNULL(pack.carton, ''), '|', IFNULL(pack.POno, ''), '|', IFNULL(pack.OP, ''), '|',
                        IFNULL(po.poref, ''), '|', IFNULL(NULLIF(pack.part, 0), ''))
                ) AS ctn_key")
            ->get()
            ->groupBy('exportpk');

        return $exportpks->mapWithKeys(function ($exportpk) use ($packsByExport) {
            $packs = $packsByExport->get($exportpk, collect());

            return [$exportpk => [
                'loaded' => $packs->where('stuffed', 1)->pluck('ctn_key')->unique()->count(),
                'total'  => $packs->pluck('ctn_key')->unique()->count(),
            ]];
        });
    }
}
