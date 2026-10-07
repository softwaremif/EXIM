<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncPoImportStatus extends Command
{
    /**
     * Nama & signature command, ini yang dipakai di scheduler / manual run
     */
    protected $signature = 'po:sync-import-status';

    protected $description = 'Sinkronisasi status po.stsimp berdasarkan qty shipment vs qty PO';

    public function handle()
    {
        DB::connection('mysql_sig')->statement("
            UPDATE po
            LEFT JOIN (
                SELECT popk, SUM(qtys) AS shipped_qty
                FROM importdt
                WHERE stsdt = '0'
                GROUP BY popk
            ) shipped ON shipped.popk = po.popk
            LEFT JOIN (
                SELECT popk, SUM(pocons) AS po_qty
                FROM podt
                GROUP BY popk
            ) podt_sum ON podt_sum.popk = po.popk
            SET po.stsimp = CASE
                WHEN COALESCE(shipped.shipped_qty, 0) <= 0 THEN 0
                WHEN shipped.shipped_qty < podt_sum.po_qty THEN 1
                ELSE 2
            END
            WHERE po.polipk = 2
        ");

        Log::info('po:sync-import-status dijalankan otomatis pada ' . now());

        $this->info('Sync po.stsimp berhasil dijalankan pada ' . now());
    }
}